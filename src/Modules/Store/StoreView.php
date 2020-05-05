<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Foodsharing\Utility\WeightHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreView extends View
{
	private $weightHelper;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		WeightHelper $weightHelper,
		TranslatorInterface $translator
	) {
		$this->weightHelper = $weightHelper;
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
	}

	public function dateForm()
	{
		return
			'<div id="datepicker" style="height:220px;"></div>' .
			$this->v_utils->v_input_wrapper('Uhrzeit', $this->v_utils->v_form_time('time')) .
			$this->v_utils->v_form_select('fetchercount', ['selected' => 1, 'values' => [
				['id' => 1, 'name' => '1 Abholer/in'],
				['id' => 2, 'name' => '2 Abholer/innen'],
				['id' => 3, 'name' => '3 Abholer/innen'],
				['id' => 4, 'name' => '4 Abholer/innen'],
				['id' => 5, 'name' => '5 Abholer/innen'],
				['id' => 6, 'name' => '6 Abholer/innen'],
				['id' => 7, 'name' => '7 Abholer/innen'],
				['id' => 8, 'name' => '8 Abholer/innen']
			]]);
	}

	public function fetchHistory()
	{
		return $this->v_utils->v_form_daterange('daterange', [
				'content_after' => ' <a href="#" id="daterange_submit" class="button"><i class="fas fa-search"></i></a>'
			]) . '<div id="daterange_content"></div>';
	}

	public function fetchlist($history)
	{
		$out = '
			<ul class="linklist history">';
		$curdate = 0;
		foreach ($history as $h) {
			if ($curdate != $h['date']) {
				$out .= '<li class="title">' . $this->timeHelper->niceDate($h['date_ts']) . '</li>';
				$curdate = $h['date'];
			}
			$out .= '
				<li>
					<a class="corner-all" href="/profile/' . (int)$h['id'] . '">
						<span class="i"><img src="' . $this->imageService->img($h['photo']) . '" /></span>
						<span class="n">' . $h['name'] . ' ' . $h['nachname'] . '</span>
						<span class="t"></span>
						<span class="c"></span>
					</a>
				</li>';
		}

		$out .= '
			</ul>';

		return $out;
	}

	public function betrieb_form($region)
	{
		global $g_data;

		if (!isset($g_data['foodsaver'])) {
			$g_data['foodsaver'] = [$this->session->id()];
		}

		if (isset($g_data['stadt'])) {
			$g_data['ort'] = $g_data['stadt'];
		}
		if (isset($g_data['str'])) {
			$g_data['anschrift'] = $g_data['str'];
		}
		if (isset($g_data['hsnr'])) {
			$g_data['anschrift'] .= ' ' . $g_data['hsnr'];
		}

		$latLonOptions = [];

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			if (isset($g_data[$i])) {
				$latLonOptions[$i] = $g_data[$i];
			}
		}
		if (isset($g_data['lat'], $g_data['lon'])) {
			$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];
		} else {
			$latLonOptions['location'] = ['lat' => 0, 'lon' => 0];
		}

		$editExisting = !$this->identificationHelper->getAction('new');
		$fieldset = array_merge($editExisting ? [] : [
			$this->v_utils->v_form_text('name', ['required' => true]),
		], [
			$this->v_utils->v_bezirkChooser('bezirk_id', $region),
			$this->latLonPicker('LatLng', $latLonOptions),
		], $editExisting ? [] : [
			$this->v_utils->v_form_textarea('first_post'),
		]);

		return $this->v_utils->v_quickform($this->translator->trans('storeview.store'), $fieldset);
	}

	public function bubble(array $store): string
	{
		$b = $store;
		$verantwortlich = '<ul class="linklist">';
		foreach ($b['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$verantwortlich .= '
			<li><a style="background-color:transparent;" href="/profile/' . (int)$fs['id'] . '">' . $this->imageService->avatar($fs, 50) . '</a></li>';
			}
		}
		$verantwortlich .= '</ul>';

		$besonderheiten = '';

		$count_info = '';
		$activeFoodSaver = count($b['foodsaver']);
		$jumperFoodSaver = count($b['springer']);
		$count_info .= '<div>' . $this->translator->trans('storeview.teamInfo', [
			'{active}' => $activeFoodSaver,
			'{jumper}' => $jumperFoodSaver,
		]) . '</div>';
		$pickup_count = (int)$b['pickup_count'];
		if ($pickup_count > 0) {
			$count_info .= '<div>' . $this->translator->trans('storeview.pickupCount', [
				'{pickupCount}' => $pickup_count,
			]) . '</div>';
			$fetch_weight = round(floatval(
				$pickup_count * $this->weightHelper->mapIdToKilos($b['abholmenge'])
			), 2);
			$count_info .= '<div>' . $this->translator->trans('storeview.pickupWeight', [
				'{fetch_weight}' => $fetch_weight,
			]) . '</div>';
		}

		$time = strtotime($b['begin']);
		if ($time > 0) {
			$count_info .= '<div>' . $this->translator->trans('storeview.cooperation', [
				'{startTime}' => $this->translationHelper->s('month_' . (int)date('m', $time)) . ' ' . date('Y', $time),
			]) . '</div>';
		}

		if ((int)$b['public_time'] != 0) {
			$count_info .= '<div>' . $this->translator->trans('storeview.frequency', [
				'{freq}' => $this->translator->trans('storeview.frequency' . (int)$b['public_time']),
			]) . '</div>';
		}

		if (!empty($b['public_info'])) {
			$besonderheiten = $this->v_utils->v_input_wrapper(
				$this->translator->trans('storeview.info'),
				$b['public_info'],
				'bcntspecial'
			);
		}

		$bstatus = $this->v_utils->v_getStatusAmpel($b['betrieb_status_id']);
		$tstatus = $this->translator->trans('storeedit.fetch.teamStatus' . (int)$b['team_status']);
		$html = $this->v_utils->v_input_wrapper(
			$this->translator->trans('storeedit.store.status'),
			$bstatus . $count_info
		) . '
		' . $this->v_utils->v_input_wrapper(
			$this->translator->trans('storeview.managers'), $verantwortlich, 'bcntverantwortlich'
		) . '
		' . $besonderheiten . '<div class="ui-padding">
		' . $this->v_utils->v_info('<strong>' . $tstatus . '</strong>') . '</div>';

		return $html;
	}
}
