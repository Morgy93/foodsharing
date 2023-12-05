<?php

class SettingsCept
{
    protected array $regions;
    protected array $users;
    protected array $groups;
    protected array $stores;

    final public function _before()
    {
        $this->gateway = $this->tester->get(SearchGateway::class);

        $regionEurope = $this->tester->createRegion('Europa', ['parent_id' => RegionIDs::ROOT, 'type' => UnitType::COUNTRY, 'has_children' => 1]);
        $regionCountry = $this->tester->createRegion('Deutschland', ['parent_id' => $regionEurope['id'], 'type' => UnitType::COUNTRY, 'has_children' => 1]);
        $regionState1 = $this->tester->createRegion('Sachsen', ['parent_id' => $regionCountry['id'], 'type' => UnitType::FEDERAL_STATE, 'has_children' => 1]);
        $regionState2 = $this->tester->createRegion('Sachsen-Anhalt', ['parent_id' => $regionCountry['id'], 'type' => UnitType::FEDERAL_STATE, 'has_children' => 1]);
        $regionCity1 = $this->tester->createRegion('Dresden', ['parent_id' => $regionState1['id'], 'type' => UnitType::CITY, 'has_children' => 1, 'email' => 'dreeesden']);
        $regionCity2 = $this->tester->createRegion('Freiberg', ['parent_id' => $regionState1['id'], 'type' => UnitType::CITY, 'has_children' => 1]);
        $regionCity3 = $this->tester->createRegion('Magdeburg', ['parent_id' => $regionState2['id'], 'type' => UnitType::CITY, 'has_children' => 1]);
        $regionCity4 = $this->tester->createRegion('Bad Dürrenberg', ['parent_id' => $regionState2['id'], 'type' => UnitType::CITY, 'has_children' => 1]);

        $this->regions = [
            'europe' => $regionEurope,
            'country' => $regionCountry,
            'state1' => $regionState1,
            'state2' => $regionState2,
            'city1' => $regionCity1,
            'city2' => $regionCity2,
            'city3' => $regionCity3,
            'city4' => $regionCity4,
        ];

        $this->groups = [
            'wg-city1' => $this->tester->createRegion('AG Dresden', ['parent_id' => $regionCity1['id'], 'type' => UnitType::WORKING_GROUP, 'email' => 'ag-mail-dresden']),
            'wg-city2' => $this->tester->createRegion('AG Freiberg', ['parent_id' => $regionCity2['id'], 'type' => UnitType::WORKING_GROUP, 'email' => 'ag-mail-freiberg']),
        ];

        $this->users = array_combine(
            array_map(fn ($key) => 'user-' . $key, array_keys($this->regions)),
            array_map(fn ($region) => $this->tester->createFoodsaver(null, [
                    'name' => 'Nutzer',
                    'nachname' => 'Nachname',
                    'bezirk_id' => $region['id'],
                ]), $this->regions)
        );
        $this->users['bot-city1'] = $this->tester->createAmbassador(null, [
            'name' => 'Nutzer Bot',
            'nachname' => 'Nachname',
            'bezirk_id' => $regionCity1['id'],
        ]);
        $this->tester->addRegionAdmin($regionCity1['id'], $this->users['bot-city1']['id']);

        $this->stores = [
            'store-city1' => $this->tester->createStore($regionCity1['id'], null, null, ['name' => 'BetriebA', 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]),
            'store-city1-closed' => $this->tester->createStore($regionCity1['id'], null, null, ['name' => 'Betrieb geschlossen', 'betrieb_status_id' => CooperationStatus::PERMANENTLY_CLOSED->value]),
            'store-city2' => $this->tester->createStore($regionCity2['id'], null, null, ['name' => 'BetriebB', 'betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED->value]),
        ];

        $this->sharePoints = [
            'fsp-city1' => $this->tester->createFoodSharePoint($this->users['user-city3']['id'], $regionCity1['id'], ['name' => 'FairteilerA']),
            'fsp-city2' => $this->tester->createFoodSharePoint($this->users['user-city3']['id'], $regionCity2['id'], ['name' => 'FairteilerB']),
        ];

        $this->chats = [
            'chat1' => $this->tester->createConversation([$this->users['user-city1']['id'], $this->users['user-city2']['id']], ['last_message_id' => 1, 'last_message' => 'msg']),
            'chat2' => $this->tester->createConversation([$this->users['user-city2']['id'], $this->users['user-city3']['id']], ['last_message_id' => 1, 'last_message' => 'msg']),
        ];
    }

    final public function canSearch(AcceptanceTester $I): void
    {
        $I->login($this->users['user-city1']['email']);
        $I->amOnPage('/');
        $I->waitForPageBody();

        $I->click('a.nav-link .fa-search');
        $I->waitForElementVisible('#searchBarModal .modal-dialog');
        $I->fillField('#searchField', 'Nu');
        $I->waitForActiveAPICalls();
        $I->see('Du kannst nach Personen, Gruppen, Betrieben, Bezirken, Chats, Forenbeiträgen und Fairteilern suchen.');
        $I->fillField('#searchField', 'Nutzer Bot');
        $I->waitForActiveAPICalls();

        $I->see('Personen');
        $I->see('Nutzer Bot');

        //Dont show empty categories
        $I->dontSee('Betriebe');
    }
}
