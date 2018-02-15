<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('add a foodsaver to a working group by seeing their ID in the tag select');

$group = $I->createWorkingGroup('a test group');
$foodsaver = $I->createFoodsaver(null, array('name' => 'WorkingGroupTestUser', 'nachname' => 'lastNameOfThat'));
$admin = $I->createFoodsaver();
$I->addBezirkMember($group['id'], $admin['id']);
$I->addBezirkAdmin($group['id'], $admin['id']);

$I->login($admin['email']);
$I->amOnPage($I->groupEditUrl($group['id']));
$I->waitForText('Mitglieder', 10, '#member-wrapper');
$I->click('#member-wrapper .tagedit-list');
$I->addInTagSelect('WorkingGroupTest', '#member-wrapper');

$I->waitForText('lastNameOfThat (' . $foodsaver['id'] . ')', 4, '#ui-id-7');
$I->click('#ui-id-7');
$I->click('Änderungen speichern');
$I->seeInDatabase('fs_foodsaver_has_bezirk', array('bezirk_id' => $group['id'], 'foodsaver_id' => $foodsaver['id']));
