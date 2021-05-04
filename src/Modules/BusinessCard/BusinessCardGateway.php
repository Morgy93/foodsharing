<?php

namespace Foodsharing\Modules\BusinessCard;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;

class BusinessCardGateway extends BaseGateway
{
	public function getFoodsaverData(int $fsId): array
	{
		return $this->db->fetchByCriteria('fs_foodsaver', [
			'id', 'name', 'nachname', 'geschlecht', 'plz', 'stadt', 'anschrift', 'telefon', 'handy', 'verified', 'email'
		], ['id' => $fsId]);
	}

	/**
	 * Returns ID and name of all regions in which the user is a member.
	 */
	public function getFoodsaverRegions(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	b.name,
					b.id

			FROM 	fs_bezirk b,
					fs_foodsaver_has_bezirk fhb

			WHERE 	fhb.bezirk_id = b.id
			AND 	fhb.foodsaver_id = :foodsaver_id
			AND 	b.type != 7
			AND  b.type != 6
			AND  b.type != 5
		', [':foodsaver_id' => $fsId]);
	}

	/**
	 * Returns ID, name, and email of all regions for which the user is ambassador.
	 */
	public function getAmbassadorRegions(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	b.name,
					b.id,
					CONCAT(mb.`name`,"@","' . PLATFORM_MAILBOX_HOST . '") AS email,
					mb.name AS mailbox

			FROM 	fs_bezirk b,
					fs_mailbox mb,
					fs_botschafter bot

			WHERE 	b.mailbox_id = mb.id
			AND 	bot.bezirk_id = b.id
			AND 	bot.foodsaver_id = :foodsaver_id
			AND 	b.type != 7
		', [':foodsaver_id' => $fsId]);
	}

	/**
	 * Returns the user's foodsharing email address.
	 */
	public function getMailboxData(int $fsId): ?string
	{
		try {
			$mailbox = $this->db->fetchValue('
			SELECT mb.name
			FROM fs_mailbox mb, fs_foodsaver fs
			WHERE fs.mailbox_id = mb.id
			AND fs.id = :foodsaver_id', [
				':foodsaver_id' => $fsId
			]);

			return $mailbox . '@' . PLATFORM_MAILBOX_HOST;
		} catch (Exception $e) {
			return null;
		}
	}
}
