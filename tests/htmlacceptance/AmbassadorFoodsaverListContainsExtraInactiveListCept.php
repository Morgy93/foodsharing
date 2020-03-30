<?php

$testRegionId = 241;
$I = new HtmlAcceptanceTester($scenario);
$I->wantTo('see that the foodsaver list for a bezirk contains a second list with inactive foodsavers');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegionId]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'last_login' => null, 'bezirk_id' => $testRegionId]);
$inactiveFoodsaver = $I->createFoodsaver(null, ['name' => 'fs-i', 'nachname' => 'saver2', 'photo' => 'does-not-exist.jpg', 'last_login' => '2017-01-01 00:00:00', 'bezirk_id' => $testRegionId]);
$activeFoodsaver = $I->createFoodsaver(null, ['name' => 'fs-a', 'nachname' => 'saver3', 'photo' => 'does-not-exist.jpg', 'last_login' => (new \DateTime())->format('Y-m-d H:i:s'), 'bezirk_id' => $testRegionId]);
$ambassador = $I->createAmbassador(null, ['name' => 'ambassador-a', 'photo' => 'does-not-exist.jpg', 'last_login' => (new \DateTime())->format('Y-m-d H:i:s'), 'bezirk_id' => $testRegionId]);
$unrelatedFoodsaver = $I->createFoodsaver(null, ['name' => 'unrelated-fs']);
$I->addBezirkAdmin($testRegionId, $ambassador['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&bid=' . $testRegionId);
$I->see('Foodsaver in Göttingen', '#foodsaverlist');
$I->see('fs-a', '#foodsaverlist');
$I->see('fs-i', '#foodsaverlist');
$I->see('fs1', '#foodsaverlist');
$I->see('ambassador-a', '#foodsaverlist');

// This one should show nowhere
$I->dontSee('unrelated-fs');

$I->see('die sich 6 Monate', '#inactivefoodsaverlist');
$I->see('fs-i', '#inactivefoodsaverlist');
// That foodsaver never logged in, so is inactive as well. Actually this situation is hard to happen in real life as a not-logged-in user cannot have a region...
$I->see('fs1', '#inactivefoodsaverlist');
$I->dontSee('fs-a', '#inactivefoodsaverlist');
$I->dontSee('ambassador-a', '#inactivefoodsaverlist');
