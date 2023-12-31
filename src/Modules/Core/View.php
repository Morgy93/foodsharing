<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
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

class View
{
    protected Session $session;
    protected Utils $v_utils;

    public \Twig\Environment $twig;

    protected DataHelper $dataHelper;
    protected IdentificationHelper $identificationHelper;
    protected ImageHelper $imageService;
    protected NumberHelper $numberHelper;
    protected PageHelper $pageHelper;
    protected RouteHelper $routeHelper;
    protected Sanitizer $sanitizerService;
    protected TimeHelper $timeHelper;
    protected TranslationHelper $translationHelper;
    protected TranslatorInterface $translator;

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
        $this->twig = $twig;
        $this->session = $session;
        $this->v_utils = $viewUtils;
        $this->dataHelper = $dataHelper;
        $this->identificationHelper = $identificationHelper;
        $this->imageService = $imageService;
        $this->numberHelper = $numberHelper;
        $this->pageHelper = $pageHelper;
        $this->routeHelper = $routeHelper;
        $this->sanitizerService = $sanitizerService;
        $this->timeHelper = $timeHelper;
        $this->translationHelper = $translationHelper;
        $this->translator = $translator;
    }

    public function topbar(string $title, string $subtitle = '', string $icon = ''): string
    {
        if ($icon != '') {
            $icon = '<div class="img">' . $icon . '</div>';
        }

        if ($subtitle != '') {
            $subtitle = '<p>' . $subtitle . '</p>';
        }

        return '
		<div class="content-top corner-all">
			' . $icon . '
			<h3>' . $title . '</h3>
			' . $subtitle . '
			<div class="clear"></div>
		</div>';
    }

    public function location($location)
    {
        $out = $this->v_utils->v_field('
		<p>' . $location['name'] . '</p>
		<p>
			' . $location['street'] . '<br />
			' . $location['zip'] . ' ' . $location['city'] . '
		</p>

		', 'Ort', ['class' => 'ui-padding event-location']);

        return $out;
    }

    public function menu(array $items, array $option = []): string
    {
        $title = false;
        if (isset($option['title'])) {
            $title = $option['title'];
        }

        $active = false;
        if (isset($option['active'])) {
            $active = $option['active'];
        }

        $id = $this->identificationHelper->id('vmenu');

        $out = '';

        foreach ($items as $item) {
            if (!isset($item['href'])) {
                $item['href'] = '#';
            }

            $click = '';
            if (isset($item['click'])) {
                $click = ' onclick="' . $item['click'] . '"';
            }
            $class = '';
            if ($active !== false && (strpos($item['href'], '=' . $active) !== false)) {
                $class = 'active ';
            }

            $out .= '<li>
					<a class="' . $class . 'ui-corner-all" href="' . $item['href'] . '"' . $click . '>
						<span>' . $item['name'] . '</span>
					</a>
				</li>';
        }

        if (!$title) {
            return '
		<div class="ui-widget ui-widget-content ui-corner-all ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
        }

        return '
		<h3 class="head ui-widget-header ui-corner-top">' . $title . '</h3>
		<div class="ui-widget ui-widget-content ui-corner-bottom ui-padding margin-bottom">
			<ul class="linklist">
				' . $out . '
			</ul>
		</div>';
    }

    /**
     * @deprecated use the Vue components LeafletLocationPicker and LeafletLocationSearch instead
     */
    public function latLonPicker(string $id, array $options = [], string $context = ''): string
    {
        if (!isset($options['location'])) {
            $data = $this->session->getLocation() ?? ['lat' => 0, 'lon' => 0];
        } else {
            $data['lat'] = $options['location']['lat'];
            $data['lon'] = $options['location']['lon'];
        }

        if (empty($data['lat']) || empty($data['lon'])) {
            /* set empty coordinates, javascript will take over default location */
            $data['lat'] = 0;
            $data['lon'] = 0;
        }

        // Default to blank values for these keys
        foreach (['anschrift', 'plz', 'ort'] as $key) {
            if (!isset($options[$key])) {
                $options[$key] = '';
            }
        }

        $out = $this->v_utils->v_input_wrapper(
            $this->translator->trans('addresspicker.label'),
            '<div class="lat-lon-picker">' .
                    $this->v_utils->v_info(
                        $this->translator->trans('addresspicker.infobox')
                        . ($context ? '<hr>' . $this->translator->trans('addresspicker.infobox' . $context) : '')
                    ) .
                '<input placeholder="' . $this->translator->trans('addresspicker.placeholder') . '" '
                    . 'type="text" value="" id="addresspicker" type="text" class="input text value ui-corner-top" />
		<div id="map" class="pickermap"></div>
	</div>');
        $out .=
            $this->v_utils->v_form_text('anschrift', ['value' => $options['anschrift'], 'required' => '1']) .
            $this->v_utils->v_form_text('plz', ['value' => $options['plz'], 'disabled' => '1', 'required' => '1']) .
            $this->v_utils->v_form_text('ort', ['value' => $options['ort'], 'disabled' => '1', 'required' => '1']) .
            $this->v_utils->v_form_text('lat', ['value' => $data['lat']]) .
            $this->v_utils->v_form_text('lon', ['value' => $data['lon']]) .
            '';

        return $out;
    }

    public function vueComponent(string $id, string $component, array $props = [], array $data = []): string
    {
        return $this->twig->render('partials/vue-wrapper.twig', [
            'id' => $id,
            'component' => $component,
            'props' => $props,
            'initialData' => $data,
        ]);
    }
}
