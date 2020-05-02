<?php

namespace Foodsharing\Modules\Email;

use DOMDocument;
use Exception;
use Flourish\fImage;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\Sanitizer;

class EmailControl extends Control
{
	private $storeGateway;
	private $foodsaverGateway;
	private $emailGateway;
	private $regionGateway;
	private $sanitizerService;
	private $mailboxGateway;
	private $identificationHelper;
	private $dataHelper;
	private $newsletterEmailPermissions;

	public function __construct(
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		EmailGateway $emailGateway,
		RegionGateway $regionGateway,
		Sanitizer $sanitizerTransactions,
		MailboxGateway $mailboxGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		NewsletterEmailPermissions $newsletterEmailPermissions
	) {
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->emailGateway = $emailGateway;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerTransactions;
		$this->mailboxGateway = $mailboxGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->newsletterEmailPermissions = $newsletterEmailPermissions;

		parent::__construct();

		if (!$this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		$this->handleEmail();
		$this->pageHelper->addBread($this->translationHelper->s('mailinglist'), '/?page=email');

		if ($emailstosend = $this->emailGateway->getEmailsToSend($this->session->id())) {
			$this->pageHelper->addContent($this->v_email_statusbox($emailstosend));
		}

		$recip = '';
		if ($this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
			$recip = $this->v_utils->v_form_recip_chooser();
		} elseif ($this->session->isAmbassador()) {
			$recip = $this->v_utils->v_form_recip_chooser_mini();
		}
		global $g_data;
		if (!isset($g_data['message'])) {
			$g_data['message'] = '<p><strong>{ANREDE} {NAME}</strong><br /><br /><br />';
		}

		$boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'));
		foreach ($boxes as $key => $b) {
			$boxes[$key]['name'] = $b['name'] . '@' . NOREPLY_EMAIL_HOST;
		}
		$this->pageHelper->addContent($this->v_utils->v_form('Nachrichten Verteiler', [
			$this->v_utils->v_field(
				$recip .
				$this->v_utils->v_form_select('mailbox_id', ['values' => $boxes, 'required' => true]) .
				$this->v_utils->v_form_text('subject', ['required' => true]) .
				$this->v_utils->v_form_file('attachement'),

				$this->translationHelper->s('mailing_list'),
				['class' => 'ui-padding']
			),
			$this->v_utils->v_field($this->v_utils->v_form_tinymce('message', ['nowrapper' => true, 'type' => 'email']), $this->translationHelper->s('message'))
		], ['submit' => 'Zum Senden Vorbereiten']));

		$this->pageHelper->addStyle('#testemail{width:91%;}');

		$g_data['testemail'] = $this->foodsaverGateway->getEmailAddress($this->session->id());

		$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_form_text('testemail') . $this->v_utils->v_input_wrapper('', '<a class="button" href="#" onclick="trySendTestEmail();return false;">Test-Mail senden</a>'), 'Newsletter Testen', ['class' => 'ui-padding']), CNT_RIGHT);

		$this->pageHelper->addJs("$('#rightmenu').menu();");
		$this->pageHelper->addContent($this->v_utils->v_field('<div class="ui-padding">' . $this->translationHelper->s('personal_styling_desc') . '</div>', $this->translationHelper->s('personal_styling')), CNT_RIGHT);

