<?php

use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;

/**
 * Uses the search field in the store management panel to add a user to the team.
 */
function addToTeam(AcceptanceTester $I, array $user)
{
	$I->fillField('#new-foodsaver-search input', $user['name']);
	$I->waitForActiveAPICalls();
	$I->waitForElement('#new-foodsaver-search li.suggest-item');
	$I->click('#new-foodsaver-search li.suggest-item');
	$I->click('#new-foodsaver-search button[type="submit"]');
	$I->waitForActiveAPICalls();
}

$I = new AcceptanceTester($scenario);

$I->wantTo('create a store and manage it and my team');

// Setup a new region
$region = $I->createRegion();

// store informations
$storeName = 'Multistore 24';
$newStoreName = 'Ex-Ultrastore';
$storeStreet = 'Kantstraße 20';
$storePostcode = '04808';
$storeCity = 'Wurzen';

// create users
$extra_params = ['bezirk_id' => $region['id']];
$storeManager = $I->createStoreCoordinator(null, $extra_params);
$foodsaver_A = $I->createStoreCoordinator(null, $extra_params);
$foodsaver_B = $I->createFoodsaver(null, $extra_params);
$foodsaver_C = $I->createFoodsaver(null, $extra_params);
$foodsaver_D = $I->createFoodsaver(null, $extra_params);

// login as storeManager and start the test
$I->login($storeManager['email']);

// create new store
$I->amOnPage($I->storeNewUrl());
// fill the form
$I->unlockAllInputFields();
$I->fillField('#name', $storeName);
$I->fillField('#anschrift', $storeStreet);
$I->fillField('#plz', $storePostcode);
$I->fillField('#ort', $storeCity);
$I->fillField('#first_post', 'A first wallpost entry on the store');

$I->click('Senden');

// site should reloaded
$I->waitForText($storeStreet, 10, '#inputAdress');
$I->waitForText($storePostcode, 10, '#inputAdress');
$I->waitForText($storeCity, 10, '#inputAdress');

// $I->see($storeManager['handy'], '.store-team'); // not working based on different number formatting (frontend != backend)
$I->see($storeManager['name'] . ' ' . $storeManager['nachname'], '.store-team');

$storeId = $I->grabFromCurrentUrl('~&id=(\d+)~');

// Check the database if the storeManager joined the team chats
$teamConversationId = $I->grabFromDatabase('fs_betrieb', 'team_conversation_id', ['id' => $storeId]);
$jumperConversationId = $I->grabFromDatabase('fs_betrieb', 'springer_conversation_id', ['id' => $storeId]);

$I->seeInDatabase('fs_foodsaver_has_conversation', [
	'conversation_id' => $teamConversationId,
	'foodsaver_id' => $storeManager['id'],
]);
$I->seeInDatabase('fs_foodsaver_has_conversation', [
	'conversation_id' => $jumperConversationId,
	'foodsaver_id' => $storeManager['id'],
]);

// Rename the store
$I->amOnPage($I->storeEditUrl($storeId));
$I->fillField('#name', $newStoreName);
$I->click('Senden');

$I->see($newStoreName . '-Team');
// Reload to get rid of green overlay
$I->amOnPage($I->storeUrl($storeId));

// Add more Users
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->waitForElement('#new-foodsaver-search', 5);

addToTeam($I, $foodsaver_A);
addToTeam($I, $foodsaver_B);
addToTeam($I, $foodsaver_C);
addToTeam($I, $foodsaver_D);

$I->waitForActiveAPICalls();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_A['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_B['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_C['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_D['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);

$I->waitForElement('button.reload-page', 5);
$I->click('button.reload-page');

// Promote foodsaver to storemanager
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->click($foodsaver_A['name'] . ' ' . $foodsaver_A['nachname'], '.store-team');
$I->click('Verantwortlich machen', '.member-actions');
$I->waitForActiveAPICalls();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_A['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 1,
]);

// Edit the store to see that team does not change
$I->amOnPage($I->storeEditUrl($storeId));
$I->click('Senden');
$I->see('Änderungen wurden gespeichert');

