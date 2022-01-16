<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\View;

class MapView extends View
{
	public function lMap()
	{
		$this->pageHelper->addHidden('
			<div id="b_content" class="loading">
				<div class="inner">
					' . $this->v_utils->v_input_wrapper($this->translator->trans('status'), $this->translator->trans('map.donates'), 'bcntstatus') . '
					' . $this->v_utils->v_input_wrapper($this->translator->trans('storeview.managers'), '...', 'bcntverantwortlich') . '
					' . $this->v_utils->v_input_wrapper($this->translator->trans('specials'), '...', 'bcntspecial') . '
				</div>
				<input type="hidden" class="fetchbtn" name="fetchbtn" value="' . $this->translator->trans('storeview.want_to_fetch') . '" />
			</div>
		');

		return '<div id="map"></div>';
	}

	public function mapControl()
	{
		$betriebe = '';

		if ($this->session->may('fs')) {
			$betriebe = '<li><a name="betriebe" class="ui-corner-all betriebe"><span class="fa-stack fa-lg" style="color: #9E3235"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-shopping-cart fa-stack-1x fa-inverse"></i></span><span>' . $this->translator->trans('menu.entry.stores') . '</span></a>
				<div id="map-options">
					<label><input type="checkbox" name="viewopt[]" value="STANDARD" /> ' . 'Standard' . '</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="NEED_HELP" /> ' . $this->translator->trans('menu.entry.helpwanted') . '</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="NEED_HELP_INSTANT" /> ' . $this->translator->trans('menu.entry.helpneeded') . '</label>
					<label><input type="checkbox" name="viewopt" value="IN_NEGOTIATION" /> ' . $this->translator->trans('menu.entry.in_negotiation') . '</label>
					<label><input type="checkbox" name="viewopt" value="ALL_STORES" /> ' . $this->translator->trans('store.bread') . '</label>

				</div>
			</li>';
		}

		return '
			<div id="map-control-wrapper">
				<div id="map-control-colapse" class="ui-dialog ui-widget ui-widget-content" tabindex="-1">
					<i class="fas fa-layer-group"></i>
				</div>
				<div id="map-legend" class="ui-dialog ui-widget ui-widget-content ui-corner-all" tabindex="-1">
					<div class="ui-dialog-content ui-widget-content">
						<div id="map-control">
							<ul class="linklist">
								<li>
									<a name="baskets" class="map-legend-entry baskets">
										<i class="fas fa-shopping-basket"></i>'
										. $this->translator->trans('terminology.baskets') .
									'</a>
								</li>
								' . $betriebe . '
								<li>
									<a name="fairteiler" class="map-legend-entry foodshare-points">
										<i class="fas fa-recycle"></i>'
										. $this->translator->trans('terminology.fsp') .
									'</a>
								</li>
								<li>
									<a name="communities" class="map-legend-entry communities">
										<i class="fas fa-users"></i>'
										. $this->translator->trans('menu.entry.regionalgroups') .
									'</a>
								</li>
							</ul>
						</div>
					</div>
				</div>

			</div>';
	}
}
