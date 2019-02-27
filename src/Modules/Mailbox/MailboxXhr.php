<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Permissions\MailboxPermissions;
use Foodsharing\Services\SanitizerService;

class MailboxXhr extends Control
{
	private $sanitizerService;
	private $timeHelper;
	private $mailboxGateway;
	private $mailboxPermissions;

	public function __construct(
		Db $model,
		MailboxView $view,
		SanitizerService $sanitizerService,
		TimeHelper $timeHelper,
		MailboxGateway $mailboxGateway,
		MailboxPermissions $mailboxPermissions
	) {
		$this->model = $model;
		$this->view = $view;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->mailboxGateway = $mailboxGateway;
		$this->mailboxPermissions = $mailboxPermissions;

		parent::__construct();
	}

	public function attach()
	{
		if (!$this->session->may('bieb')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		// is filesize (10MB) and filetype allowed?
		$attachmentIsAllowed = $this->attach_allow($_FILES['etattach']['name'], $_FILES['etattach']['type']);
		if ($attachmentIsAllowed && isset($_FILES['etattach']['size']) && $_FILES['etattach']['size'] < 1310720) {
			$new_filename = bin2hex(random_bytes(16));

			$ext = strtolower($_FILES['etattach']['name']);
			$ext = explode('.', $ext);
			if (count($ext) > 1) {
				$ext = end($ext);
				$ext = trim($ext);
				$ext = '.' . preg_replace('/[^a-z0-9]/', '', $ext);
			} else {
				$ext = '';
			}

			$new_filename = $new_filename . $ext;

			move_uploaded_file($_FILES['etattach']['tmp_name'], 'data/mailattach/tmp/' . $new_filename);

			$init = 'window.parent.mb_finishFile("' . $new_filename . '");';
		} elseif (!$attachmentIsAllowed) {
			$init = 'window.parent.pulseInfo(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('wrong_file')) . '\');window.parent.mb_removeLast();';
		} else {
			$init = 'window.parent.pulseInfo(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('file_to_big')) . '\');window.parent.mb_removeLast();';
		}

		echo '<html><head>

		<script type="text/javascript">
			function init()
			{
				' . $init . '
			}
		</script>
				
		</head><body onload="init();"></body></html>';

		exit();
	}

	public function loadmails()
	{
		if (!$this->session->may('bieb')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$last_refresh = (int)$this->mem->get('mailbox_refresh');

		$cur_time = (int)time();

		if (
			$last_refresh == 0
			||
			($cur_time - $last_refresh) > 30
		) {
			$this->mem->set('mailbox_refresh', $cur_time);
		}

		$mb_id = (int)$_GET['mb'];
		if ($this->mailboxPermissions->mayMailbox($mb_id)) {
			$this->mailboxGateway->mailboxActivity($mb_id);
			if ($messages = $this->mailboxGateway->listMessages($mb_id, $_GET['folder'])) {
				$nc_js = '';
				if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
					if ($newcount = $this->mailboxGateway->getNewCount($boxes)) {
						foreach ($newcount as $nc) {
							$nc_js .= '
								$( "ul.dynatree-container a.dynatree-title:contains(\'' . $nc['name'] . '@' . PLATFORM_MAILBOX_HOST . '\')" ).removeClass("nonew").addClass("newmail").text("' . $nc['name'] . '@' . PLATFORM_MAILBOX_HOST . ' (' . (int)$nc['count'] . ')");';
						}
					}
				}
				$vontext = 'Von';
				if ($_GET['folder'] == 'sent') {
					$vontext = 'An';
				}

				return array(
					'status' => 1,
					'html' => $this->view->listMessages($messages),
					'append' => '#messagelist tbody',
					'script' => '
						$("#messagelist .from a:first").text("' . $vontext . '");
						$("#messagelist tbody tr").on("mouseover", function(){
							$("#messagelist tbody tr").removeClass("selected focused");
							$(this).addClass("selected focused");
							
						});
						$("#messagelist tbody tr").on("mouseout", function(){
							$("#messagelist tbody tr").removeClass("selected focused");							
						});
						$("#messagelist tbody tr").on("click", function(){
							ajreq("loadMail",{id:($(this).attr("id").split("-")[1])});
						});
						$("#messagelist tbody td").disableSelection();
						' . $nc_js . '
					'
				);
			}

			return array(
				'status' => 1,
				'html' => $this->view->noMessage(),
				'append' => '#messagelist tbody'
			);
		}
	}

	public function move()
	{
		if (!$this->session->may('bieb') || !$this->mailboxPermissions->mayMessage($_GET['mid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$folder = $this->model->getVal('folder', 'mailbox_message', $_GET['mid']);

		if ($folder == 3) {
			$this->mailboxGateway->deleteMessage($_GET['mid']);
		} else {
			$this->mailboxGateway->move($_GET['mid'], $_GET['f']);
		}

		return array(
			'status' => 1,
			'script' => '$("tr#message-' . (int)$_GET['mid'] . '").remove();$("#message-body").dialog("close");'
		);
	}

	public function quickreply()
	{
		if (!$this->session->may('bieb') || !isset($_GET['mid']) || !$this->mailboxPermissions->mayMessage($_GET['mid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($message = $this->mailboxGateway->getMessage(
			$_GET['mid'], $this->mailboxPermissions->mayMailbox($this->mailboxGateway->getMailboxId($_GET['mid']))
		)) {
			$sender = @json_decode($message['sender'], true);
			if (isset($sender['mailbox'], $sender['host']) && $sender != null) {
				$subject = 'Re: ' . trim(str_replace(array('Re:', 'RE:', 're:', 'aw:', 'Aw:', 'AW:'), '', $message['subject']));

				$body = strip_tags($_POST['msg']) . "\n\n\n\n--------- Nachricht von " . $this->timeHelper->niceDate($message['time_ts']) . " ---------\n\n>\t" . str_replace("\n", "\n>\t", $message['body']);

				$mail = new AsyncMail($this->mem);
				$mail->setFrom($message['mailbox'] . '@' . PLATFORM_MAILBOX_HOST, $this->session->user('name'));
				if ($sender['personal']) {
					$mail->addRecipient($sender['mailbox'] . '@' . $sender['host'], $sender['personal']);
				} else {
					$mail->addRecipient($sender['mailbox'] . '@' . $sender['host']);
				}
				$mail->setSubject($subject);
				$html = nl2br($body);
				$mail->setHTMLBody($html);
				$plainBody = $this->sanitizerService->htmlToPlain($html);
				$mail->setBody($body);
				$mail->send();

				echo json_encode(array(
					'status' => 1,
					'message' => 'Spitze! Die E-Mail wurde versendet.'
				));
				exit();
			}
		}

		echo json_encode(array(
			'status' => 0,
			'message' => 'Die E-Mail konnte nicht gesendet werden.'
		));
		exit();
	}

	public function send_message()
	{
		if (!$this->session->may('bieb')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		/*
		 *  an		an
			body	body
			mb		1
			sub		betr
		 */

		if ($last = (int)$this->mem->user($this->session->id(), 'mailbox-last')) {
			if ((time() - $last) < 15) {
				return array(
					'status' => 1,
					'script' => 'pulseError("Du kannst nur eine E-Mail pro 15 Sekunden versenden, bitte warte einen Augenblick...");'
				);
			}
		}

		$this->mem->userSet($this->session->id(), 'mailbox-last', time());

		if ($this->mailboxPermissions->mayMailbox($_POST['mb'])) {
			if ($mailbox = $this->mailboxGateway->getMailbox($_POST['mb'])) {
				$an = explode(';', $_POST['an']);
				$tmp = array();
				foreach ($an as $a) {
					$tmp[$a] = $a;
				}
				$an = $tmp;
				if (count($an) > 100) {
					return array(
						'status' => 1,
						'script' => 'pulseError("Zu viele Empfänger");'
					);
				}
				$attach = false;

				if (isset($_POST['attach']) && is_array($_POST['attach'])) {
					$attach = array();
					foreach ($_POST['attach'] as $a) {
						if (isset($a['name'], $a['tmp'])) {
							$tmp = str_replace(array('/', '\\'), '', $a['tmp']);
							$name = strtolower($a['name']);
							str_replace(array('ä', 'ö', 'ü', 'ß', ' '), array('ae', 'oe', 'ue', 'ss', '_'), $name);
							$name = preg_replace('/[^a-z0-9\-\.]/', '', $name);

							if (file_exists('data/mailattach/tmp/' . $tmp)) {
								$attach[] = array(
									'path' => 'data/mailattach/tmp/' . $tmp,
									'name' => $name
								);
							}
						}
					}
				}

				$this->libPlainMail(
					$an,
					array(
						'email' => $mailbox['name'] . '@' . PLATFORM_MAILBOX_HOST,
						'name' => $mailbox['email_name']
					),
					$_POST['sub'],
					$_POST['body'],
					$attach
				);

				$to = array();
				foreach ($an as $a) {
					if ($this->emailHelper->validEmail($a)) {
						$t = explode('@', $a);

						$to[] = array(
							'personal' => $a,
							'mailbox' => $t[0],
							'host' => $t[1]
						);
					}
				}

				if ($this->mailboxGateway->saveMessage(
					$_POST['mb'],
					2,
					json_encode(array(
						'host' => PLATFORM_MAILBOX_HOST,
						'mailbox' => $mailbox['name'],
						'personal' => $mailbox['email_name']
					)),
					json_encode($to),
					$_POST['sub'],
					$_POST['body'],
					nl2br($_POST['body']),
					date('Y-m-d H:i:s'),
					'',
					1
				)
				) {
					$this->mailboxGateway->setAnswered(
						$_POST['reply'],
						$this->mailboxPermissions->mayMailbox($this->mailboxGateway->getMailboxId($_POST['reply']))
					);

					return array(
						'status' => 1,
						'script' => '
									pulseInfo("' . $this->translationHelper->s('send_success') . '");
									mb_clearEditor();
									mb_closeEditor();'
					);
				}
			}
		}
	}

	public function fmail()
	{
		if (!$this->session->may('bieb') || !$this->mailboxGateway->mayMessage($_GET['id'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$html = $this->model->getVal('body_html', 'mailbox_message', $_GET['id']);

		if (strpos(strtolower($html), '<body') === false) {
			$html = '<html><head><style type="text/css">html{height:100%;background-color: white;}body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana,sans-serif;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style></head><body>' . $html . '</body></html>';
		} else {
			$html = str_replace(array('<body', '<BODY', '<Body'), '<body', $html);
			$html = str_replace(array('<head>', '<HEAD>', '<Head>'), '<head><style type="text/css">html{height:100%;background-color: white;}body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style>', $html);
		}

		// $html = str_replace('href="mailto:', 'onclick="parent.mb_new_message(this.href.replace(\'mailto:\',\'\'));return false;" href="mailto:', $html);

		echo $html;
		exit();
	}

	public function loadMail()
	{
		if (!$this->session->may('bieb') || !$this->mailboxPermissions->mayMessage($_GET['id'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($mail = $this->mailboxGateway->getMessage(
			$_GET['id'], $this->mailboxPermissions->mayMailbox($this->mailboxGateway->getMailboxId($_GET['id'])))) {
			$this->mailboxGateway->setRead($_GET['id'], 1);
			$mail['attach'] = trim($mail['attach']);
			if (!empty($mail['attach'])) {
				$mail['attach'] = json_decode($mail['attach'], true);
			}

			return array(
				'status' => 1,
				'html' => $this->view->message($mail),
				'append' => '#message-body',
				'script' => '
		
				bodymin = 80;
				if($("#mailattch").length > 0)
				{
					bodymin += 40;
				}
		
				$("#message-body").dialog("option",{
					title: \'' . $this->sanitizerService->jsSafe($mail['subject']) . '\',
					height: ($( window ).height()-40)
				});
				$(".mailbox-body").css({
					"height" : ($("#message-body").height()-bodymin)+"px",
					"overflow":"auto"
				});
				$(".mailbox-body-loader").css({
					"height" : ($("#message-body").height()-bodymin)+"px",
					"overflow":"auto"
				});
				$("#message-body").dialog("open");
				$("tr#message-' . (int)$_GET['id'] . ' .read-0,tr#message-' . (int)$_GET['id'] . '").addClass("read-1").removeClass("read-0");'
			);
		}
	}

	private function libPlainMail($to, $from, $subject, $message, $attach = false)
	{
		if (is_array($to) && !isset($to['name'])) {
			$email = $to;
		} elseif (is_array($to) && isset($to['email'])) {
			$email = $to['email'];
			$name = $to['name'];
		} else {
			$email = $to;
			$name = $to;
		}

		$from_email = $from;
		$from_name = $from;
		if (is_array($from)) {
			$from_email = $from['email'];
			$from_name = $from['name'];
		}

		$mail = new AsyncMail($this->mem);

		$mail->setFrom($from_email, $from_name);

		if (is_array($email)) {
			foreach ($email as $e) {
				if ($this->emailHelper->validEmail($e)) {
					$this->mailboxGateway->addContact($e, $this->session->id());
					$mail->addRecipient($e);
				}
			}
		} else {
			$mail->addRecipient($email);
		}

		$mail->setSubject($subject);

		$message = str_replace(array('<br>', '<br/>', '<br />', '<p>', '</p>', '</p>'), "\r\n", $message);
		$message = strip_tags($message);

		$html = nl2br($message);
		$mail->setHTMLBody($html);

		$plainBody = $this->sanitizerService->htmlToPlain($html);
		$mail->setBody($plainBody);

		if ($attach !== false) {
			foreach ($attach as $a) {
				$mail->addAttachment($a['path'], $a['name']);
			}
		}
		$mail->send();
	}

	public function attach_allow($filename, $mime)
	{
		if (strlen($filename) < 300) {
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$notallowed = array(
				'php' => true,
				'html' => true,
				'htm' => true,
				'php5' => true,
				'php4' => true,
				'php3' => true,
				'php2' => true,
				'php1' => true
			);
			$notallowed_mime = array();

			if (!isset($notallowed[$ext]) && !isset($notallowed_mime[$mime])) {
				return true;
			}
		}

		return false;
	}
}
