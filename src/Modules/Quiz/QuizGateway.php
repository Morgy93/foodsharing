<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\WallPost\WallPostGateway;

class QuizGateway extends BaseGateway
{
    private BellGateway $bellGateway;
    private FoodsaverGateway $foodsaverGateway;
    private WallPostGateway $wallPostGateway;

    public function __construct(
        Database $db,
        BellGateway $bellGateway,
        FoodsaverGateway $foodsaverGateway,
        WallPostGateway $wallPostGateway
    ) {
        parent::__construct($db);

        $this->bellGateway = $bellGateway;
        $this->foodsaverGateway = $foodsaverGateway;
        $this->wallPostGateway = $wallPostGateway;
    }

    public function listQuiz(): array
    {
        return $this->db->fetchAll('
			SELECT id, name
			FROM fs_quiz
			ORDER BY id
		');
    }

    public function addQuiz(string $name, string $desc, int $maxFailurePoints, int $questionCount): int
    {
        return $this->db->insert('fs_quiz',
            [
                'name' => $name,
                'desc' => $desc,
                'maxfp' => $maxFailurePoints,
                'questcount' => $questionCount
            ]
        );
    }

    public function updateQuiz(int $id, string $name, string $desc, int $maxFailurePoints, int $questionCount, ?int $questionCountUntimed): int
    {
        return $this->db->update(
            'fs_quiz',
            [
                'name' => $name,
                'desc' => $desc,
                'is_desc_htmlentity_encoded' => 0,
                'maxfp' => $maxFailurePoints,
                'questcount' => $questionCount,
                'questcount_untimed' => $questionCountUntimed,
            ],
            ['id' => $id]
        );
    }

    public function getQuiz(int $id): array
    {
        return $this->db->fetchByCriteria(
            'fs_quiz',
            [
                'id',
                'name',
                'desc',
                'is_desc_htmlentity_encoded',
                'maxfp',
                'questcount',
                'questcount_untimed',
            ],
            ['id' => $id]
        );
    }

    public function getQuizName(int $quizId): string
    {
        $quiz = $this->getQuiz($quizId);

        return $quiz ? $quiz['name'] : '';
    }

    public function addQuestion(int $quizId, string $text, int $failurePoints, int $duration): int
    {
        $questionId = $this->db->insert(
            'fs_question',
            [
                'text' => $text,
                'duration' => $duration
            ]
        );
        if ($questionId > 0) {
            $this->db->insert(
                'fs_question_has_quiz',
                [
                    'question_id' => $questionId,
                    'quiz_id' => $quizId,
                    'fp' => $failurePoints
                ]
            );

            return $questionId;
        }

        return 0;
    }

    public function getQuestion(int $questionId): array
    {
        return $this->db->fetch('
			SELECT
					q.id,
					q.`text`,
					q.duration,
					q.wikilink,
					hq.fp,
					hq.quiz_id

				FROM
					fs_question q
					LEFT JOIN fs_question_has_quiz hq
					ON hq.question_id = q.id

				WHERE
					q.id = :questionId
		', [':questionId' => $questionId]);
    }

    public function getRandomQuestions(int $count, int $failurePoints, int $quizId): array
    {
        return $this->db->fetchAll('
			SELECT
				q.id,
				q.duration,
				hq.fp
			FROM fs_question q
			LEFT JOIN fs_question_has_quiz hq
				ON hq.question_id = q.id
			WHERE hq.quiz_id = :quizId AND hq.fp = :fp
            ORDER BY RAND()
			LIMIT :count
		', [':quizId' => $quizId, ':fp' => $failurePoints, ':count' => $count]);
    }

    public function getQuestionCountByFailurePoints(int $quizId): array
    {
        return $this->db->fetchAll('
            SELECT
                hq.fp,
                COUNT(q.id) AS `count`
            FROM fs_question q
            LEFT JOIN fs_question_has_quiz hq
                ON hq.question_id = q.id
            WHERE hq.quiz_id = :quizId
            GROUP BY hq.fp
            ORDER BY hq.fp ASC
        ', [':quizId' => $quizId]);
    }

    public function getFairQuestions(int $count, int $quizId): array
    {
        $questions = [];
        $fpCounts = $this->getQuestionCountByFailurePoints($quizId);
        $total = array_reduce($fpCounts, fn ($a, $b) => $b['count'] + $a, 0);
        $carryOver = 0;
        foreach ($fpCounts as &$fpCount) {
            $numQuestions = $fpCount['count'] / $total * $count + $carryOver;
            $rounded = round($numQuestions);
            $carryOver = $numQuestions - $rounded;
            array_push($questions, ...$this->getRandomQuestions($rounded, $fpCount['fp'], $quizId));
        }
        shuffle($questions);

        return $questions;
    }

    public function updateQuestion(int $questionId, string $text, int $failurePoints, int $duration, string $wikiLink): void
    {
        $this->db->update(
            'fs_question',
            [
                'text' => $text,
                'duration' => $duration,
                'wikilink' => $wikiLink
            ],
            ['id' => $questionId]
        );

        $this->db->update(
            'fs_question_has_quiz',
            ['fp' => $failurePoints],
            [
                'question_id' => $questionId
            ]
        );
    }

    public function deleteQuestion(int $questionId): void
    {
        $this->db->delete('fs_answer', ['question_id' => $questionId]);
        $this->db->delete('fs_question', ['id' => $questionId]);
        $this->db->delete('fs_question_has_quiz', ['question_id' => $questionId]);
    }

    public function listQuestions(int $quizId): array
    {
        $questions = $this->getQuestions($quizId);
        foreach ($questions as &$question) {
            $question['answers'] = $this->getAnswers($question['id']);
            $question['comment_count'] = $this->countComments($question['id']);
        }

        return $questions;
    }

    public function listComments(int $questionId): array
    {
        return $this->db->fetchAllByCriteria(
            'fs_question_has_wallpost',
            ['question_id' => $questionId]
        );
    }

    private function countComments(int $questionId): int
    {
        return $this->db->count(
            'fs_question_has_wallpost',
            ['question_id' => $questionId]
        );
    }

    public function getRightQuestions(int $quizId): array
    {
        $out = [];
        $questions = $this->getQuestions($quizId);
        if ($questions) {
            foreach ($questions as $q) {
                $questionId = $q['id'];
                $out[$questionId] = $q;
                $answers = $this->getAnswers($questionId);
                if ($answers) {
                    $out[$questionId]['answers'] = [];
                    foreach ($answers as $a) {
                        $out[$questionId]['answers'][$a['id']] = $a;
                    }
                }
            }

            return $out;
        }

        return [];
    }

    private function getQuestions(int $quizId): array
    {
        return $this->db->fetchAll('
			SELECT
				q.id,
				q.text,
				q.duration,
				q.wikilink,
				hq.fp

			FROM
				fs_question q
				LEFT JOIN fs_question_has_quiz hq
				ON hq.question_id = q.id

			WHERE
				hq.quiz_id = :quizId
		', [':quizId' => $quizId]);
    }

    public function addAnswer(int $questionId, string $text, string $explanation, int $right): int
    {
        return $this->db->insert(
            'fs_answer',
            [
                'question_id' => $questionId,
                'text' => $text,
                'explanation' => $explanation,
                'right' => $right
            ]
        );
    }

    public function getAnswer(int $answerId): array
    {
        return $this->db->fetchByCriteria(
            'fs_answer',
            ['id', 'question_id', 'text', 'explanation', 'right'],
            ['id' => $answerId]
        );
    }

    public function getAnswers(int $questionId, bool $includeSolution = true): array
    {
        $columns = ['id', 'text'];
        if ($includeSolution) {
            array_push($columns, 'explanation', 'right');
        }

        return $this->db->fetchAllByCriteria(
            'fs_answer',
            $columns,
            ['question_id' => $questionId]
        );
    }

    public function updateAnswer(int $answerId, string $text, string $explanation, int $right): int
    {
        return $this->db->update(
            'fs_answer',
            [
                'text' => $text,
                'explanation' => $explanation,
                'right' => $right
            ],
            ['id' => $answerId]
        );
    }

    public function deleteAnswer(int $answerId): int
    {
        return $this->db->delete('fs_answer', ['id' => $answerId]);
    }

    public function addUserComment(int $questionId, int $fsId, string $comment): bool
    {
        $commentId = $this->wallPostGateway->addPost($comment, $fsId, 'question', $questionId);

        return $this->handleUserComment($questionId, $commentId, $comment);
    }

    private function handleUserComment(int $questionId, int $commentId, string $comment): bool
    {
        if ($commentId > 0) {
            if ($quizAMBs = $this->foodsaverGateway->getAdminsOrAmbassadors(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)) {
                $bellData = Bell::create(
                    'new_quiz_comment_title',
                    'new_quiz_comment',
                    'fas fa-question-circle',
                    ['href' => '/?page=quiz&sub=wall&id=' . $questionId],
                    ['comment' => $comment]
                );
                $this->bellGateway->addBell($quizAMBs, $bellData);
            }

            $this->db->update(
                'fs_question_has_wallpost',
                ['usercomment' => 1],
                ['wallpost_id' => $commentId]
            );

            return true;
        }

        return false;
    }
}
