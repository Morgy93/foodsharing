<?php

namespace Foodsharing\Modules\Quiz;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;
use Foodsharing\Modules\Core\View;

class QuizView extends View
{
	public function abortOrPause(): string
	{
		return '
				<p>Nimm Dir doch noch die Zeit diese Frage zu beantworten! Ansonsten wird diese Frage als falsch gewertet.</p>
				<p>Nach der Beantwortung der Frage kannst Du auch <strong>pausieren</strong> ohne Fehlerpunkte zu bekommen.</p>';
	}

	public function sessionList(array $sessions, array $quiz): string
	{
		$rows = [];

		$this->pageHelper->addJs('
			$(".usersessionlink").parent().parent().on("click", function(){
				goTo($(this).children("td").children(".usersessionlink").attr("href"));
			});
		');

		foreach ($sessions as $s) {
			$status = '<span style="color:orange;">Quiz läuft</span>';
			if ($s['min_status'] == SessionStatus::PASSED) {
				$status = '<span style="color:green">bestanden</span>';
			} elseif ($s['max_status'] == SessionStatus::FAILED) {
				$status = '<span style="color:red;">durchgefallen</span>';
			}

			$rows[] = [
				['cnt' => '<a style="margin-left:10px;" href="#"><img src="' . $this->imageService->img($s['fs_photo']) . '" /></a>'],
				['cnt' => '<a class="usersessionlink" href="/?page=quiz&sub=sessiondetail&fsid=' . $s['fs_id'] . '">' . $s['fs_name'] . '</a>'],
				['cnt' => $s['max_fp']],
				['cnt' => substr($s['time_start'], 0, -3)],
				['cnt' => $s['trycount']],
				['cnt' => $status]
			];
		}

		$table = $this->v_utils->v_tablesorter([
			['name' => '&nbsp;', 'width' => 50, 'sort' => false],
			['name' => 'Name'],
			['name' => 'FP', 'width' => 40],
			['name' => 'Datum', 'width' => 100],
			['name' => 'Versuche', 'width' => 40],
			['name' => 'Status', 'width' => 75]
		], $rows, ['pager' => true]);

		return $this->v_utils->v_field($table, $quiz['name']);
	}

	public function userSessions(array $sessions): string
	{
		$out = '';

		/*
		* Example:
		*
			[0] => Array
			(
				[id] => 9
				[fp] => 0.00
				[status] => 1
				[time_start] => 2014-07-05 18:14:02
				[time_start_ts] => 1404576842
				[name] => Foodsaver
			)

			[1] => Array
			(
				[id] => 10
				[fp] => 5.50
				[status] => 2
				[time_start] => 2014-07-07 09:06:33
				[time_start_ts] => 1404716793
				[name] => Foodsaver
			)
		*/

		$cur_qid = $sessions[0]['quiz_id'];
		$rows = [];
		foreach ($sessions as $key => $s) {
			$status = '<span style="color:orange;">Quiz läuft</span>';
			if ($s['status'] == SessionStatus::PASSED) {
				$status = '<span style="color:green">bestanden</span>';
			} elseif ($s['status'] == SessionStatus::FAILED) {
				$status = '<span style="color:red;">durchgefallen</span>';
			}

			$rows[] = [
				['cnt' => substr($s['time_start'], 0, -3)],
				['cnt' => $s['fp']],
				['cnt' => $status],
				['cnt' => $this->legacyToolbar($s['id'])],
			];
			if ($cur_qid != $s['quiz_id'] || $key == (count($sessions) - 1)) {
				$cur_qid = $s['quiz_id'];
				$out .= $this->v_utils->v_field($this->v_utils->v_tablesorter([
					['name' => 'Datum'],
					['name' => 'FP', 'width' => 40],
					['name' => 'Status', 'width' => 75],
					['name' => '&nbsp;', 'width' => 30]
				], $rows, ['pager' => true]), $s['quiz_name'] . ' Quiz');

				$rows = [];
			}
		}

		return $out;
	}

	/**
	 * @deprecated Use modern frontend code instead
	 */
	private function legacyToolbar($id): string
	{
		if (isset($_GET['bid'])) {
			$bid = '&bid=' . (int)$_GET['bid'];
		} else {
			$bid = $this->session->getCurrentRegionId();
		}

		$page = $this->routeHelper->getPage();
		$confirmMsg = 'Soll diese Quiz-Session wirklich gel&ouml;scht werden?';
		$link = "'/?page=" . $page . '&a=delete&id=' . $id . "'";
		$out = '
			<li class="ui-state-default ui-corner-left ui-corner-right"'
			. ' title="' . $this->translator->trans('button.delete') . '"'
			. ' onclick="ifconfirm(' . $link . ',\'' . $confirmMsg . '\');">'
			. '<span class="ui-icon ui-icon-trash"></span>'
		. '</li>';

		return '<ul class="toolbar" class="ui-widget ui-helper-clearfix">' . $out . '</ul>';
	}

	public function noSessions(array $quiz): string
	{
		return $this->v_utils->v_field($this->v_utils->v_info('Dieses Quiz wurde noch nicht ausgeführt'), $quiz['name']);
	}

	public function listQuiz($quizze): string
	{
		$menu = [];
		$out = '';
		if (is_array($quizze)) {
			foreach ($quizze as $q) {
				$menu[] = [
					'name' => $q['name'],
					'href' => '/?page=quiz&id=' . (int)$q['id']
				];
			}
		}
		if (empty($menu)) {
			$out = $this->v_utils->v_info('Es wurde noch kein Quiz angelegt');
		} else {
			$out = $this->menu($menu, ['active' => 'quiz&id=' . (int)$_GET['id']]);
		}

		return $this->v_utils->v_field($out, ' Quizze');
	}

	public function quizbuttons($quizid): string
	{
		return '
		<div class="ui-widget ui-widget-content ui-corner-all ui-padding margin-bottom">
			<a href="#" class="button" onclick="ajreq(\'addquestion\',{qid:' . (int)$quizid . '});return false;">Neue Frage</a> <a href="#" class="button" onclick="ajreq(\'startquiz\',{qid:' . (int)$quizid . '});return false;">Quiz testen</a> <a href="/?page=quiz&sub=edit&qid=' . (int)$_GET['id'] . '" class="button">Quiz bearbeiten</a> <a href="/?page=quiz&sub=sessions&id=' . (int)$_GET['id'] . '" class="button">Auswertung</a>
		</div>';
	}

	public function quizComment(): string
	{
		return '<div id="quizcomment"> ' . $this->v_utils->v_form_textarea('quizusercomment', [
			'placeholder' => $this->translator->trans('quizusercomment'),
			'nolabel' => true
		]) . '</div>';
	}

	public function questionForm(): string
	{
		return
			$this->v_utils->v_form_textarea('text') .
			$this->v_utils->v_form_select('duration', [
				'values' => [
					['id' => 10, 'name' => '10 Sekunden'],
					['id' => 20, 'name' => '20 Sekunden'],
					['id' => 30, 'name' => '30 Sekunden'],
					['id' => 40, 'name' => '40 Sekunden'],
					['id' => 50, 'name' => '50 Sekunden'],
					['id' => 60, 'name' => '1 Minute'],
					['id' => 70, 'name' => '1 Min 10 Sekunden'],
					['id' => 80, 'name' => '1 Min 20 Sekunden'],
					['id' => 90, 'name' => '1,5 Minuten'],
					['id' => 100, 'name' => '1 Min 40 Sekunden'],
					['id' => 110, 'name' => '1 Min 50 Sekunden'],
					['id' => 120, 'name' => '2 Minuten'],
					['id' => 130, 'name' => '2 Min 10 Sekunden'],
					['id' => 140, 'name' => '2 Min 20 Sekunden'],
					['id' => 150, 'name' => '2,5 Minuten'],
					['id' => 160, 'name' => '2 Min 40 Sekunden'],
					['id' => 170, 'name' => '2 Min 50 Sekunden'],
					['id' => 180, 'name' => '3 Minuten'],
					['id' => 190, 'name' => '3 Min 10 Sekunden'],
					['id' => 200, 'name' => '3 Min 20 Sekunden']
				]
			]) .
			$this->v_utils->v_form_select('fp', [
				'values' => [
					['id' => 1, 'name' => '1 Fehlerpunkt'],
					['id' => 2, 'name' => '2 Fehlerpunkte'],
					['id' => 3, 'name' => '3 Fehlerpunkte'],
					['id' => 12, 'name' => '12 Fehlerpunkte (k. o.)'],
					['id' => 0, 'name' => 'keine Fehlerpunkte (Scherzfrage)']
				]
			]) .
			$this->v_utils->v_form_text('wikilink');
	}

	public function answerForm(): string
	{
		return
			$this->v_utils->v_form_textarea('text') .
			$this->v_utils->v_form_textarea('explanation') .
			$this->v_utils->v_form_select('right', ['values' => [
				['id' => 1, 'name' => 'Richtig'],
				['id' => 0, 'name' => 'Falsch'],
				['id' => 2, 'name' => 'Neutral']
			]]);
	}

	public function quizMenu()
	{
		$menu = [];

		$menu[] = [
			'name' => 'Neues Quiz Anlegen',
			'href' => '/?page=quiz&sub=newquiz'
		];

		return $this->menu($menu);
	}

	public function quizForm(): string
	{
		return $this->v_utils->v_quickform('Neues Quiz', [
			$this->v_utils->v_form_text('name'),
			$this->v_utils->v_form_tinymce('desc'),
			$this->v_utils->v_form_text('maxfp'),
			$this->v_utils->v_form_text('questcount')
		]);
	}

	public function quizQuestion(array $question, array $answers): string
	{
		$out = '
				<div style="float:right;width:150px;margin-left:50px;margin-bottom:10px;" id="countdown"></div>
			<div style="border-radius:10px;font-size:16px;color:#4A3520;padding:10px;background:#F5F5B5;margin-bottom:15px;line-height:20px;">' . $question['text'] . '</div>
		';

		$out .= '<div id="qanswers"><ul style="display:block;list-style:none;">';
		$i = 0;
		foreach ($answers as $k => $a) {
			++$i;
			$cb[] = ['id' => $a['id'], 'name' => $a['text']];
			$out .= '
			<li id="qanswer-' . $a['id'] . '" class="answer" onmouseout="$(this).css(\'background-color\',\'transparent\');" onmouseover="$(this).css(\'background-color\',\'#FFFFFF\');" style="cursor:pointer;border-radius:10px;display:block;list-style:none;padding:10px 10px;font-size:14px;color:#4A3520">
				<label>
					<span style="cursor:pointer;user-select:none;float:left">' . ($k + 1) . '. &nbsp;</span>
					<input id="qacb-' . $a['id'] . '" style="cursor:pointer;float:left;" type="checkbox" class="qanswers" name="qanswers[]" value="' . $a['id'] . '" />
					<span style="cursor:pointer;user-select:none;display:block;margin-left:43px;">' . $a['text'] . '</span>
					<span style="clear:both;"></span>
				</label>
			</li>';
		}
		++$i;
		$out .= '
		<li class="noanswer" onmouseout="$(this).css(\'background-color\',\'transparent\');" onmouseover="$(this).css(\'background-color\',\'#FFFFFF\');" style="cursor:pointer;-moz-user-select:none;border-radius:10px;display:block;list-style:none;padding:10px 10px;font-size:14px;">
			<label>
				<span style="cursor:pointer;user-select:none;float:left">' . ($i) . '. &nbsp;</span>
				<input class="nocheck" style="float:left;" type="checkbox" name="none" value="0" />
				<span style="cursor:pointer;wouser-select:none;display:block;margin-left:43px;color:#4A3520;">Es ist keine Antwort richtig!</span>
				<span style="clear:both;"></span>
			</label>
		</li>
		</ul></div>';

		return '
			<div id="quizwrapper">
				' . $out . '
			</div>
			<table id="quizbreath" width="100%" height="95%">
				<tr><td style="vertical-align: middle;text-align:center;font-size:16px;font-weight:bold;color:#4A3520">
				<img src="/img/cuploader.gif" style="margin-bottom:20px;" /><br />
				<span>Verschnaufpause... </span>
				</td></tr>
			</table>';
	}

	public function pause(): string
	{
		$msg = '';
		if (isset($_GET['timefail'])) {
			$msg = $this->v_utils->v_info('Die Zeit ist abgelaufen. Daher wird diese Frage leider als falsch gewertet.');
		}

		return $msg . '
		<p style="text-align: center; padding: 40px;">
			<img src="/img/clockloader.gif" />
		</p>';
	}

	public function result($explains, $failurePoints, $maxFailurePoints): string
	{
		$valid = 'Sorry, diesmal hat es nicht geklappt.';
		$bg = '#ED563D';
		if ($failurePoints < $maxFailurePoints) {
			$valid = 'Herzlichen Glückwunsch! Bestanden.';
			$bg = '#48A21C';
		}
		$out = '
		<div style="font-size:16px;font-weight:bold;margin-bottom:15px;background-color:' . $bg . ';padding:15px;border-radius:10px;line-height:30px;text-align:center;color:#fff;">
		' . $valid . '<br />
		<span style="font-size:13px;">' . $failurePoints . ' von maximal ' . $maxFailurePoints . ' Fehlerpunkten</span>
		</div>';

		$out .= '
		<div id="explains">';
		foreach ($explains as $e) {
			$exp = '';

			foreach ($e['explains'] as $ex) {
				$right = 'Auch diese Antwort wäre <strong style="color: green; font-weight: bold;">richtig</strong> gewesen!';
				if ($ex['right'] == 0) {
					$right = 'Diese Antwort ist <strong style="color: red; font-weight: bold;">nicht richtig</strong>!';
				} elseif ($ex['right'] != 1) {
					$right = 'Diese Antwort wurde nicht gewertet.';
				}
				$exp .= $this->v_utils->v_input_wrapper(
					$right,
					'<div style="margin: 10px 0;">' . $ex['text'] . '</div>' .
					'<div class="ui-state-highlight ui-corner-all" style="padding: 15px;">'
						. '<p><strong>Erklärung:</strong> ' . $ex['explanation'] . '</p>' .
					'</div>'
				);
			}

			$out .= '
				<h3><strong>Frage ' . (int)$e['number'] . ' ' . (100 - $e['percent']) . ' % richtig</strong> - ' . $e['userfp'] . '/' . $e['fp'] . ' Fehlerpunkten</h3>
				<div style="background-color:#FFFFFF;">
					<p style="font-style:italic;padding:15px;">&bdquo;' . ($e['text']) . '&ldquo;</p>
					' . $exp . '
				</div>';
		}
		$out .= '
		</div>
		<p style="text-align: center;">';

		if ($failurePoints < $maxFailurePoints) {
			switch ($this->session->get('quiz-id')) {
				case Role::FOODSAVER:
					$out .= '<a href="/?page=settings&sub=up_fs" class="button">'
						. 'Jetzt die Foodsaver-Anmeldung abschließen.' .
					'</a>';
					break;

				case Role::STORE_MANAGER:
					$out .= '<a href="/?page=settings&sub=up_bip" class="button">'
						. 'Jetzt die Betriebsverantwortlichenanmeldung abschließen.' .
					'</a>';
					break;

				case Role::AMBASSADOR:
					$out .= '<a href="/?page=settings&sub=up_bot" class="button">'
						. 'Jetzt die Botschafteranmeldung abschließen.'
					. '</a>';
					break;

				default:
					break;
			}
		}

		$out .= '
		</p>';

		return $out;
	}

	public function initQuizPage(array $page): string
	{
		return '<h1>' . $page['title'] . '</h1>' . $page['body'];
	}

	public function listQuestions($questions, $quiz_id): string
	{
		if (is_array($questions)) {
			$this->pageHelper->addJs('
				$("#questions").accordion({
					heightStyle: "content",
					animate: 200,
					collapsible: true,
					autoHeight: false,
					active: false
				});
				setTimeout(function () {
					$("#questions").css("opacity", 1);
				}, 500);');
			$out = '
			<div id="questions">';
			foreach ($questions as $q) {
				$answers = '<ul class="answers" id="answerlist-' . $q['id'] . '">';
				if (is_array($q['answers'])) {
					foreach ($q['answers'] as $k => $a) {
						$answers .= '<li class="right-' . $a['right'] . '" id="answer-' . $a['id'] . '">'
							. $a['text']
							. ' <span class="explanation"><strong>Erklärung: </strong>' . $a['explanation'] . '</span>'
							. ' <a class="dellink" href="#" onclick="if(confirm(\'Antwort wirklich löschen?\')){ajreq(\'delanswer\',{id:' . (int)$a['id'] . '});}return false;">[löschen]</a>'
							. ' <a class="dellink" href="#" onclick="ajreq(\'editanswer\',{id:' . (int)$a['id'] . '});return false;">[bearbeiten]</a>'
						. '</li>';
					}
				}
				$answers .= '</ul>';
				$out .= '
				<h3 class="question-' . $q['id'] . '"><strong>#' . (int)$q['id'] . ' </strong> - <span class="teaser">' . $this->sanitizerService->tt($q['text'], 50) . ' ' . (int)$q['comment_count'] . ' Kommentare</span></h3>
				<div class="question-' . $q['id'] . '">
					' . $this->v_utils->v_input_wrapper('Frage', $q['text'] . '
					<p><strong>' . $q['fp'] . ' Fehlerpunkte, ' . $q['duration'] . ' Sekunden zum Antworten</strong></p>
					<p style="margin-top:15px;">
						<a href="#" class="button" onclick="ajreq(\'addanswer\',{qid:' . (int)$q['id'] . '});return false;">Antwort hinzufügen</a> <a href="#" class="button" onclick="if(confirm(\'Wirklich die ganze Frage löschen?\')){ajreq(\'delquest\',{id:' . (int)$q['id'] . '});}return false;">Frage komplett löschen</a> <a href="#" class="button" onclick="ajreq(\'editquest\',{id:' . (int)$q['id'] . ',qid:' . (int)$quiz_id . '});return false;">Frage bearbeiten</a> <a class="button" href="/?page=quiz&sub=wall&id=' . (int)$q['id'] . '">Kommentare</a>
					</p>') . '
					' . $this->v_utils->v_input_wrapper('Antworten', $answers) . '
				</div>';
			}
			$out .= '
			</div>';

			return $out;
		}

		return $this->v_utils->v_field($this->v_utils->v_info('Noch keine Fragen zu diesem Quiz'), 'Fragen');
	}

	public function answerSidebar(array $answers): string
	{
		if (empty($answers)) {
			return '';
		}

		$out = '
<ul class="linklist">';
		foreach ($answers as $a) {
			$indicator = 'trafficlight color-success';
			if ($a['right'] == 0) {
				$indicator = 'trafficlight color-danger';
			} elseif ($a['right'] == 2) {
				$indicator = '';
			}
			$out .= '
	<li>
		<a href="#" onclick="ajreq(\'editanswer\',{app:\'quiz\',id:' . $a['id'] . '});return false;" class="ui-corner-all">
			<span style="height: 35px; overflow: hidden; font-size: 11px;">'
			. '<strong class="' . $indicator . '" style="float: right; margin: 0 0 0 3px;">'
				. '<span>&nbsp;</span>'
			. '</strong>'
			. $this->sanitizerService->tt($a['text'], 60) .
			'</span>
			<span style="clear:both;"></span>
		</a>
	</li>';
		}
		$out .= '
</ul>';

		return $this->v_utils->v_field($out, 'Antwortmöglichkeiten');
	}
}
