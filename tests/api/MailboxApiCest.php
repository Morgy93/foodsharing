<?php

use Codeception\Example;
use Codeception\Util\HttpCode;
use Faker\Generator;
use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;
use Foodsharing\Modules\Core\DBConstants\Uploads\UploadUsage;

class MailboxApiCest
{
    private array $region;
    private int $mailboxId;
    private array $user;
    private array $userAmbassador;
    private array $emailIdsForFolder;
    private Generator $faker;

    public function _before(ApiTester $I)
    {
        $this->faker = Faker\Factory::create('de_DE');

        // create a region with a mailbox
        $this->region = $I->createRegion(null);
        $this->mailboxId = $I->grabFromDatabase('fs_bezirk', 'mailbox_id', ['id' => $this->region['id']]);

        // create a foodsaver and an ambassador in that region
        $this->user = $I->createFoodsaver();
        $I->addRegionMember($this->region['id'], $this->user['id']);
        $this->userAmbassador = $I->createAmbassador();
        $I->addRegionMember($this->region['id'], $this->userAmbassador['id']);
        $I->addRegionAdmin($this->region['id'], $this->userAmbassador['id']);

        // Store the list of emails from the mailbox so that they don't need to be fetched in every test
        foreach ([MailboxFolder::FOLDER_INBOX, MailboxFolder::FOLDER_SENT, MailboxFolder::FOLDER_TRASH] as $folder) {
            $this->emailIdsForFolder[$folder] = $this->listEmailIDs($I, $this->mailboxId, $folder);
        }
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     * @example["FOLDER_TRASH"]
     */
    public function canListEmailsAsAmbassador(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);

        // try without login
        $I->sendGet('api/mailbox/all/' . $this->mailboxId . '/' . $folder);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        // try with login
        $I->login($this->userAmbassador['email']);
        $I->sendGet('api/mailbox/all/' . $this->mailboxId . '/' . $folder);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([[
            'mailboxId' => $this->mailboxId,
            'mailboxFolder' => $folder,
        ]]);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     * @example["FOLDER_TRASH"]
     */
    public function canNotListEmailsAsFoodsaver(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);

        $I->login($this->user['email']);
        $I->sendGet('api/mailbox/all/' . $this->mailboxId . '/' . $folder);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     * @example["FOLDER_TRASH"]
     */
    public function canFetchEmailAsAmbassador(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);

        $emails = $this->listEmailIDs($I, $this->mailboxId, $folder);
        $emailId = $emails[array_rand($emails)];
        // the API changes the 'isRead' status of the email, which is why it needs to be fetched from the database before
        $email = $this->fetchEmail($I, $emailId);

        // try without login
        $I->sendGet('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        // try with login
        $I->login($this->userAmbassador['email']);
        $I->sendGet('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($email);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     * @example["FOLDER_TRASH"]
     */
    public function canNotFetchEmailAsFoodsaver(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);
        $emailId = $this->getRandomEmailId($folder);

        $I->login($this->user['email']);
        $I->sendGet('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     * @example["FOLDER_TRASH"]
     */
    public function canSetEmailToReadAndUnread(ApiTester $I, Example $example)
    {
        // take a random email and set it to unread
        $folder = $this->getFolderIdByName($example[0]);
        $emailId = $this->getRandomEmailId($folder);
        $I->updateInDatabase('fs_mailbox_message', ['read' => 0], ['id' => $emailId]);

        // try without login
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['isRead' => true]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'read' => 0]);

        // set to read
        $I->login($this->userAmbassador['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['isRead' => true]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'read' => 1]);

        // set to unread again
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['isRead' => false]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'read' => 0]);
    }

    /**
     * @example["FOLDER_INBOX", "FOLDER_TRASH"]
     * @example["FOLDER_SENT", "FOLDER_TRASH"]
     * @example["FOLDER_TRASH", "FOLDER_INBOX"]
     */
    public function canNotMoveEmailAsFoodsaver(ApiTester $I, Example $example)
    {
        $srcFolder = $this->getFolderIdByName($example[0]);
        $dstFolder = $this->getFolderIdByName($example[1]);
        $emailId = $this->getRandomEmailId($srcFolder);

        $I->login($this->user['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['folder' => $dstFolder]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => $srcFolder]);
    }

    /**
     * @example["FOLDER_INBOX", "FOLDER_TRASH"]
     * @example["FOLDER_SENT", "FOLDER_TRASH"]
     * @example["FOLDER_TRASH", "FOLDER_INBOX"]
     */
    public function canMoveEmail(ApiTester $I, Example $example)
    {
        $srcFolder = $this->getFolderIdByName($example[0]);
        $dstFolder = $this->getFolderIdByName($example[1]);
        $emailId = $this->getRandomEmailId($srcFolder);

        // try without login
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['folder' => $dstFolder]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => $srcFolder]);

        // try with login
        $I->login($this->userAmbassador['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPatch('api/mailbox/' . $emailId, ['folder' => $dstFolder]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => $dstFolder]);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     */
    public function canNotDeleteEmailAsFoodsaver(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);
        $emailId = $this->getRandomEmailId($folder);

        $I->login($this->user['email']);
        $I->sendDelete('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => $folder]);
    }

    /**
     * @example["FOLDER_INBOX"]
     * @example["FOLDER_SENT"]
     */
    public function canDeleteEmail(ApiTester $I, Example $example)
    {
        $folder = $this->getFolderIdByName($example[0]);
        $emailId = $this->getRandomEmailId($folder);

        // try without login
        $I->sendDelete('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => $folder]);

        // move to trash
        $I->login($this->userAmbassador['email']);
        $I->sendDelete('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('fs_mailbox_message', ['id' => $emailId, 'folder' => MailboxFolder::FOLDER_TRASH]);

        // delete from trash
        $I->sendDelete('api/mailbox/' . $emailId);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->cantSeeInDatabase('fs_mailbox_message', ['id' => $emailId]);
    }

    public function canNotSendEmailAsFoodsaver(ApiTester $I)
    {
        $email = $this->createEmailForSending();

        $I->login($this->user['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->cantSeeInDatabase('fs_mailbox_message', ['subject' => $email['subject']]);
    }

    public function canSendEmail(ApiTester $I)
    {
        $email = $this->createEmailForSending();

        // try without login
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        // try with login
        $I->login($this->userAmbassador['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        // compare sent email with response
        $I->seeResponseContainsJson([
            'mailboxId' => $this->mailboxId,
            'mailboxFolder' => MailboxFolder::FOLDER_SENT,
            'subject' => $email['subject'],
            'body' => $email['body'],
        ]);

        // compare sent email with database
        $emailId = $I->grabDataFromResponseByJsonPath('id')[0];
        $I->canSeeInDatabase('fs_mailbox_message', [
            'id' => $emailId,
            'folder' => MailboxFolder::FOLDER_SENT,
            'subject' => $email['subject'],
            'body' => $email['body'],
        ]);
    }

    public function canNotSendEmptyEmail(ApiTester $I)
    {
        $I->login($this->userAmbassador['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, []);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function canNotSendEmailToTooManyRecipients(ApiTester $I)
    {
        // maximum is 100 recipients
        $email = $this->createEmailForSending(101);

        $I->login($this->userAmbassador['email']);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function canSendEmailWithAttachment(ApiTester $I)
    {
        $I->login($this->userAmbassador['email']);

        // upload attachment
        $filename = $this->faker->word() . '.' . $this->faker->fileExtension();
        $body = $this->faker->realTextBetween(100, 1000);
        $I->sendPost('api/uploads', [
            'filename' => $filename,
            'body' => base64_encode($body),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $uuid = json_decode($I->grabResponse(), true)['uuid'];

        // send the email
        $email = $this->createEmailForSending();
        $email['attachments'][] = [
            'filename' => $filename,
            'uuid' => $uuid,
        ];
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        // compare the sent email with the response
        $I->seeResponseContainsJson([
            'mailboxId' => $this->mailboxId,
            'mailboxFolder' => MailboxFolder::FOLDER_SENT,
            'subject' => $email['subject'],
            'body' => $email['body'],
            'attachments' => [[
                'fileName' => $filename,
                'hashedFileName' => $uuid,
                'size' => strlen($body)
            ]],
        ]);

        // compare the sent email with the database
        $emailId = $I->grabDataFromResponseByJsonPath('id')[0];
        $I->canSeeInDatabase('fs_mailbox_message', [
            'id' => $emailId,
            'folder' => MailboxFolder::FOLDER_SENT,
            'subject' => $email['subject'],
            'body' => $email['body'],
        ]);

        // check that the uploaded file's usage was set
        $I->canSeeInDatabase('uploads', [
            'used_in' => UploadUsage::EMAIL_ATTACHMENT->value,
            'usage_id' => $emailId,
        ], ['uuid' => $uuid]) > 0;
    }

    public function canNotSendEmailWithInvalidAttachment(ApiTester $I)
    {
        $I->login($this->userAmbassador['email']);

        $email = $this->createEmailForSending();
        $email['attachments'][] = [
            'uuid' => $this->faker->uuid(),
        ];
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function canNotSendEmailWithSomeoneElsesUpload(ApiTester $I)
    {
        // upload attachment as user
        $I->login($this->user['email']);
        $filename = $this->faker->word() . '.' . $this->faker->fileExtension();
        $body = $this->faker->realTextBetween(100, 1000);
        $I->sendPost('api/uploads', [
            'filename' => $filename,
            'body' => base64_encode($body),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $uuid = json_decode($I->grabResponse(), true)['uuid'];

        // send the email as ambassador
        $I->login($this->userAmbassador['email']);
        $email = $this->createEmailForSending();
        $email['attachments'][] = [
            'filename' => $filename,
            'uuid' => $uuid,
        ];
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('api/mailbox/' . $this->mailboxId, $email);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        // check that the uploaded file's usage was not set
        $I->canSeeInDatabase('uploads', [
            'used_in' => null,
            'usage_id' => null,
        ], ['uuid' => $uuid]) > 0;
    }

    /**
     * Returns the id of a random email from the specified mailbox folder. This function assumes that there is at least
     * one email in that folder.
     */
    private function getRandomEmailId(int $folder): int
    {
        $emails = $this->emailIdsForFolder[$folder];

        return $emails[array_rand($emails)];
    }

    /**
     * Returns the IDs of all emails in the specified mailbox and folder.
     */
    private function listEmailIDs(ApiTester $I, int $mailboxId, int $folder): array
    {
        return $I->grabColumnFromDatabase('fs_mailbox_message', 'id', ['mailbox_id' => $mailboxId, 'folder' => $folder]);
    }

    private function getFolderIdByName(string $name): int
    {
        $folder = new ReflectionClass(new MailboxFolder());
        $constants = $folder->getConstants();

        return $constants[$name] ?? 0;
    }

    /**
     * Fetches all columns of an email from the database and returns it in the same form as the API.
     */
    private function fetchEmail(ApiTester $I, int $emailId): array
    {
        return [
            'id' => $emailId,
            'mailboxId' => $I->grabFromDatabase('fs_mailbox_message', 'mailbox_id', ['id' => $emailId]),
            'mailboxFolder' => $I->grabFromDatabase('fs_mailbox_message', 'folder', ['id' => $emailId]),
            'subject' => $I->grabFromDatabase('fs_mailbox_message', 'subject', ['id' => $emailId]),
            'body' => $I->grabFromDatabase('fs_mailbox_message', 'body', ['id' => $emailId]),
            'isRead' => boolval($I->grabFromDatabase('fs_mailbox_message', '`read`', ['id' => $emailId])),
            'isAnswered' => boolval($I->grabFromDatabase('fs_mailbox_message', 'answer', ['id' => $emailId]))
        ];
    }

    private function createEmailForSending(?int $toCount = null): array
    {
        return [
            'to' => $this->createRandomEmailAddressArray($toCount ?? $this->faker->numberBetween(0, 20)),
            'cc' => $this->createRandomEmailAddressArray($this->faker->numberBetween(0, 20)),
            'bcc' => $this->createRandomEmailAddressArray($this->faker->numberBetween(0, 20)),
            'subject' => $this->faker->realTextBetween(3, 100),
            'body' => $this->faker->text(10000),
            'attachments' => [],
            'replyEmailId' => null,
        ];
    }

    private function createRandomEmailAddressArray(int $length): array
    {
        $addresses = [];
        foreach (range(0, $length) as $_) {
            $addresses[] = $this->faker->email();
        }

        return $addresses;
    }
}
