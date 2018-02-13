<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;

class TeamXhr extends Control
{
	public function __construct(TeamModel $model, TeamView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	public function contact()
	{
		$xhr = new Xhr();

		if ($this->func->ipIsBlocked(120, 'contact')) {
			$xhr->addMessage('Du hast zu viele Nachrichten versendet, bitte warte einen Moment', 'error');
			$xhr->send();
		}

		if ($id = $this->getPostInt('id')) {
			if ($user = $this->model->getUser($id)) {
				$mail = new AsyncMail();

				if ($this->func->validEmail($_POST['email'])) {
					$mail->setFrom($_POST['email']);
				} else {
					$mail->setFrom(DEFAULT_EMAIL);
				}

				$msg = strip_tags($_POST['message']);
				$name = strip_tags($_POST['name']);

				$msg = 'Name: ' . $name . "\n\n" . $msg;

				$mail->setBody($msg);
				$mail->setHTMLBody(nl2br($msg));
				$mail->setSubject('foodsharing.de Kontaktformular Anfrage von ' . $name);

				$mail->addRecipient($user['email']);

				$mail->send();

				$xhr->addScript('$("#contactform").parent().parent().parent().fadeOut();');
				$xhr->addMessage($this->func->s('mail_send_success'), 'success');
				$xhr->send();
			}
		}

		$xhr->addMessage($this->func->s('error'), 'error');
		$xhr->send();
	}
}
