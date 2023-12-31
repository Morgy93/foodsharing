<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
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

class FoodsaverView extends View
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
        Sanitizer $sanitizer,
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
            $sanitizer,
            $timeHelper,
            $translationHelper,
            $translator
        );
    }

    public function foodsaverForm($foodsaver = false): string
    {
        if ($foodsaver === false) {
            return '<div id="fsform"></div>';
        }

        $photo = $this->imageService->img($foodsaver['photo'], 'med');
        $cnt = $this->v_utils->v_input_wrapper($this->translator->trans('foodsaver.manage.photo'),
            '<a class="avatarlink corner-all" href="/profile/' . (int)$foodsaver['id'] . '">'
            . '<img style="display: none;" class="corner-all" src="' . $photo . '" />'
            . '</a>');

        $cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('foodsaver.manage.name'),
            $foodsaver['name'] . ' ' . $foodsaver['nachname']
        );

        $cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('foodsaver.manage.role'),
            $this->translator->trans(
                $this->translationHelper->getRoleName($foodsaver['rolle'], $foodsaver['geschlecht'])
            )
        );

        $cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('foodsaver.manage.last-activity'),
            $foodsaver['last_activity']
        );

        $cnt .= $this->v_utils->v_input_wrapper($this->translator->trans('foodsaver.manage.actions'),
            '<span class="button" onclick="fsapp.deleteFromRegion(' . $foodsaver['id'] . ');">'
            . $this->translator->trans('foodsaver.manage.remove') .
            '</span>
		');

        return $this->v_utils->v_field($cnt, $foodsaver['name'], ['class' => 'ui-padding']);
    }

    /**
     * @param Profile[] $foodsaver
     */
    public function foodsaverList(array $foodsaver, $bezirk, $inactive = false): string
    {
        $avatars = $this->vueComponent('fslist', 'AvatarList', [
            'profiles' => $foodsaver,
            'maxVisibleAvatars' => 8,
        ]);
        $name = $inactive ? 'inactive' : '';
        $label = $this->translator->trans('foodsaver.list.summary', [
            '{count}' => count($foodsaver),
            '{region}' => $bezirk['name'],
        ]) . ($inactive ? $this->translator->trans('foodsaver.list.inactive') : '');

        return '<div id="' . $name . 'foodsaverlist">' .
            $this->v_utils->v_field($avatars, $label) . '</div>';
    }

    public function foodsaver_form($title, $regionDetails): string
    {
        global $g_data;

        $orga = '';

        $position = '';

        if ($this->session->mayRole(Role::ORGA)) {
            $position = $this->v_utils->v_form_text('position');
            $options = [
                'values' => [
                    ['id' => 1, 'name' => $this->translator->trans('foodsaver.manage.orga')],
                ]
            ];

            if ($g_data['orgateam'] == 1) {
                $options['checkall'] = true;
            }

            $orga = $this->v_utils->v_form_checkbox('orgateam', $options);
            $orga .= $this->v_utils->v_form_select('rolle', [
                'values' => [
                    ['id' => 0, 'name' => $this->translator->trans('terminology.foodsharer.d')],
                    ['id' => 1, 'name' => $this->translator->trans('terminology.foodsaver.d')],
                    ['id' => 2, 'name' => $this->translator->trans('terminology.storemanager.d')],
                    ['id' => 3, 'name' => $this->translator->trans('terminology.ambassador.d')],
                    ['id' => 4, 'name' => $this->translator->trans('terminology.orga.d')],
                ]
            ]);
        }

        $this->pageHelper->addJs('
			$("#rolle").on("change", function () {
				if (this.value == 4) {
					$("#orgateam-wrapper input")[0].checked = true;
				} else {
					$("#orgateam-wrapper input")[0].checked = false;
				}
			});

			$("#plz, #stadt, #anschrift").on("blur", function () {
				if ($("#plz").val() != "" && $("#stadt").val() != "" && $("#anschrift").val() != "") {
					u_loadCoords({
						plz: $("#plz").val(),
						stadt: $("#stadt").val(),
						anschrift: $("#anschrift").val(),
					},
					function (lat, lon) {
						$("#lat").val(lat);
						$("#lon").val(lon);
					});
				}
			});

			$("#lat-wrapper").hide();
			$("#lon-wrapper").hide();
		');

        $regionPicker = $this->vueComponent('region-tree-vform', 'RegionTreeVForm', [
            'title' => $this->translator->trans('terminology.homeRegion'),
            'inputName' => 'bezirk_id',
            'value' => $regionDetails,
            'selectableRegionTypes' => [UnitType::CITY, UnitType::DISTRICT, UnitType::REGION, UnitType::WORKING_GROUP, UnitType::PART_OF_TOWN],
        ]);

        $g_data['ort'] = $g_data['stadt'];
        foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
            $latLonOptions[$i] = $g_data[$i];
        }
        $latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];

        return $this->v_utils->v_quickform($title, [
            $this->v_utils->v_form_text('name', ['required' => true]),
            $this->v_utils->v_form_text('nachname', ['required' => true]),
            $this->v_utils->v_form_date('geb_datum', [
                'required' => true,
                'yearRangeFrom' => ((int)date('Y') - 111),
                'yearRangeTo' => date('Y'),
            ]),
            $this->v_utils->v_form_select('geschlecht', ['values' => [
                ['id' => 2, 'name' => $this->translator->trans('gender.f')],
                ['id' => 1, 'name' => $this->translator->trans('gender.m')],
                ['id' => 3, 'name' => $this->translator->trans('gender.d')],
            ],
                ['required' => true]
            ]),
            $this->v_utils->v_form_text('handy', ['placeholder' => $this->translator->trans('register.phone_example')]),
            $this->v_utils->v_form_text('telefon', ['placeholder' => $this->translator->trans('register.landline_example')]),
            $regionPicker,
            $orga,
            $this->latLonPicker('LatLng', $latLonOptions, '_profile'),
            $position,
            $this->v_utils->v_form_text('email', ['required' => true, 'disabled' => true]),
        ], ['submit' => $this->translator->trans('button.save')]);
    }

    public function u_delete_account(): string
    {
        $content = '
	<div style="text-align: center; margin-bottom: 10px;">
		<span id="delete-account">' . $this->translator->trans('foodsaver.delete_account_now') . '</span>
	</div>
	';

        return $this->v_utils->v_field($content, '⚠️ ' . $this->translator->trans('foodsaver.delete_account'), ['class' => 'ui-padding']);
    }
}
