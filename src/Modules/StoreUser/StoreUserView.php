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

    public function u_editPickups(array $allDates): string
    {
        $out = '<table class="timetable">
		<thead>
			<tr>
				<th class="ui-padding">' . $this->translator->trans('day') . '</th>
				<th class="ui-padding">' . $this->translator->trans('time') . '</th>
				<th class="ui-padding">' . $this->translator->trans('pickup.edit.slotcount') . '</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="3" class="ui-padding">
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
					<input class="fetchercount" type="text" name="nft-count[]" value="' . $date['fetcher'] . '"/>
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
					</select></td>
				<td class="ui-padding">
					<input class="fetchercount" type="text" name="nft-count[]" value="2" />
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
