<?php

namespace Foodsharing\Utility;

class WeightHelper
{
	public function mapIdToKilos(int $weightId)
	{
		$weightArray = [
			1 => 2,   // '1-3 kg'
			2 => 4,   // '3-5 kg'
			3 => 7.5, // '5-10 kg'
			4 => 15,  // '10-20 kg'
			5 => 25,  // '20-30 kg'
			6 => 45,  // '40-50 kg'
			7 => 64,  // 'mehr als 50 kg'
		];

		return $weightArray[$weightId] ?? 1.5;
	}
}
