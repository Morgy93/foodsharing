<?php

namespace Foodsharing\unit;

use Codeception\Test\Unit;
use Foodsharing\Utility\EmailHelper;
use UnitTester;

class EmailHelperTest extends Unit
{
    protected UnitTester $tester;
    private EmailHelper $helper;

    protected function _before()
    {
        $this->helper = $this->tester->get(EmailHelper::class);
    }

    /**
     * @dataProvider _validEmailProvider
     */
    public function testValidEmailCheckValidEmails(string $email)
    {
        $isValid = $this->helper->validEmail($email);
        $this->assertTrue($isValid, "Email '$email' should be accepted");
    }

    /**
     * @dataProvider _invalidEmailProvider
     */
    public function testValidEmailCheckInvalidEmails(string $email)
    {
        $isValid = $this->helper->validEmail($email);
        $this->assertFalse($isValid, "Email '$email' shouldn't be accepted");
    }

    /**
     * @dataProvider _validFoodsharingAddresses
     */
    public function testIsFoodsharingEmailAddress(string $email)
    {
        $isValid = $this->helper->validEmail($email);
        $this->assertTrue($isValid, "'$email' should be detected as a foodsharing mail address");
    }

    /**
     * @dataProvider _invalidFoodsharingAddresses
     */
    public function testIsNoFoodsharingEmailAddress(string $email)
    {
        $isValid = $this->helper->validEmail($email);
        $this->assertTrue($isValid, "'$email' should be detected as a foodsharing mail address");
    }

    public function _validFoodsharingAddresses(): array
    {
        return array_map(function ($domain) {
            return ["user@$domain"];
        }, MAILBOX_OWN_DOMAINS);
    }

    public function _invalidFoodsharingAddresses(): array
    {
        $invalid_email_groups = array_map(function ($domain) {
            return [
                ["user@hello.$domain"],
                ["user@$domain.de"],
                ["user@xxx$domain"],
                ["user@{$domain}"],
                ["user@{$domain}.something"],
            ];
        }, MAILBOX_OWN_DOMAINS);

        return array_merge(...$invalid_email_groups);
    }

    public function _validEmailProvider(): array
    {
        return [
            ['a@b.de'],
            ['münchen@foodsharing.de'],
            ['te.st+abc@gmail.com'],
            ['he.llo@x.y'],
            ['email@example.com'],
            ['firstname.lastname@example.com'],
            ['email@subdomain.example.com'],
            ['firstname+lastname@example.com'],
            ['"email"@example.com'],
            ['1234567890@example.com'],
            ['email@[123.123.123.123]'],
            ['email@example-one.com'],
            ['_______@example.com'],
            ['email@example.name'],
            ['email@example.museum'],
            ['email@example.co.jp'],
            ['email@example.web'],
            ['あいうえお@example.com'],
            ['firstname-lastname@example.com']
        ];
    }

    public function _invalidEmailProvider(): array
    {
        return [
            [''],
            ['@'],
            ['test@localhost'],
            ['test@'],
            ['test@test'],
            ['@test'],
            ['herr@müller.de'],
            ['frau meyer@tets.de'],
            ["a@b.de\0b@b.de"],
            [' hello@space.com'],
            ['hello@space.com '],
            ['plainaddress'],
            ['#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            ['email.example.com'],
            ['email@example@example.com'],
            ['.email@example.com'],
            ['email.@example.com'],
            ['email..email@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example'],
            ['email@-example.com'],
            ['email@123.123.123.123'],
            ['email@111.222.333.44444'],
            ['email@example..com'],
            ['Abc..123@example.com'],
            ['”(),:;<>[\]@example.com'],
            ['just”not”right@example.com'],
            ['this\ is"really"not\allowed@example.com'],
        ];
    }
}