		$this->pageHelper->addContent('
	<div id="dialog-confirm" title="E-Mail senden?" style="display:none">
	<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>' . $this->translationHelper->s('shure') . '</p>
	</div>
	<h3 class="head ui-widget-header ui-corner-top">von Dir gesendete Mails</h3>
	<div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom">
	<ul id="rightmenu">', CNT_RIGHT);

		$i = 0;
		$divs = '';
		if ($mails = $this->emailGateway->getSendMails($this->session->id())) {
			foreach ($mails as $m) {
				++$i;
				$this->pageHelper->addContent('<li><a href="#" onclick="$(\'#right-' . $i . '\').dialog(\'open\');return false;">' . date('d.m.', strtotime($m['zeit'])) . ' ' . $m['name'] . '</a></li>', CNT_RIGHT);
				$divs .= '<div id="right-' . $i . '" style="display:none;">' . nl2br($m['message']) . '</div>';
				$this->pageHelper->addJs('$("#right-' . $i . '").dialog({autoOpen:false,title:"' . $this->sanitizerService->jsSafe($m['name'], '"') . '",modal:true});');
			}
		}
		$this->pageHelper->addContent('</ul></div>' . $divs, CNT_RIGHT);
	}

	private function handleEmail()
	{
		if ($this->submitted()) {
			$betreff = $_POST['subject'];
			$nachricht = $_POST['message'];
			$mailbox_id = $_POST['mailbox_id'];

			$nachricht = $this->handleImages($nachricht);

			$data = $this->dataHelper->getPostData();

			$foodsaver = [];

			if ($this->session->isAmbassador() || $this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
				if ($data['recip_choose'] == 'bezirk') {
					$region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentRegionId());
					$foodsaver = $this->foodsaverGateway->getEmailAddressesFromMainRegions($region_ids);
				} elseif ($data['recip_choose'] == 'botschafter') {
					$foodsaver = $this->foodsaverGateway->getActiveAmbassadors();
				} elseif ($data['recip_choose'] == 'orgateam') {
					$foodsaver = $this->foodsaverGateway->getOrgateam();
				}
			}
			if ($this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
				if ($data['recip_choose'] == 'all') {
					$foodsaver = $this->foodsaverGateway->getEmailAddresses(Role::FOODSAVER);
				} elseif ($data['recip_choose'] == 'newsletter') {
					$foodsaver = $this->foodsaverGateway->getNewsletterSubscribersEmailAddresses(Role::FOODSAVER);
				} elseif ($data['recip_choose'] == 'newsletter_all') {
					$foodsaver = $this->foodsaverGateway->getNewsletterSubscribersEmailAddresses();
				} elseif ($data['recip_choose'] == 'newsletter_only_foodsharer') {
					$foodsaver = $this->foodsaverGateway->getNewsletterSubscribersEmailAddresses(Role::FOODSHARER, Role::FOODSHARER);
				} elseif ($data['recip_choose'] == 'all_no_botschafter') {
					$foodsaver = $this->foodsaverGateway->getFoodsaversWithoutAmbassadors();
				} elseif ($data['recip_choose'] == 'storemanagers') {
					$foodsaver = $this->storeGateway->getAllStoreManagers();
				} elseif ($data['recip_choose'] == 'storemanagers_and_ambs') {
					$foodsaver1 = $this->storeGateway->getAllStoreManagers();
					$foodsaver2 = $this->foodsaverGateway->getActiveAmbassadors();
					$tmp = array_merge($foodsaver1, $foodsaver2);
					$foodsaver = [];
					foreach ($tmp as $t) {
						$foodsaver[$t['id']] = $t;
					}
				} elseif ($data['recip_choose'] == 'manual') {
					$foodsaver = $data['recip_choosemanual'];
					str_replace(["\r"], '', $foodsaver);
					$foodsaver = explode("\n", $foodsaver);

					$bezirk = $this->regionGateway->getRegion($this->session->getCurrentRegionId());

					$count = 0;
					foreach ($foodsaver as $i => $fs) {
						$arr = explode(';', $fs);

						foreach ($arr as $y => $a) {
							$arr[$y] = trim($a);
						}

						$name = '';
						$email = $arr[0];

						if (isset($arr[1])) {
							$name = $arr[1];
						}

						if ($this->emailHelper->validEmail($email)) {
							$this->emailHelper->libmail($bezirk, $email, $betreff, str_replace('{NAME}', $name, $nachricht));
							++$count;
						} else {
							unset($foodsaver[$i]);
						}
					}

					$this->flashMessageHelper->info('Die E-Mail wurde erfolgreich an ' . $count . ' E-Mail-Adressen gesendet');

					$foodsaver = [];
				} elseif ($data['recip_choose'] == 'filialbez') {
					// TODO can probably be removed
					$foodsaver = $this->storeGateway->getEmailBiepBez($data['recip_choose-choose']);
				} elseif (isset($data['recip_choose-choose'])) {
					if ($data['recip_choose'] == 'choosebot') {
						$foodsaver = $this->foodsaverGateway->getRegionAmbassadorsEmailAddresses($data['recip_choose-choose']);
					} else {
						$foodsaver = $this->foodsaverGateway->getEmailAddressesFromRegions($data['recip_choose-choose']);
					}
				}
			} else {
				$region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentRegionId());
				$foodsaver = $this->foodsaverGateway->getEmailAddressesFromMainRegions($region_ids);
			}

