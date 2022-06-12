<?php

namespace Foodsharing\Modules\Settings;

use DateTime;
use DateTimeZone;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Mails\MailsGateway;

class SettingsXhr extends Control
{
	private FoodsaverGateway $foodsaverGateway;
	private LoginGateway $loginGateway;
	private SettingsGateway $settingsGateway;
	private MailsGateway $mailsGateway;

	public function __construct(
		SettingsView $view,
		SettingsGateway $settingsGateway,
		FoodsaverGateway $foodsaverGateway,
		LoginGateway $loginGateway,
		MailsGateway $mailsGateway
	) {
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->loginGateway = $loginGateway;
		$this->settingsGateway = $settingsGateway;
		$this->mailsGateway = $mailsGateway;

		parent::__construct();

		if (!$this->session->may()) {
			return;
		}
	}

	public function changemail()
	{
		if (!$this->session->may()) {
			echo '0';
			exit();
		}

		$dia = new XhrDialog();
		$dia->setTitle($this->translator->trans('settings.email'));

		$dia->addContent($this->view->changeMail());

		$dia->addButton($this->translator->trans('settings.email'), 'ajreq("changemail2", {email: $("#newmail").val()});');

		return $dia->xhrout();
	}

	public function changemail2(): array
	{
		$emailAddress = $_GET['email'];
		if (!$this->emailHelper->validEmail($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('settings.changemail.invalid') . '");',
			];
		}
		if ($this->emailHelper->isFoodsharingEmailAddress($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('settings.changemail.domain') . '");',
			];
		}
		if ($this->foodsaverGateway->emailExists($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('settings.changemail.occupied') . '");',
			];
		}

		$token = bin2hex(random_bytes(16));
		$this->settingsGateway->addNewMail($this->session->id(), $emailAddress, $token);

		$fs = $this->foodsaverGateway->getFoodsaverBasics($this->session->id());
		if (!$fs) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translator->trans('error_unexpected') . '");',
			];
		}

		$this->mailsGateway->removeBounceForMail($emailAddress);
		$this->emailHelper->tplMail('user/change_email', $emailAddress, [
			'anrede' => $this->translator->trans('salutation.' . $fs['geschlecht']),
			'name' => $fs['name'],
			'link' => BASE_URL . '/?page=settings&sub=general&newmail=' . $token
		], false, true);

		return [
			'status' => 1,
			'script' => 'pulseInfo("' . $this->translator->trans('settings.changemail.sent') . '",{sticky:true});'
		];
	}

	public function changemail3()
	{
		$email = $this->settingsGateway->getMailChange($this->session->id());
		if (!$email) {
			return;
		}

		$dia = new XhrDialog();
		$dia->setTitle($this->translator->trans('settings.email'));

		$dia->addContent($this->view->changemail3($email));

		$dia->addButton('Abbrechen', 'ajreq(\'abortchangemail\');$(\'#' . $dia->getId() . '\').dialog(\'close\');');
		$dia->addButton('Bestätigen', 'ajreq(\'changemail4\',{pw:$(\'#passcheck\').val(),did:\'' . $dia->getId() . '\'});');

		return $dia->xhrout();
	}

	public function abortchangemail(): void
	{
		$this->settingsGateway->abortChangemail($this->session->id());
	}

	public function changemail4(): array
	{
		$fsId = $this->session->id();
		$currentEmail = $this->foodsaverGateway->getEmailAddress($fsId);

		if (!$currentEmail || !$this->loginGateway->checkClient($currentEmail, $_GET['pw'])) {
			return [
				'status' => 1,
				'script' => '
					pulseError("' . $this->translator->trans('settings.changemail.passfail') . '");
					$("#passcheck").val("");
					$("#passcheck")[0].focus();
				',
			];
		}

		$newEmail = $this->settingsGateway->getMailChange($fsId);
		if (!$newEmail) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('error_unexpected') . '");',
			];
		}

		if ($this->settingsGateway->changeMail($fsId, $newEmail) == 0) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translator->trans('settings.changemail.occupied') . '");',
			];
		}

		$this->settingsGateway->logChangedSetting(
			$fsId,
			['email' => $this->session->user('email')],
			['email' => $newEmail],
			['email']
		);
		$dialogId = strip_tags($_GET['did']);

		return [
			'status' => 1,
			'script' => '
				pulseInfo("' . $this->translator->trans('settings.changemail.done') . '");
				$("#' . $dialogId . '").dialog("close");
			',
		];
	}

	public function sleepmode(): void
	{
		$xhr = new Xhr();
		$xhr->setStatus(0);
		if (!isset($_POST['status'])) {
			$xhr->send();

			return;
		}
		$status = (int)$_POST['status'];

		$from = '';
		$until = '';
		$msg = $this->getPostString('msg') ?: '';
		if ($status == 0) {
			$msg = '';
		}

		if ($status == SleepStatus::TEMP) {
			if (!isset($_POST['from']) || !isset($_POST['until'])) {
				$xhr->send();

				return;
			}
			$date_from = DateTime::createFromFormat('d.m.Y', $_POST['from']);
			$from = $date_from->format('Y-m-d H:i:s');
			$date_until = DateTime::createFromFormat('d.m.Y', $_POST['until']);
			$until = $date_until->format('Y-m-d H:i:s');
			$now = new DateTime('now', new DateTimeZone('Europe/Prague'));
			if ($date_from > $now) { // Sleep after today, not yet sleeping
				$status = 0;
			}
		}

		$this->settingsGateway->updateSleepMode($this->session->id(), $status, $from, $until, $msg);

		$xhr->setStatus(1);
		$xhr->send();
	}
}
