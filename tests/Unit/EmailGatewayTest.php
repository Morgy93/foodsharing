<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Foodsharing\Modules\Email\EmailGateway;
use Tests\Support\UnitTester;

class EmailGatewayTest extends Unit
{
    protected UnitTester $tester;
    private EmailGateway $gateway;

    public function _before()
    {
        $this->gateway = $this->tester->get(EmailGateway::class);
    }

    public function testInitEmail(): void
    {
        $sender = $this->tester->createFoodsaver();
        $recipient = $this->tester->createFoodsaver();
        $message = 'test';
        $mailboxId = 42;

        $this->gateway->initEmail($sender['id'], $mailboxId, [$recipient], $message, '', '');

        $this->tester->seeInDatabase('fs_send_email', ['foodsaver_id' => $sender['id'], 'message' => $message]);
        $this->tester->seeInDatabase('fs_email_status', ['foodsaver_id' => $recipient['id']]);
    }
}
