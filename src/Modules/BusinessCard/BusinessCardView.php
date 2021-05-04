<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\View;

class BusinessCardView extends View
{
	public function top()
	{
		return $this->topbar($this->translator->trans('bcard.card'),
			$this->translator->trans('bcard.claim'),
			'<img src="/img/bcard.png" />'
		);
	}

	public function optionForm($selectedData)
	{
		return $this->v_utils->v_field(
			$this->vueComponent('business-card-form', 'BusinessCardForm', [
				'roles' => $selectedData
			]),
			$this->translator->trans('bcard.actions')
		);
	}
}