			if (!empty($foodsaver)) {
				$attach = $this->handleAttach('attachement');

				$out = [];
				foreach ($foodsaver as $fs) {
					$out[$fs['id']] = $fs;
				}
				$foodsaver = [];
				foreach ($out as $o) {
					$foodsaver[] = $o;
				}
				$this->emailGateway->initEmail($this->session->id(), $mailbox_id, $foodsaver, $nachricht, $betreff, $attach);
				$this->routeHelper->goPage();
			} elseif ($data['recip_choose'] != 'manual') {
				$this->flashMessageHelper->error('In den ausgew&auml;hlten Bezirken gibt es noch keine Foodsaver');
			}
		}
	}

	private function handleAttach($name)
	{
		if (isset($_FILES[$name]) && $_FILES[$name]['size'] > 0) {
			$datei = $_FILES[$name]['tmp_name'];
			$size = $_FILES[$name]['size'];
			$datein = $_FILES[$name]['name'];
			$datein = strtolower($datein);
			$datein = str_replace('.jpeg', '.jpg', $datein);
			$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));

			$new_name = bin2hex(random_bytes(16)) . $dateiendung;
			move_uploaded_file($datei, './data/attach/' . $new_name);

			return [
				'name' => $datein,
				'path' => './data/attach/' . $new_name,
				'uname' => $new_name,
				'mime' => mime_content_type('./data/attach/' . $new_name),
				'size' => $size
			];
		}

		return false;
	}

	private function v_email_statusbox($mail)
	{
		$out = '';

		$recip = $this->emailGateway->getRecipient($mail['id']);

		$id = $this->identificationHelper->id('mailtosend');

		$this->pageHelper->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth : 600,
				scrolling :"auto",
				closeClick : false,
				helpers : {
				  overlay : {closeClick: false}
				}
			});

			$("#' . $id . '-link").trigger("click");

			$("#' . $id . '-continue").button().on("click", function(){
				' . $id . '_continue_xhr();
				return false;
			});

			$("#' . $id . '-abort").button().on("click", function(){
				showLoader();
				$.ajax({
					url:"/xhr.php?f=abortEmail",
					data:{id:' . (int)$mail['id'] . '},
					complete:function(){hideLoader();closeBox();}
				});
			});


		');

		$this->pageHelper->addJsFunc('
		function ' . $id . '_continue_xhr()
		{
				showLoader();
				$.ajax({
						dataType:"json",
						url:"/xhr.php?f=continueMail&id=' . (int)$mail['id'] . '",
						success : function(data){
							$("#' . $id . '-continue").hide();
							if(data.status == 1)
							{
								$("#' . $id . '-comment").html(data.comment);
								$("#' . $id . '-left").html(data.left);
								' . $id . '_continue_xhr();
							}
							else if(data.status == 2)
							{
								$("#' . $id . '-comment").html(data.comment);
								hideLoader();
							}
							else
							{
								alert("Du hast nich nie nötigen Rechte E-Mails zu versenden");
							}
						}
				});
			}');

		$style = '';
		if (count($recip) > 50) {
			$style = ' style="height:100px;overflow:auto;font-size:10px;background-color:#fff;color:#333;padding:5px;"';
		}

		$this->pageHelper->addHidden('
				<a id="' . $id . '-link" href="#' . $id . '">&nbsp;</a>
				<div class="popbox" id="' . $id . '">
					<h3>E-Mail senden</h3>
					<p class="subtitle">Es sind noch <span id="' . $id . '-left">' . $mail['anz'] . '</span> E-Mails zu versenden</p>

					<div id="' . $id . '-comment">
						' . $this->v_utils->v_input_wrapper('Empfänger', '<div' . $style . '>' . implode(', ', $recip) . '</div>') . '
						' . $this->v_utils->v_input_wrapper($this->translationHelper->s('subject'), $mail['name']) . '
						' . $this->v_utils->v_input_wrapper($this->translationHelper->s('message'), nl2br($mail['message'])) . '

					</div>
					<a id="' . $id . '-continue" href="#">Mit dem Senden weitermachen</a> <a id="' . $id . '-abort" href="#">Senden Abbrechen</a>
				</div>');

		return $out;
	}

	private function handleImages($body)
	{
		if (strpos($body, '<') === false) {
			return $body;
		}

		$doc = new DOMDocument();
		$doc->loadHTML($body);
		$tags = $doc->getElementsByTagName('img');

		try {
			foreach ($tags as $tag) {
				$src = $tag->getAttribute('src');
				$wwith = $tag->getAttribute('width');
				$hheight = $tag->getAttribute('height');
				$iname = $tag->getAttribute('name');

				// prevent path traversal attacks
				$src = preg_replace('/%/', '', $src);
				$src = preg_replace('/\.+/', '.', $src);
				$iname = preg_replace('/%/', '', $iname);
				$iname = preg_replace('/\.+/', '.', $iname);

				if (!empty($wwith) || !empty($hheight)) {
					$old_filepath = '';

					$file = explode('/', $src);
					$filename = end($file);

					if (strpos($src, 'images/upload/') !== false) {
						$old_filepath = explode('images/upload', $src);
						$old_filepath = end($old_filepath);
					} elseif (!empty($iname) && strpos($iname, '/') !== false) {
						$old_filepath = $iname;
					}

					$file = 'images/upload' . $old_filepath;

					if (file_exists($file) && !is_dir($file)) {
						$ffile = explode('/', $old_filepath);
						$filename = end($ffile);

						$new_path = 'images/newsletter/';
						$new_filename = $filename;
						$y = 1;

						while (file_exists($new_path . $new_filename)) {
							$new_filename = $y . '-' . $filename;
							++$y;
						}
						copy($file, $new_path . $new_filename);
						$fimage = new fImage($new_path . $new_filename);
						if (!empty($src) && $width = $tag->getAttribute('width') && $width < 2000) {
							$fimage->resize($width, 0);
						} elseif (!empty($src) && $height = $tag->getAttribute('height') && $height < 2000) {
							$fimage->resize(0, $height);
						}
						$fimage->saveChanges();
						$tag->setAttribute('src', BASE_URL . '/' . $new_path . $new_filename);
						$tag->setAttribute('name', $old_filepath);
						$tag->removeAttribute('width');
						$tag->removeAttribute('height');
					}
				} elseif (substr($src, 0, 7) != 'http://' && substr($src, 0, 8) != 'https://') {
					$tag->setAttribute('src', BASE_URL . '/' . $src);
				}
			}

			$html = $doc->saveHTML();
			$html = explode('<body>', $html);
			$html = end($html);
			$html = explode('</body>', $html);
			$html = $html[0];

			return $html;
		} catch (Exception $e) {
			if ($this->session->isSiteAdmin()) {
				echo $e->getMessage();
				die();
			}

			return $body;
		}
	}
}
