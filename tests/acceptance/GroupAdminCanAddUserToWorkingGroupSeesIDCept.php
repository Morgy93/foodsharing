<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('add a foodsaver to a working group by seeing their ID in the tag select');

$group = $I->createWorkingGroup('a test group');
$foodsaver = $I->createFoodsaver(null, ['name' => 'WorkingGroupTestUser', 'nachname' => 'lastNameOfThat']);
$I->addRegionMember($group['parent_id'], $foodsaver['id']);
$admin = $I->createFoodsaver();
$I->addRegionMember($group['id'], $admin['id']);
$I->addRegionAdmin($group['id'], $admin['id']);
$I->addRegionMember($group['parent_id'], $admin['id']);
$I->addRegionAdmin($group['parent_id'], $admin['id']);

$I->login($admin['email']);
$I->amOnPage($I->groupMemberListUrl($group['id']));
$I->waitForElement('.fa-cog');
$I->click('Ansicht für Administrator*innen aktivieren');
$I->waitForElement('#new-foodsaver-search');
$I->fillField('#new-foodsaver-search div input', $foodsaver['name']);
$I->waitForElement('.suggestions');
$I->click('li[id$="suggestion-' . $foodsaver['id'] . '"]');
$I->click('.fa-user-plus');
$I->waitForActiveAPICalls();
$I->seeInDatabase('fs_foodsaver_has_bezirk', ['bezirk_id' => $group['id'], 'foodsaver_id' => $foodsaver['id']]);
