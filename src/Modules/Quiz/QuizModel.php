<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class QuizModel extends Db
{
	private $bellGateway;
	private $foodsaverGateway;

	public function __construct(BellGateway $bellGateway, FoodsaverGateway $foodsaverGateway)
	{
		parent::__construct();
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function deleteQuest($id)
	{
		$this->del('DELETE FROM fs_answer WHERE `question_id` = ' . (int)$id);
		$this->del('DELETE FROM fs_question WHERE `id` = ' . (int)$id);
		$this->del('DELETE FROM fs_question_has_quiz WHERE `question_id` = ' . (int)$id);
	}

	public function deleteAnswer($id)
	{
		return $this->del('DELETE FROM fs_answer WHERE `id` = ' . (int)$id);
	}

	public function addAnswer($qid, $text, $exp, $right)
	{
		return $this->insert('
			INSERT INTO `fs_answer`(`question_id`, `text`,`explanation` ,`right`) VALUES (' . (int)$qid . ',' . $this->strval($text) . ',' . $this->strval($exp) . ', ' . (int)$right . ')
		');
	}

	public function addQuestion($qid, $text, $fp, $duration)
	{
		if ($id = $this->insert('
			INSERT INTO `fs_question`(`text`,`duration`) VALUES (' . $this->strval($text) . ',' . (int)$duration . ')
		')
		) {
			$this->insert('
				INSERT INTO `fs_question_has_quiz`(`question_id`, `quiz_id`, `fp`) VALUES (' . (int)$id . ',' . (int)$qid . ',' . (int)$fp . ')
			');

			return $id;
		}

		return false;
	}

	public function updateAnswer($id, $data)
	{
		return $this->update('
			UPDATE 	`fs_answer`
			SET 	`text` = ' . $this->strval($data['text']) . ',
					`explanation` = ' . $this->strval($data['explanation']) . ',
					`right` = ' . (int)$data['right'] . '
			WHERE 	`id` = ' . (int)$id . '
		');
	}

	public function getAnswer($aid)
	{
		return $this->qRow('
			SELECT 	`id`, question_id, `text`,`explanation`, `right`
			FROM	fs_answer
			WHERE 	id = ' . (int)$aid . '
		');
	}

	public function getRandomQuestions($count, $fp, $quiz_id)
	{
		return $this->q('
			SELECT
				q.id,
				q.`duration`,
				hq.fp

			FROM
				fs_question q,
				fs_question_has_quiz hq

			WHERE
				hq.question_id = q.id

			AND
				hq.quiz_id = ' . (int)$quiz_id . '

			AND
				hq.fp = ' . (int)$fp . '

			ORDER BY
				RAND()

			LIMIT ' . (int)$count . '
		');
	}

	public function getQuestionMetas($quiz_id)
	{
		if ($questions = $this->q('
			SELECT
				q.id,
				q.`duration`,
				hq.fp

			FROM
				fs_question q,
				fs_question_has_quiz hq

			WHERE
				hq.question_id = q.id

			AND
				hq.quiz_id = ' . (int)$quiz_id . '
		')
		) {
			$outmeta = array();
			if ($meta = $this->q('
				SELECT 	hq.fp, COUNT(q.id) AS `count`
				FROM
				fs_question q,
				fs_question_has_quiz hq

				WHERE
					hq.question_id = q.id

				AND
					hq.quiz_id = ' . (int)$quiz_id . '

				GROUP BY
					hq.fp
			')
			) {
				foreach ($meta as $m) {
					if (!isset($outmeta[$m['fp']])) {
						$outmeta[$m['fp']] = $m['count'];
					}
				}
			}

			return array(
				'meta' => $outmeta,
				'question' => $questions
			);
		}
	}

	public function getQuestion($id)
	{
		return $this->qRow('
			SELECT
					q.id,
					q.`text`,
					q.duration,
					q.wikilink,
					hq.fp,
					hq.quiz_id

				FROM
					fs_question q,
					fs_question_has_quiz hq

				WHERE
					hq.question_id = q.id

				AND
					q.id = ' . (int)$id . '
		');
	}

	public function addUserComment($question_id, $comment)
	{
		if ($id = $this->insert('
			INSERT INTO `fs_wallpost`
			(`foodsaver_id`, `body`, `time`)
			VALUES
			(
				' . (int)$this->session->id() . ',
				' . $this->strval($comment) . ',
				NOW()
			)
		')
		) {
			if ($quizAMBs = $this->foodsaverGateway->getBotschafter(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP)) {
				$this->bellGateway->addBell($quizAMBs, 'new_quiz_comment_title', 'new_quiz_comment', 'fas fa-question-circle', array('href' => '/?page=quiz&sub=wall&id=' . (int)$question_id), array(
					'comment' => $comment
				));
			}
			$this->insert('INSERT INTO `fs_question_has_wallpost`(`question_id`, `wallpost_id`, `usercomment`) VALUES (' . (int)$question_id . ',' . (int)$id . ',1)');

			return true;
		}

		return false;
	}

	public function getAnswers($question_id)
	{
		return $this->q('
			SELECT 	`id`, `text`,`explanation`, `right`
			FROM	fs_answer
			WHERE 	question_id = ' . (int)$question_id . '
		');
	}

	public function getRightQuestions($quiz_id)
	{
		$out = array();
		if ($questions = $this->q('
				SELECT
				q.id,
				q.`text`,
				q.duration,
				q.wikilink,
				hq.fp

				FROM
				fs_question q,
				fs_question_has_quiz hq

				WHERE
				hq.question_id = q.id

				AND
				hq.quiz_id = ' . (int)$quiz_id . '
				')
		) {
			foreach ($questions as $key => $q) {
				$out[$q['id']] = $q;
				if ($answers = $this->q('
						SELECT 	`id`, `text`,`explanation`, `right`
						FROM	fs_answer
						WHERE 	question_id = ' . (int)$q['id'] . '
				')
				) {
					$out[$q['id']]['answers'] = array();
					foreach ($answers as $a) {
						$out[$q['id']]['answers'][$a['id']] = $a;
					}
				}
			}

			return $out;
		}

		return false;
	}

	public function listQuestions($quiz_id)
	{
		if ($questions = $this->q('
				SELECT
					q.id,
					q.`text`,
					q.duration,
					q.wikilink,
					hq.fp

				FROM
					fs_question q,
					fs_question_has_quiz hq

				WHERE
					hq.question_id = q.id

				AND
					hq.quiz_id = ' . (int)$quiz_id . '
		')
		) {
			foreach ($questions as $key => $q) {
				$questions[$key]['answers'] = $this->q('
					SELECT 	`id`, `text`,`explanation`, `right`
					FROM	fs_answer
					WHERE 	question_id = ' . (int)$q['id'] . '
				');
				$questions[$key]['comment_count'] = (int)$this->qOne('SELECT COUNT(question_id) FROM fs_question_has_wallpost WHERE question_id = ' . (int)$q['id']);
			}

			return $questions;
		}

		return false;
	}

	public function abortSession($sid)
	{
		$this->update('
			UPDATE
				fs_quiz_session

			SET
				`status` = 2

			WHERE
				id = ' . (int)$sid . '

			AND
				foodsaver_id = ' . (int)$this->session->id() . '
		');
	}

	public function getQuizStatus($quiz_id)
	{
		$out = array(
			'cleared' => 0,
			'running' => 0,
			'failed' => 0,
			'last_try' => 0,
			'times' => 0
		);

		if ($res = $this->q('
				SELECT foodsaver_id, `status`, UNIX_TIMESTAMP(`time_start`) AS time_ts
				FROM fs_quiz_session
				WHERE foodsaver_id =' . (int)$this->session->id() . '
				AND quiz_id = ' . (int)$quiz_id . '
				')
		) {
			foreach ($res as $r) {
				++$out['times'];
				if ($r['time_ts'] > $out['last_try']) {
					$out['last_try'] = $r['time_ts'];
				}

				if ($r['status'] == 0) {
					++$out['running'];
				} elseif ($r['status'] == 1) {
					++$out['cleared'];
				} elseif ($r['status'] == 2) {
					++$out['failed'];
				}
			}
		}

		return $out;
	}

	public function updateQuizSession($session_id, $questions, $quiz_index)
	{
		$questions = serialize($questions);

		$this->update('
			UPDATE
				`fs_quiz_session`

			SET
				quiz_questions = ' . $this->strval($questions) . ',
				quiz_index = ' . (int)$quiz_index . '

			WHERE
				id = ' . (int)$session_id . '
		');
	}

	public function finishQuiz($session_id, $questions, $quiz_result, $fp, $maxfp)
	{
		$quiz_result = serialize($quiz_result);
		$questions = serialize($questions);

		// nicht bestanden ?
		$status = 2;

		// quiz fertig und bestanden
		if ($fp <= $maxfp) {
			$status = 1;
		}

		$this->update('
			UPDATE
				`fs_quiz_session`

			SET
				quiz_result = ' . $this->strval($quiz_result) . ',
				quiz_questions = ' . $this->strval($questions) . ',
				time_end = ' . $this->dateval(date('Y-m-d H:i:s')) . ',
				`status` = ' . (int)$status . ',
				`fp` = ' . floatval($fp) . ',
				`maxfp` = ' . (int)$maxfp . '

			WHERE
				id = ' . (int)$session_id . '
		');
	}

	public function updateQuestion($id, $quiz_id, $text, $fp, $duration, $wikilink)
	{
		$this->update('
			UPDATE 	`fs_question`
			SET 	`text` = ' . $this->strval($text) . ',
					`duration` = ' . (int)$duration . ',
					`wikilink` = ' . $this->strval($wikilink) . '
			WHERE 	`id` = ' . (int)$id . '
		');

		return $this->update('
			UPDATE 	`fs_question_has_quiz`
			SET 	`fp` = ' . (int)$fp . '
			WHERE 	`question_id` = ' . (int)$id . '
			AND 	`quiz_id` = ' . (int)$quiz_id . '
		');
	}
}
