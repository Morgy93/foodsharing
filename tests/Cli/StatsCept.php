<?php

declare(strict_types=1);

namespace Tests\Cli;

use Tests\Support\CliTester;

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that stat generation jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php Stats bezirke', false);
$I->seeInShellOutput('Statistik Auswertung für Bezirke');
$I->runShellCommand('php -f run.php Stats betriebe', false);
$I->seeInShellOutput('::betriebe...');
$I->runShellCommand('php -f run.php Stats foodsaver', false);
$I->seeInShellOutput('::foodsaver...');
