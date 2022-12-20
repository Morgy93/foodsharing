<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Lib\View\vMap;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Foodsaver\Profile;
use Foodsharing\Permissions\FoodSharePointPermissions;
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

class FoodSharePointView extends View
{
	private ?array $region;
	private array $regions;

	private array $foodSharePoint;
	/**
	 * @var Profile[]
	 */
	private array $managers;
	/**
	 * @var Profile[]
	 */
	private array $followers;

	private FoodSharePointPermissions $fspPermissions;

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
		TranslatorInterface $translator,
		FoodSharePointPermissions $fspPermissions
	) {
		$this->fspPermissions = $fspPermissions;
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

	public function setRegions(array $regions): void
	{
		$this->regions = $regions;
	}

	public function setRegion(?array $region): void
	{
		$this->region = $region;
	}

	/**
	 * @param Profile[] $managers
	 * @param Profile[] $followers
	 */
	public function setFoodSharePoint(array $foodSharePoint, array $managers, array $followers): void
	{
		$this->foodSharePoint = $foodSharePoint;
		$this->managers = $managers;
		$this->followers = $followers;
	}

	public function foodSharePointHead(): string
	{
		return $this->twig->render('pages/FoodSharePoint/foodSharePointTop.html.twig', [
			'food_share_point' => $this->foodSharePoint,
		]);
	}

	public function checkFoodSharePoint(array $foodSharePoint): string
	{
		$htmlEscapedName = htmlspecialchars($foodSharePoint['name']);
		$content = '';
		if ($foodSharePoint['pic']) {
			$content .= $this->v_utils->v_input_wrapper($this->translator->trans('fsp.pic'),
				'<img src="' . $foodSharePoint['pic']['head'] . '" alt="' . $htmlEscapedName . '" />'
			);
		}

		$content .= $this->v_utils->v_input_wrapper($this->translator->trans('fsp.address'),
			$foodSharePoint['anschrift']
			. '<br />'
			. $foodSharePoint['plz'] . ' ' . $foodSharePoint['ort']
		);

		$content .= $this->v_utils->v_input_wrapper($this->translator->trans('fsp.description'),
			$this->sanitizerService->markdownToHtml($foodSharePoint['desc'])
		);

		$content .= $this->v_utils->v_input_wrapper($this->translator->trans('fsp.addedOn'),
			date('d.m.Y', $foodSharePoint['time_ts'])
		);

		$fsName = $foodSharePoint['fs_name'] . ' ' . $foodSharePoint['fs_nachname'];
		$content .= $this->v_utils->v_input_wrapper($this->translator->trans('fsp.addedBy'),
			'<a href="/profile/' . (int)$foodSharePoint['fs_id'] . '">' . $fsName . '</a>'
		);

		return $this->v_utils->v_field(
			$content,
			$this->translator->trans('fsp.acceptName', ['{name}' => $foodSharePoint['name']]),
			['class' => 'ui-padding']
		);
	}

	public function address(): string
	{
		$address = $this->v_utils->v_input_wrapper($this->translator->trans('fsp.street'),
		   $this->foodSharePoint['anschrift']
		   . '<br />'
		   . $this->foodSharePoint['plz']
		   . ' '
		   . $this->foodSharePoint['ort']
	    );

		return $this->v_utils->v_field(
			$address,
			$this->translator->trans('fsp.address'),
			['class' => 'ui-padding']
		);
	}

	public function map(): string
	{
		$mapHtml = $this->v_utils->v_input_wrapper($this->translator->trans('smallMap.title'),
			'<i>'
			. $this->translator->trans('smallMap.noCoordinatesAvailable')
			. '</i>'
		);

		if ($this->foodSharePoint['lat'] != 0 || $this->foodSharePoint['lon'] != 0) {
			$map = new vMap([$this->foodSharePoint['lat'], $this->foodSharePoint['lon']]);
			$map->addMarker($this->foodSharePoint['lat'], $this->foodSharePoint['lon']);
			$map->setDefaultMarkerOptions('recycle', '#FFCA92');
			$map->setCenter($this->foodSharePoint['lat'], $this->foodSharePoint['lon']);

			$mapHtml = $map->render();
			$this->pageHelper->addJsFunc("
				function openPostPage(url, data) {
					var form = document.createElement('form');
					document.body.appendChild(form);
					form.target = '_blank';
					form.method = 'post';
					form.action = url;
					for (var name in data) {
						var input = document.createElement('input');
						input.type = 'hidden';
						input.name = name;
						input.value = data[name];
						form.appendChild(input);
					}
					form.submit();
					document.body.removeChild(form);
				}
				");
			$this->pageHelper->addJsFunc('
				function enlargeMap() {
					openPostPage(\'/karte\', { fairteiler: [ '
				. json_encode($this->foodSharePoint)
				. ' ] } );'
				. ' } ');
			$mapHtml .= $this->v_utils->v_input_wrapper('', '<a id="enlargeMap" title="'
				. $this->translator->trans('smallMap.enlarge')
				. '" href="#" onclick="'
				. 'enlargeMap();return false;'
				. '">'

//				'<a href="/?page=map&fspid='
//				. $this->foodSharePoint['id']
//				. '&lat='
//				. $this->foodSharePoint['lat']
//				. '&lon='
//				. $this->foodSharePoint['lon']
//				. '" target="_blank">'
				. $this->translator->trans('smallMap.enlarge')
				. '</a><br />'
				. '<b>'
				. $this->translator->trans('smallMap.coordinates')
				. '</b><br />'
				. $this->foodSharePoint['lat']
				. ', '
				. $this->foodSharePoint['lon']
				. '<br /><br /><a href="https://www.google.com/maps/search/?api=1&query='
				. $this->foodSharePoint['lat']
				. ','
				. $this->foodSharePoint['lon']
				. '" target="_blank" rel="noreferrer">'
				. $this->translator->trans('smallMap.showOnGoogleMaps')
				. '</a>'
			);

		}

		return $this->v_utils->v_field(
			$mapHtml,
			$this->translator->trans('smallMap.title'),
			['class' => 'ui-padding']
		);
	}

	public function bubbleNoUser(array $fairteiler): string
	{
		$img = '';
		if (!empty($fairteiler['picture'])) {
			$img = '<div style="width: 100%; overflow: hidden;">
				<img src="/images/basket/medium-' . $fairteiler['picture'] . '" width="100%" />
			</div>';
		}

		return $img . $this->v_utils->v_input_wrapper(
				$this->translator->trans('fsp.description'),
				nl2br($this->routeHelper->autolink($fairteiler['description']))
			);
	}

	public function bubble(array $fairteiler): string
	{
		$img = '';
		if (!empty($fairteiler['picture'])) {
			$img = '<div style="width: 100%; overflow: hidden;">
				<img src="/images/basket/medium-' . $fairteiler['picture'] . '" width="100%" />
			</div>';
		}

		return $img . $this->v_utils->v_input_wrapper(
				$this->translator->trans('fsp.street'),
				nl2br($this->routeHelper->autolink($fairteiler['description']))
			);
	}

	public function foodSharePointForm(array $data = []): string
	{
		$title = $this->translator->trans('fsp.new');

		$tagselect = '';
		if ($data) {
			$fspName = $this->foodSharePoint['name'];
			$title = $this->translator->trans('fsp.editName', ['{name}' => $fspName]);

			$tagselect = $this->v_utils->v_form_tagselect('fspmanagers', null,
				$data['bfoodsaver_values'], $data['bfoodsaver']
			);
			$this->pageHelper->addJs('
			$("#fairteiler-form").on("submit", function (ev) {
				if ($("#fspmanagers input[type=\'hidden\']").length == 0) {
					ev.preventDefault();
					pulseError("' . $this->translator->trans('fsp.noCoordinator') . '");
				}
			});');

			foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
				$latLonOptions[$i] = $data[$i];
			}
			$latLonOptions['location'] = ['lat' => $data['lat'], 'lon' => $data['lon']];
		} else {
			$latLonOptions = [];
			$data = [
				'bezirk_id' => null,
				'name' => '',
				'desc' => '',
				'picture' => '',
			];
		}

		// initial value for the image chooser can be empty (no image yet) or an old or new file path
		$initialValue = '';
		if (!empty($data['picture'])) {
			$initialValue = (strpos($data['picture'], '/api/uploads/') !== 0 ? '/images/' : '') . $data['picture'];
		}

		return $this->v_utils->v_field($this->v_utils->v_form('fairteiler', [
			$this->v_utils->v_form_select('fsp_bezirk_id', ['values' => $this->regions, 'selected' => $data['bezirk_id'], 'required' => true]),
			$this->v_utils->v_form_text('name', ['value' => $data['name'], 'required' => true]),
			$this->v_utils->v_form_textarea('desc', [
				'value' => $data['desc'],
				'desc' => $this->translator->trans('fsp.descLabel') . '<br>' . $this->translator->trans('formatting.md'),
				'required' => true,
			]),
			$this->vueComponent('image-upload', 'file-upload-v-form', [
				'inputName' => 'picture',
				'isImage' => true,
				'initialValue' => $initialValue,
				'imgHeight' => 525,
				'imgWidth' => 169
			]),
			$this->latLonPicker('latLng', $latLonOptions),
			$tagselect,
		], ['submit' => $this->translator->trans('button.save')]
		), $title, ['class' => 'ui-padding']);
	}

	public function options(array $items): string
	{
		return $this->v_utils->v_menu($items, $this->translator->trans('options'));
	}

	public function followHidden(): string
	{
		$this->pageHelper->addJsFunc('
			function u_follow () {
				$("#follow-hidden").dialog("open");
			}
		');
		$this->pageHelper->addJs('
			$("#follow-hidden").dialog({
				modal: true,
				title: "' . $this->translator->trans('fsp.followName', [
					'{name}' => $this->sanitizerService->jsSafe($this->foodSharePoint['name'], '"')
				]) . '",
				autoOpen: false,
				width: 500,
				resizable: false,
				buttons: {
					"' . $this->translator->trans('button.save') . '": function () {
						goTo("' . $this->routeHelper->getSelf() . '&follow=1&infotype=" + $("input[name=\'infotype\']:checked").val());
					}
				}
			});
		');

		global $g_data;
		$g_data['infotype'] = 1;

		return '<div id="follow-hidden">' . $this->v_utils->v_form_radio(
			'infotype',
			[
				'desc' => $this->translator->trans('fsp.info.descModal'),
				'values' => [
					['id' => InfoType::BELL, 'name' => $this->translator->trans('fsp.info.bell')],
					['id' => InfoType::EMAIL, 'name' => $this->translator->trans('fsp.info.mail')],
				]
			]
		) . '</div>';
	}

	public function follower(): string
	{
		$out = '';

		if (!empty($this->managers)) {
			shuffle($this->managers);
			$out .= $this->v_utils->v_field(
				$this->vueComponent('fsp-managers', 'AvatarList', [
					'profiles' => $this->managers,
					'maxVisibleAvatars' => 5,
				]),
				$this->translator->trans('fsp.managers')
			);
		}
		if (!empty($this->followers)) {
			shuffle($this->followers);
			$out .= $this->v_utils->v_field(
				$this->vueComponent('fsp-followers', 'AvatarList', [
					'profiles' => $this->followers,
					'maxVisibleAvatars' => 8,
				]),
				$this->translator->trans('fsp.followers')
			);
		}

		return $out;
	}

	public function desc(): string
	{
		return $this->v_utils->v_field(
			'<p>' . $this->sanitizerService->markdownToHtml($this->foodSharePoint['desc']) . '</p>',
			$this->translator->trans('fsp.description'),
			['class' => 'ui-padding fsp-desc']
		);
	}

	public function listFoodSharePoints(array $regions): string
	{
		$content = '';
		$count = 0;
		foreach ($regions as $region) {
			$count += count($region['fairteiler']);
			$content .= $this->twig->render('partials/listFoodSharePointsForRegion.html.twig', [
				'region' => $region,
				'food_share_points' => $region['fairteiler'],
			]);
		}

		$topbarHeader = $this->translator->trans('fsp.yours');
		$topbarText = $this->translator->trans('fsp.summary', ['{count}' => $count]);
		if ($this->region) {
			$regionName = $this->region['name'];
			$topbarHeader = $this->translator->trans('fsp.inRegion', ['{region}' => $regionName]);
			$topbarText = $this->translator->trans('fsp.summaryRegion', [
				'{count}' => $count,
				'{region}' => $regionName,
			]);
		}

		$this->pageHelper->addContent($this->topbar(
			$topbarHeader,
			$topbarText,
			'<img src="/img/foodSharePointThumb.png" />'
		), CNT_TOP);

		return $content;
	}

	public function foodSharePointOptions(int $regionId): string
	{
		$mayCreateFSP = $this->fspPermissions->mayAdd($regionId);

		$item = [
			'name' => $this->translator->trans($mayCreateFSP ? 'fsp.add' : 'fsp.suggest'),
			'href' => '/?page=fairteiler&bid=' . $regionId . '&sub=add',
		];

		return $this->v_utils->v_menu([$item], $this->translator->trans('options'));
	}
}