// Reload to get rid of green overlay
$I->amOnPage($I->storeUrl($storeId));

// Check if the team is visible
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $storeManager['id'] . '"]', 5);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $foodsaver_A['id'] . '"]', 5);
// $I->see($storeManager['handy'], '.store-team'); // not working based on different number formatting (frontend != backend)
$I->see($foodsaver_A['name'] . ' ' . $foodsaver_A['nachname'], '.store-team');
$I->see($foodsaver_B['name'] . ' ' . $foodsaver_B['nachname'], '.store-team');
$I->see($foodsaver_C['name'] . ' ' . $foodsaver_C['nachname'], '.store-team');
$I->see($foodsaver_D['name'] . ' ' . $foodsaver_D['nachname'], '.store-team');

// Demote newly promoted storemanager to regular team member
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->click($foodsaver_A['name'] . ' ' . $foodsaver_A['nachname'], '.store-team');
$I->click('Als Betriebsverantwortliche*n entfernen', '.member-actions');
$I->seeInPopup('die Verantwortung für diesen Betrieb entziehen?');
$I->cancelPopup();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_A['id'],
	'verantwortlich' => 1,
]);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $foodsaver_A['id'] . '"]', 2);
$I->click('Als Betriebsverantwortliche*n entfernen', '.member-actions');
$I->seeInPopup('die Verantwortung für diesen Betrieb entziehen?');
$I->acceptPopup();
$I->waitForActiveAPICalls();
$I->dontSeeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_A['id'],
	'verantwortlich' => 1,
]);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $storeManager['id'] . '"]', 5);

// Check if the demoted storemanager is shown as a regular team member
$I->waitForElement('.store-team tr[data-pk="' . $foodsaver_A['id'] . '"]:not(.table-warning)', 5);
$I->see($foodsaver_B['name'] . ' ' . $foodsaver_B['nachname'], '.store-team');
$I->see($foodsaver_C['name'] . ' ' . $foodsaver_C['nachname'], '.store-team');
$I->see($foodsaver_D['name'] . ' ' . $foodsaver_D['nachname'], '.store-team');

// convert a member to jumper (standby list)
$I->click($foodsaver_B['name'] . ' ' . $foodsaver_B['nachname'], '.store-team');
$I->click('Auf die Springerliste', '.member-actions');
$I->waitForActiveAPICalls();
// check that the jumper is still displayed as team member (but with muted colors)
$I->see($foodsaver_D['name'] . ' ' . $foodsaver_D['nachname'], '.store-team');
$I->waitForElement('.store-team #member-' . $foodsaver_B['id'] . '.member-info.jumper', 5);

$jumperIds = $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::JUMPER,
]);
$jumpers = array_column([$foodsaver_B], 'id');
$I->assertEquals($jumperIds, $jumpers);

// remove a member from the team entirely
$I->click($foodsaver_D['name'] . ' ' . $foodsaver_D['nachname'], '.store-team');
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('aus diesem Betriebs-Team entfernen?');
$I->cancelPopup();

// confirm alert this time
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('aus diesem Betriebs-Team entfernen?');
$I->acceptPopup();
$I->waitForActiveAPICalls();
$I->dontSee($foodsaver_D['name'] . ' ' . $foodsaver_D['nachname'], '.store-team');
$I->dontSeeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaver_D['id'],
]);

$storeTeam = array_column([$storeManager, $foodsaver_A, $foodsaver_C], 'id');
$I->assertEquals($storeTeam, $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::MEMBER,
]));

$teamConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', [
	'conversation_id' => $teamConversationId,
]);
$I->assertEquals($storeTeam, $teamConversationMembers);

$storeManagers = array_column([$storeManager], 'id');
$I->assertEquals($storeManagers, $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 1,
]));

// There were bugs with removed/demoted store managers staying in jumper chat
// See https://gitlab.com/foodsharing-dev/-/issues/104 for details :)
$jumperConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', [
	'conversation_id' => $jumperConversationId,
]);
$I->assertEquals($jumperConversationMembers, array_merge($storeManagers, $jumperIds));
