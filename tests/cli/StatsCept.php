<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that stat generation jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php Stats bezirke');
$I->seeInShellOutput('Statistik Auswertung für Bezirke');
$I->runShellCommand('php -f run.php Stats betriebe');
$I->seeInShellOutput('Starting ::betriebe...');
$I->runShellCommand('php -f run.php Stats foodsaver');
$I->seeInShellOutput('Starting ::foodsaver...');
