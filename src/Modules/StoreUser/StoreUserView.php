<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
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

class StoreUserView extends View
{
	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
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
	}

	public function u_legacyStoreTeamStatus(array $storeData): string
	{
		$this->pageHelper->addJs('
			$("#team_status").on("change", function(){
				var val = $(this).val();
				showLoader();
				$.ajax({
					method: "PATCH",
					url: "/api/stores/' . $storeData['id'] . '",
					data:  { \'teamStatus\': val},
					success: function() { hideLoader(); },
					error: function(xhr) { hideLoader(); pulseError("Error during status update (" + xhr.status + ")"); }
				});
			});
		');

		global $g_data;
		$g_data['team_status'] = $storeData['team_status'];

		$out = $this->v_utils->v_form_select('team_status', [
			'values' => [
				['id' => 0, 'name' => $this->translator->trans('store.team.isfull')],
				['id' => 1, 'name' => $this->translator->trans('menu.entry.helpwanted')],
				['id' => 2, 'name' => $this->translator->trans('menu.entry.helpneeded')]
			]
		]);

		return $out;
	}

	public function u_storeList($storeData, $title)
	{
		if (empty($storeData)) {
			return '';
		}

		$isRegion = false;
		$storeRows = [];
		foreach ($storeData as $i => $store) {
			$status = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);

			$storeRows[$i] = [
				['cnt' => '<a class="linkrow ui-corner-all" href="/?page=fsbetrieb&id=' . $store['id'] . '">' . $store['name'] . '</a>'],
				['cnt' => $store['str']],
				['cnt' => $store['plz']],
				['cnt' => $status]
			];

			if (isset($store['bezirk_name'])) {
				$storeRows[$i][] = ['cnt' => $store['bezirk_name']];
				$isRegion = true;
			}
		}

		$head = [
			['name' => $this->translator->trans('storelist.name'), 'width' => 180],
			['name' => $this->translator->trans('storelist.addressdata')],
			['name' => $this->translator->trans('storelist.zipcode'), 'width' => 90],
			['name' => $this->translator->trans('storelist.status'), 'width' => 50]
		];
		if ($isRegion) {
			$head[] = ['name' => $this->translator->trans('region.type.region')];
		}

		$table = $this->v_utils->v_tablesorter($head, $storeRows);

		return $this->v_utils->v_field($table, $title);
	}

	public function u_editPickups(array $allDates): string
	{
		$out = '<table class="timetable">
		<thead>
			<tr>
				<th class="ui-padding">' . $this->translator->trans('day') . '</th>
				<th class="ui-padding">' . $this->translator->trans('time') . '</th>
				<th class="ui-padding">' . $this->translator->trans('pickup.edit.slot_count_title') . '</th>
				<th class="ui-padding"></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4" class="ui-padding">
					<span id="nft-add">' . $this->translator->trans('pickup.edit.more') . '</span>
				</td>
			</tr>
		</tfoot>
		<tbody>';

		$dows = range(1, 6);
		$dows[] = 0;

		foreach ($allDates as $date) {
			$time = explode(':', $date['time']);

			$out .= '
			<tr class="odd">
				<td class="ui-padding">
					<select class="nft-dow" name="newfetchtime[]" id="nft-dow">
						' . $this->prepareOptionRange($dows, $date['dow'], true) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="nfttime-hour" name="nfttime[hour][]">
						' . $this->prepareOptionRange(range(0, 23), $time[0]) . '
					</select>
					<select class="nfttime-min" name="nfttime[min][]">
						' . $this->prepareOptionRange(range(0, 55, 5), $time[1]) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="fetchercount" name="nft-count[]">
						' . $this->prepareOptionRange(range(0, 20), $date['fetcher']) . '
					</select>
				</td>
				<td class="ui-pading">
					<button class="nft-remove"></button>
				</td>
			</tr>';
		}
		$out .= '</tbody></table>';

		$out .= '<table id="nft-hidden-row" style="display: none;">
		<tbody>
			<tr class="odd">
				<td class="ui-padding">
					<select class="nft-dow" name="newfetchtime[]" id="nft-dow">
						' . $this->prepareOptionRange($dows, null, true) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="nfttime-hour" name="nfttime[hour][]">
						' . $this->prepareOptionRange(range(0, 23)) . '
					</select>
					<select class="nfttime-min" name="nfttime[min][]">
						' . $this->prepareOptionRange(range(0, 55, 5)) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="fetchercount" name="nft-count[]">
						' . $this->prepareOptionRange(range(0, 10), '2') . '
					</select>
				</td>
				<td class="ui-pading">
					<button class="nft-remove"></button>
				</td>
			</tr>
		</tbody>
		</table>';

		return $out;
	}

	private function prepareOptionRange(array $range, ?string $selectedValue = null, bool $dayOfWeek = false): string
	{
		$out = '';
		foreach ($range as $item) {
			$selected = ($item == $selectedValue) ? ' selected="selected"' : '';
			$label = $dayOfWeek ? $this->timeHelper->getDow($item) : str_pad($item, 2, '0', STR_PAD_LEFT);
			$out .= '<option' . $selected . ' value="' . $item . '">' . $label . '</option>';
		}

		return $out;
	}
}
