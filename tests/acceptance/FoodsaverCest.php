<?php

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Quiz\SessionStatus;

class FoodsaverCest
{
	private $region;
	private $foodsharer;
	private $orga;

	public function _before(AcceptanceTester $I)
	{
		$this->region = $I->createRegion();
		$regionId = $this->region['id'];
		$this->foodsharer = $I->createFoodsharer();
		$I->addRegionMember($regionId, $this->foodsharer['id']);
		$this->orga = $I->createOrga();
		$I->addRegionAdmin($regionId, $this->orga['id']);
	}

	public function downgradeFoodsharerPermanently(AcceptanceTester $I)
	{
		$fsId = $this->foodsharer['id'];

		$I->login($this->orga['email']);
		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->selectOption('Benutzer*innenrolle', 'Foodsaver*in');
		$I->click('Speichern');

		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->selectOption('Benutzer*innenrolle', 'Foodsharer*in');
		$I->click('Speichern');

		$I->dontSeeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_botschafter', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_betrieb_team', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_abholer', ['foodsaver_id' => $fsId]);
		$I->dontSeeInDatabase('fs_foodsaver_has_conversation', ['foodsaver_id' => $fsId]);
		$I->seeNumRecords(7, 'fs_quiz_session', ['foodsaver_id' => $fsId, 'quiz_id' => Role::FOODSAVER, 'status' => SessionStatus::FAILED]);
		$I->seeInDatabase('fs_foodsaver', ['rolle' => Role::FOODSHARER, 'quiz_rolle' => Role::FOODSHARER]);
	}

	final public function canEditLocation(AcceptanceTester $I): void
	{
		$fsId = $this->foodsharer['id'];

		$address = 'Hammer Straße 23 48153 Münster Deutschland';
		$I->login($this->orga['email']);
		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->waitForPageBody();
		$I->fillField('#addresspicker', $address);
		$I->waitForElementVisible('#addresspicker_listbox');
		$I->click("//*[@id='addresspicker_listbox']//*[contains(text(), 'Hammer Straße 23')]");
		$I->click('Speichern');
		$I->waitForPageBody();

		$I->amOnPage('/?page=foodsaver&a=edit&id=' . $fsId);
		$I->waitForPageBody();
		$I->seeInField('#anschrift', 'Hammer Straße 23');
		$I->seeInField('#plz', '48153');
		$I->seeInField('#ort', 'Münster');
		$I->assertEqualsWithDelta($I->grabValueFrom('#lat'), 51.953549550000005, 0.001);
		$I->assertEqualsWithDelta($I->grabValueFrom('#lon'), 7.6261375873508435, 0.001);
	}
}
