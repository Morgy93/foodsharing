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
					' . $this->v_utils->v_input_wrapper($this->translationHelper->s('status'), 'Betrieb spendet', 'bcntstatus') . '
					' . $this->v_utils->v_input_wrapper('Verantwortliche Foodsaver', '...', 'bcntverantwortlich') . '
					' . $this->v_utils->v_input_wrapper($this->translationHelper->s('specials'), '...', 'bcntspecial') . '
				</div>
				<input type="hidden" class="fetchbtn" name="fetchbtn" value="' . $this->translationHelper->s('want_to_fetch') . '" />
			</div>
		');

		return '<div id="map"></div>';
	}

	public function mapControl()
	{
		$betriebe = '';

		if ($this->session->may('fs')) {
			$betriebe = '<li><a name="betriebe" class="ui-corner-all betriebe"><span class="fa-stack fa-lg" style="color: #9E3235"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-shopping-cart fa-stack-1x fa-inverse"></i></span><span>Betriebe</span></a>
				<div id="map-options">
					<label><input type="checkbox" name="viewopt[]" value="allebetriebe" /> Alle Betriebe</label>
					<label><input type="checkbox" name="viewopt[]" value="allow_tutoring" /> Einführungsabholungsbetriebe</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelp" /> HelferInnen gesucht</label>
					<label><input checked="checked" type="checkbox" name="viewopt[]" value="needhelpinstant" /> HelferInnen dringend gesucht</label>
					<label><input type="checkbox" name="viewopt" value="nkoorp" /> in Verhandlung</label>
				</div>
			</li>';
		}

		return '
			<div id="map-control-wrapper">
				<div class="ui-dialog ui-widget ui-widget-content ui-corner-all" tabindex="-1">
					<div class="ui-dialog-content ui-widget-content">
						<div id="map-control">
							<ul class="linklist">
								<li><a name="baskets" class="ui-corner-all baskets"><span class="fa-stack fa-lg" style="color: #72B026"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-shopping-basket fa-stack-1x fa-inverse"></i></span><span>Essenskörbe</span></a></li>
								' . $betriebe . '
								<li><a name="fairteiler" class="ui-corner-all fairteiler"><span class="fa-stack fa-lg" style="color: #FFCA92"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-recycle fa-stack-1x fa-inverse"></i></span><span>Fair-Teiler</span></a></li>
							</ul>
						</div>
					</div>
				</div>

			</div>';
	}
}
