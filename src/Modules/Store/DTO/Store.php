<?php

namespace Foodsharing\Modules\Store\DTO;

/**
 * (TODO) Represents data of one store, with several German database column names mapped to English.
 */
class Store
{
	private const COLUMNS_IN_DATABASE = [
		'id',
		'betrieb_status_id',
		'bezirk_id',
		'added',
		'plz',
		'stadt',
		'lat',
		'lon',
		'kette_id',
		'betrieb_kategorie_id',
		'name',
		'str',
		'hsnr',
		'status_date',
		'status',
		'ansprechpartner',
		'telefon',
		'fax',
		'email',
		'begin',
		'besonderheiten',
		'public_info',
		'public_time',
		'ueberzeugungsarbeit',
		'presse',
		'sticker',
		'abholmenge',
		'team_status',
		'prefetchtime',
		'team_conversation_id',
		'springer_conversation_id',
		'deleted_at',
	];

	public static function isValidStoreField(string $field)
	{
		return in_array($field, self::COLUMNS_IN_DATABASE);
	}
}
