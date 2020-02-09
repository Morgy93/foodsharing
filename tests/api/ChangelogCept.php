<?php

$I = new ApiTester($scenario);
$I->wantTo('see the changelog being rendered into html');

$request = ['page' => 'content', 'sub' => 'changelog'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContains('href="https://wiki.foodsharing.de/Foodsharing.de_Plattform:_%C3%84nderungshistorie"');
$I->seeResponseContains('href="https://gitlab.com/NerdyProjects"');
$I->seeResponseContains('Changelog');
