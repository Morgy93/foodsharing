<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Core\View;
use Foodsharing\Permissions\EventPermissions;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\NumberHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventView extends View
{
	private $eventPermissions;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		EventPermissions $eventPermissions,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		NumberHelper $numberHelper,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		TranslatorInterface $translator
	) {
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$numberHelper,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
		$this->eventPermissions = $eventPermissions;
	}

	public function eventForm($bezirke)
	{
		global $g_data;

		$g_data = array_merge([
			'online_type' => 1,
			'invite' => 1,
			'invitesubs' => 1
		], $g_data);

		$start_time = ['hour' => 15, 'min' => 0];
		$end_time = ['hour' => 16, 'min' => 0];

		if (isset($g_data['start'])) {
			$start_time = [
				'hour' => (int)date('H', $g_data['start_ts']),
				'min' => (int)date('i', $g_data['start_ts']),
			];
			$g_data['date'] = $g_data['start'];
		}

		if (isset($g_data['end'])) {
			$end_time = [
				'hour' => (int)date('H', $g_data['end_ts']),
				'min' => (int)date('i', $g_data['end_ts']),
			];
			$g_data['dateend'] = $g_data['end'];
		}

		$title = $this->translator->trans('events.create.title');
		$this->pageHelper->addJs('
			$("#online_type").on("change", function () {
				if ($(this).val() == 1) {
					$("#anschrift-wrapper").addClass("required");
					$("#plz-wrapper").addClass("required");
					$("#ort-wrapper").addClass("required");
					$("#location_name-wrapper").next().show();
					$("#anschrift-wrapper, #plz-wrapper, #ort-wrapper").show();
				} else {
					$("#anschrift-wrapper").removeClass("required");
					$("#plz-wrapper").removeClass("required");
					$("#ort-wrapper").removeClass("required");
					$("#location_name-wrapper").next().hide();
					$("#anschrift-wrapper, #plz-wrapper, #ort-wrapper").hide();
				}
			});

			var dateend_wrapper = document.getElementById("dateend-wrapper");

			$("#date").after(
				\'<label class="addend"><input type="checkbox" name="addend" id="addend" value="1" /> '
			. $this->translator->trans('events.create.multiday') .
			'</label>\'
			);

            dateend_wrapper.style.display = "none";

            var dateend = new Date(document.getElementById("dateend").value.split(" ")[0]);
			var datestart = new Date(document.getElementById("date").value.split(" ")[0]);
			datestart.setDate(datestart.getDate() + 1);

			if (dateend >= datestart)
			{
                document.getElementById("addend").checked = true;
                dateend_wrapper.style.display = "block";
			}

			dateend_wrapper.classList.remove("required");

			$("#addend").on("change", function () {
				if ($("#addend:checked").length > 0) {
					dateend_wrapper.style.display = "block";
					dateend_wrapper.classList.add("required");
				} else {
					dateend_wrapper.style.display = "none";
					dateend_wrapper.classList.remove("required");
				}
			});

			');

		$regions = '';
		$groups = '';
		$sid = 0;
		if (isset($g_data['bezirk_id'])) {
			$sid = (int)$g_data['bezirk_id'];
		} elseif (isset($_GET['bid'])) {
			$sid = (int)$_GET['bid'];
		}
		if (is_array($bezirke)) {
			foreach ($bezirke as $b) {
				$sel = '';

				if ($b['id'] == $sid) {
					$sel = ' selected="selected"';
				}

				if (UnitType::isGroup($b['type'])) {
					$groups .= '<option value="' . $b['id'] . '"' . $sel . '>' . $b['name'] . '</option>';
				} else {
					$regions .= '<option value="' . $b['id'] . '"' . $sel . '>' . $b['name'] . '</option>';
				}
			}
		}

		if (!empty($groups)) {
			$groups = '<optgroup label="' . $this->translator->trans('events.create.groups') . '">' . $groups . '</optgroup>';
		}
		if (!empty($regions)) {
			$regions = '<optgroup label="' . $this->translator->trans('events.create.regions') . '">' . $regions . '</optgroup>';
		}

		$this->pageHelper->addJs('
			$("#public").on("change", function () {
				if ($("#public:checked").length > 0) {
					$("#input-wrapper").hide();
				} else {
					$("#input-wrapper").show();
				}
			});
		');

		$delinvites = '';
		if (isset($_GET['id'])) {
			$delinvites = '<br />'
				. '<label>'
				. '<input type="checkbox" name="delinvites" id="delinvites" value="1" /> '
				. $this->translator->trans('events.create.delinvites')
				. '</label>';
		}

		$bezirkchoose = $this->v_utils->v_input_wrapper(
			$this->translator->trans('events.create.who'),
			'<select class="input select value" name="bezirk_id" id="bezirk_id">
				' . $groups . '
				' . $regions . '
			</select>
			<p style="padding-top:10px;">
				<label><input type="checkbox" name="invite" id="invite" checked="checked" value="' . $g_data['invite'] . '" /> '
				. $this->translator->trans('events.create.inviteAll') .
				'</label>
				<br />
				<label><input type="checkbox" name="invitesubs" id="invitesubs" checked="checked" value="' . $g_data['invitesubs'] . '" /> '
				. $this->translator->trans('events.create.cascading') .
				'</label>
				' . $delinvites . '
			</p>
		'
		);

		$public_el = '';

		if ($this->session->may('orga')) {
			$chk = '';
			if (isset($g_data['public']) && $g_data['public'] == 1) {
				$chk = ' checked="checked"';
				$this->pageHelper->addJs('$("#input-wrapper").hide();');
			}
			$public_el = $this->v_utils->v_input_wrapper(
				$this->translator->trans('events.create.public'),
				'<label><input id="public" type="checkbox" name="public" value="1"' . $chk . ' /> '
					. $this->translator->trans('events.create.isPublic') .
					'</label>'
			);
		}

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			if (isset($g_data[$i])) {
				$latLonOptions[$i] = $g_data[$i];
			} else {
				$latLonOptions[$i] = '';
			}
		}
		if (isset($g_data['lat'], $g_data['lon'])) {
			$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];
		} else {
			$latLonOptions['location'] = ['lat' => 0, 'lon' => 0];
		}

		return $this->v_utils->v_field($this->v_utils->v_form('eventsss', [
			$public_el,
			$bezirkchoose,
			$this->v_utils->v_form_text('name', ['required' => true]),
			$this->v_utils->v_form_date('date', ['required' => true, 'label' => $this->translator->trans('events.create.date')]),
			$this->v_utils->v_form_date('dateend', ['required' => true]),
			$this->v_utils->v_input_wrapper(
				$this->translator->trans('events.create.starttime'),
				$this->v_utils->v_form_time('time_start', $start_time)
			),
			$this->v_utils->v_input_wrapper(
				$this->translator->trans('events.create.endtime'),
				$this->v_utils->v_form_time('time_end', $end_time)
			),
			$this->v_utils->v_form_textarea('description', [
				'desc' => $this->translator->trans('events.create.desc'),
				'required' => true,
			]),
			$this->v_utils->v_form_select('online_type', ['values' => [
				['id' => 1, 'name' => $this->translator->trans('events.create.offline')],
				['id' => 0, 'name' => $this->translator->trans('events.create.mumble')],
				['id' => 2, 'name' => $this->translator->trans('events.create.online')],
			]]),
			$this->v_utils->v_form_text('location_name', ['required' => true]),
			$this->latLonPicker('latLng', $latLonOptions),
			$this->v_utils->v_info($this->translator->trans('events.create.info'))
		], ['submit' => $this->translator->trans('button.save')]), $title, ['class' => 'ui-padding']);
	}

	public function eventPanel(array $event, bool $mayEdit = false): string
	{
		return $this->vueComponent('event-panel', 'EventPanel', [
			'eventId' => $event['id'],
			'regionName' => $event['regionName'],
			'start' => $event['start'],
			'end' => $event['end'],
			'title' => $event['name'],
			'mayEdit' => $mayEdit,
			'status' => $event['status'] ?? '',
			'e' => $event,
		]);
	}

	public function invites(array $invites): string
	{
		$out = '';

		if (!empty($invites['accepted'])) {
			$avatars = $this->placeFsAvatars($invites['accepted'], 60);
			$out .= $this->v_utils->v_field(
				$avatars,
				$this->translator->trans('events.acceptedCount', ['{count}' => count($invites['accepted'])])
			);
		}

		if (!empty($invites['maybe'])) {
			$avatars = $this->placeFsAvatars($invites['maybe'], 54);
			$out .= $this->v_utils->v_field(
				$avatars,
				$this->translator->trans('events.maybeCount', ['{count}' => count($invites['maybe'])])
			);
		}

		if (!empty($invites['invited'])) {
			$avatars = $this->placeFsAvatars($invites['invited'], 54);
			$out .= $this->v_utils->v_field(
				$avatars,
				$this->translator->trans('events.invitedCount', ['{count}' => count($invites['invited'])])
			);
		}

		return $out;
	}

	private function placeFsAvatars(array $foodsavers, int $maxNumberOfAvatars): string
	{
		if (empty($foodsavers)) {
			return '';
		}

		$out = '<ul class="event-avatars p-1">';

		if (count($foodsavers) > $maxNumberOfAvatars) {
			shuffle($foodsavers);
			$foodsaverDisplayed = array_slice($foodsavers, 0, $maxNumberOfAvatars);
			$howManyMore = count($foodsavers) - $maxNumberOfAvatars;
		} else {
			$foodsaverDisplayed = $foodsavers;
			$howManyMore = 0;
		}

		foreach ($foodsaverDisplayed as $fs) {
			$out .= '
			<li>
				<a title="' . $fs['name'] . '" href="/profile/' . (int)$fs['id'] . '">
					<img src="' . $this->imageService->img($fs['photo']) . '" class="corner-all">
				</a>
			</li>';
		}
		$out .= '</ul>';
		if ($howManyMore > 0) {
			$out .=
				'<div class="p-1 pl-2">'
				. $this->translator->trans('events.morePeople', ['{count}' => $howManyMore])
				. '</div>';
		}

		return $out;
	}

	public function event(array $event): string
	{
		return $this->v_utils->v_field(
			'<p>' . nl2br(
				$this->routeHelper->autolink($event['description'])
			) . '</p>',
			$this->translator->trans('events.description'),
			['class' => 'ui-padding event-description']
		);
	}

	public function locationMumble()
	{
		return $this->v_utils->v_field(
			'
		<p style="text-align: center;">
			<a target="_blank" href="https://wiki.foodsharing.de/Mumble">'
				. '<img src="/img/mlogo.png" alt="Mumble" />' .
				'</a>
		</p>
		<p> ' . $this->translator->trans('events.mumble.text') . '</p>
		<p> ' . $this->translator->trans('events.mumble.location') . '</p>
		<p> ' . $this->translator->trans('events.mumble.guide') . '</p>
',
			$this->translator->trans('events.mumble.title'),
			['class' => 'ui-padding']
		);
	}
}
