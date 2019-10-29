<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Quiz\QuizGateway;

class SettingsModel extends Db
{
	/**
	 * @var QuizGateway
	 */
	private $quizGateway;

	/**
	 * SettingsModel constructor.
	 *
	 * @param QuizGateway $quizGateway
	 */
	public function __construct(QuizGateway $quizGateway)
	{
		$this->quizGateway = $quizGateway;

		parent::__construct();
	}

	public function saveInfoSettings($newsletter, $infomail)
	{
		return $this->update('
			UPDATE 	`fs_foodsaver`
			SET 	`newsletter` = ' . (int)$newsletter . ',
					`infomail_message` = ' . (int)$infomail . '
			WHERE 	`id` = ' . (int)$this->session->id() . '
		');
	}

	public function getSleepData()
	{
		return $this->qRow('
			SELECT
				sleep_status,
				sleep_from,
				sleep_until,
				sleep_msg

			FROM
				fs_foodsaver

			WHERE
				id = ' . (int)$this->session->id() . '
		');
	}

	final public function getQuizSession($sid)
	{
		$sql = '
			SELECT
				`quiz_id`,
				`status`,
				`quiz_index`,
				`quiz_questions`,
				`quiz_result`,
				`fp`,
				`maxfp`

			FROM
				fs_quiz_session

			WHERE
				`id` = ' . (int)$sid . '

			AND
				`foodsaver_id` = ' . (int)$this->session->id() . '
		';
		$tmp = array();
		if ($session = $this->qRow($sql)) {
			$session['try_count'] = $this->qOne('SELECT COUNT(quiz_id) FROM fs_quiz_session WHERE foodsaver_id = ' . (int)$this->session->id() . ' AND `quiz_id` = ' . (int)$session['quiz_id']);

			/*
			 * First of all sort the question array and get al questions_ids etc to calculate the result
			 */
			if (!empty($session['quiz_questions'])) {
				$session['quiz_questions'] = unserialize($session['quiz_questions']);

				foreach ($session['quiz_questions'] as $quizQuestion) {
					$tmp[$quizQuestion['id']] = $quizQuestion;
					$ttmp = array();
					if (isset($quizQuestion['answers'])) {
						foreach ($quizQuestion['answers'] as $answer) {
							$ttmp[$answer] = $answer;
						}
					}
					if (!empty($ttmp)) {
						$tmp[$quizQuestion['id']]['answers'] = $ttmp;
					}
				}
			}

			if (!empty($session['quiz_result'])) {
				$session['quiz_result'] = unserialize($session['quiz_result']);

				foreach ($session['quiz_result'] as $k => $quizResult) {
					$session['quiz_result'][$k]['user'] = $tmp[$quizResult['id']];

					foreach ($quizResult['answers'] as $k2 => $v2) {
						$session['quiz_result'][$k]['answers'][$k2]['right'] = 0;
						if ($v2['right'] == 1) {
							$session['quiz_result'][$k]['answers'][$k2]['right'] = 1;
						}
						if ($v2['right'] == 2) {
							$session['quiz_result'][$k]['answers'][$k2]['right'] = 2;
						}
						$session['quiz_result'][$k]['answers'][$k2]['user_say'] = false;
						if (isset($session['quiz_result'][$k]['user']['answers'][$v2['id']])) {
							$session['quiz_result'][$k]['answers'][$k2]['user_say'] = true;
						}
					}
					if (!isset($session['quiz_result'][$k]['user']['userduration'])) {
						$session['quiz_result'][$k]['userduration'] = $session['quiz_result'][$k]['user']['duration'];
					} else {
						$session['quiz_result'][$k]['userduration'] = $session['quiz_result'][$k]['user']['userduration'];
					}
					if (!isset($session['quiz_result'][$k]['user']['noco'])) {
						$session['quiz_result'][$k]['noco'] = false;
					} else {
						$session['quiz_result'][$k]['noco'] = $session['quiz_result'][$k]['user']['noco'];
					}
					unset($session['quiz_result'][$k]['user']);
				}

				if ($quiz = $this->getValues(array('name', 'desc'), 'quiz', $session['quiz_id'])) {
					$session = array_merge($quiz, $session);
					unset($session['quiz_questions']);

					/*
					 * Add questions they're complete right answered
					 */
					$session['quiz_result'] = $this->addRightAnswers($tmp, $session['quiz_result']);

					return $session;
				}
			}

			return false;
		}
	}

	/*
	 * in the session are only the failed answers stored in so now we get all the right answers an fill out the array
	 */
	private function addRightAnswers($indexList, $fullList)
	{
		$out = array();

		$number = 0;

		foreach ($indexList as $id => $value) {
			++$number;
			if (!isset($fullList[$id])) {
				if ($question = $this->quizGateway->getQuestion($id)) {
					$answers = array();
					if ($qanswers = $this->quizGateway->getAnswers($id)) {
						foreach ($qanswers as $a) {
							$answers[$a['id']] = $a;
							$answers[$a['id']]['user_say'] = $a['right'];
						}
					}
					$out[$id] = array(
						'id' => $id,
						'text' => $question['text'],
						'duration' => $question['duration'],
						'wikilink' => $question['wikilink'],
						'fp' => $question['fp'],
						'answers' => $answers,
						'number' => $number,
						'percent' => 0,
						'userfp' => 0,
						'userduration' => 10,
						'noco' => 0
					);
				}
			} else {
				$out[$id] = $fullList[$id];
			}
		}

		return $out;
	}

	public function getFoodSharePoint()
	{
		return $this->q('
			SELECT 	ft.id,
					ft.name,
					ff.infotype,
					ff.`type`

			FROM 	`fs_fairteiler_follower` ff,
					`fs_fairteiler` ft

			WHERE 	ff.fairteiler_id = ft.id
			AND 	ff.foodsaver_id = ' . (int)$this->session->id() . '
		');
	}

	public function addNewMail($email, $token)
	{
		return $this->insert('
			REPLACE INTO `fs_mailchange`
			(
				`foodsaver_id`,
				`newmail`,
				`time`,
				`token`
			)
			VALUES
			(
				' . (int)$this->session->id() . ',
				' . $this->strval($email) . ',
				NOW(),
				' . $this->strval($token) . '
			)
		');
	}

	public function abortChangemail()
	{
		$this->del('DELETE FROM `fs_mailchange` WHERE foodsaver_id = ' . (int)$this->session->id());
	}

	public function changeMail($email)
	{
		$this->del('DELETE FROM `fs_mailchange` WHERE foodsaver_id = ' . (int)$this->session->id());

		if ($this->update('
			UPDATE `fs_foodsaver`
			SET `email` = ' . $this->strval($email) . '
			WHERE `id` = ' . (int)$this->session->id() . '
		')
		) {
			return true;
		}

		return false;
	}

	public function getMailchange()
	{
		return $this->qOne('SELECT `newmail` FROM fs_mailchange WHERE foodsaver_id = ' . (int)$this->session->id());
	}

	public function getForumThreads()
	{
		return $this->q('
			SELECT 	th.id,
					th.name,
					tf.infotype

			FROM 	`fs_theme_follower` tf,
					`fs_theme` th

			WHERE 	tf.theme_id = th.id
			AND 	tf.foodsaver_id = ' . (int)$this->session->id() . '
		');
	}

	public function updateFollowFairteiler($fid, $infotype)
	{
		return $this->update('
			UPDATE 		`fs_fairteiler_follower`
			SET 		`infotype` = ' . (int)$infotype . '
			WHERE 		`fairteiler_id` = ' . (int)$fid . '
			AND 		`foodsaver_id` = ' . (int)$this->session->id() . '
		');
	}

	public function updateFollowThread($themeId, $infotype)
	{
		return $this->update('
			UPDATE 		`fs_theme_follower`
			SET 		`infotype` = ' . (int)$infotype . '
			WHERE 		`theme_id` = ' . (int)$themeId . '
			AND 		`foodsaver_id` = ' . (int)$this->session->id() . '
		');
	}

	public function unfollowThread($unfollow)
	{
		return $this->del('
			DELETE FROM 	`fs_theme_follower`
			WHERE 	foodsaver_id = ' . (int)$this->session->id() . '
			AND 	theme_id IN(' . implode(',', $unfollow) . ')
		');
	}

	public function unfollowFairteiler($unfollow)
	{
		return $this->del('
			DELETE FROM 	`fs_fairteiler_follower`
			WHERE 	foodsaver_id = ' . (int)$this->session->id() . '
			AND 	fairteiler_id IN(' . implode(',', $unfollow) . ')
		');
	}

	public function getFsCount($regionId)
	{
		return (int)$this->qOne('
			SELECT
				COUNT(hb.foodsaver_id)

			FROM
				fs_foodsaver_has_bezirk hb

			WHERE
				hb.bezirk_id = ' . (int)$regionId . '

			AND
				hb.active = 1
		');
	}

	public function getNewMail($token)
	{
		return $this->qOne('SELECT newmail FROM fs_mailchange WHERE `token` = ' . $this->strval($token) . ' AND foodsaver_id = ' . (int)$this->session->id());
	}

	public function updateRole($role_id, $current_role)
	{
		if ($role_id > $current_role) {
			$this->update('UPDATE fs_foodsaver SET `rolle` = ' . (int)$role_id . ' WHERE id = ' . (int)$this->session->id());
		}
	}

	public function hasQuizCleared($quiz_id)
	{
		if ($res = $this->qOne('
				SELECT COUNT(foodsaver_id) AS `count`
				FROM fs_quiz_session
				WHERE foodsaver_id =' . (int)$this->session->id() . '
				AND quiz_id = ' . (int)$quiz_id . '
				AND `status` = 1
			')
		) {
			if ($res > 0) {
				return true;
			}
		}

		return false;
	}

	public function updateSleepMode($status, $from, $to, $msg)
	{
		return $this->update('
 			UPDATE
 				fs_foodsaver

 			SET
 				`sleep_status` = ' . (int)$status . ',
 				`sleep_from` = ' . $this->dateval($from) . ',
 				`sleep_until` = ' . $this->dateval($to) . ',
 				`sleep_msg` = ' . $this->strval($msg) . '

 			WHERE
 				id = ' . (int)$this->session->id() . '
 		');
	}
}
