<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\DTO\Store;
use Foodsharing\Utility\EmailHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

interface NotificationStrategy
{
    public function hasEMailSupport(): bool;

    public function getEMailTemplate(): string;

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator);

    public function createBell();
}

class InformStoreManagerNotificationStragety implements NotificationStrategy
{
    public function __construct(private readonly Store $store, private string $authorName, private int $requestingFoodsaver)
    {
    }

    public function hasEMailSupport(): bool
    {
        return false;
    }

    public function getEMailTemplate(): string
    {
        return '';
    }

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator): array
    {
        return [];
    }

    public function createBell()
    {
        return Bell::create('store_new_request_title', 'store_new_request', 'fas fa-user-plus', [
            'href' => '/?page=fsbetrieb&id=' . $this->store->id,
        ], [
            'user' => $this->authorName,
            'name' => $this->store->name,
            'requestFoodsaverId' => $this->requestingFoodsaver
        ], 'store-request-' . $this->store->id);
    }
}

class RegularPickupTimeChangeNotificationStragety implements NotificationStrategy
{
    public function __construct(private readonly Store $store, private string $authorName)
    {
    }

    public function hasEMailSupport(): bool
    {
        return false;
    }

    public function getEMailTemplate(): string
    {
        return '';
    }

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator): array
    {
        return [];
    }

    public function createBell()
    {
        return Bell::create('store_cr_times_title', 'store_cr_times', 'fas fa-user-clock', [
                'href' => '/?page=fsbetrieb&id=' . $this->store->id,
            ], [
                'user' => $this->authorName,
                'name' => $this->store->name,
            ], BellType::createIdentifier(BellType::STORE_TIME_CHANGED, $this->store->id));
    }
}

class InformTeamMemberNotificationStrategy implements NotificationStrategy
{
    public function __construct(private readonly Store $store, private string $authorName, private int $affectedFoodsaver, private int $logAction)
    {
    }

    public function hasEMailSupport(): bool
    {
        return false;
    }

    public function getEMailTemplate(): string
    {
        return '';
    }

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator): array
    {
        return [];
    }

    public function createBell()
    {
        if ($this->logAction === StoreLogAction::ADDED_WITHOUT_REQUEST) {
            $bellTitle = 'store_request_imposed_title';
            $bellMsg = 'store_request_imposed';
            $bellIcon = 'fas fa-user-plus';
            $bellId = 'store-imposed-' . $this->store->id . '-' . $this->affectedFoodsaver;
        } elseif ($this->logAction === StoreLogAction::MOVED_TO_JUMPER) {
            $bellTitle = 'store_request_accept_wait_title';
            $bellMsg = 'store_request_accept_wait';
            $bellIcon = 'fas fa-user-tag';
            $bellId = BellType::createIdentifier(BellType::STORE_REQUEST_WAITING, $this->affectedFoodsaver);
        } elseif ($this->logAction === StoreLogAction::REQUEST_APPROVED) {
            $bellTitle = 'store_request_accept_title';
            $bellMsg = 'store_request_accept';
            $bellIcon = 'fas fa-user-check';
            $bellId = BellType::createIdentifier(BellType::STORE_REQUEST_ACCEPTED, $this->affectedFoodsaver);
        } elseif ($this->logAction === StoreLogAction::REQUEST_DECLINED) {
            $bellTitle = 'store_request_deny_title';
            $bellMsg = 'store_request_deny';
            $bellIcon = 'fas fa-user-times';
            $bellId = BellType::createIdentifier(BellType::STORE_REQUEST_REJECTED, $this->affectedFoodsaver);
        } else {
            throw new \DomainException('Unknown store-team action: ' . $this->logAction);
        }
        $bellLink = '/?page=fsbetrieb&id=' . $this->store->id;

        return Bell::create($bellTitle, $bellMsg, $bellIcon, [
            'href' => $bellLink,
        ], [
            'user' => $this->authorName,
            'name' => $this->store->name,
        ], $bellId);
    }
}

class NewStoreCooperationNotificationStrategy implements NotificationStrategy
{
    public function __construct(private readonly Store $store, private string $authorName)
    {
    }

    public function hasEMailSupport(): bool
    {
        return false;
    }

    public function getEMailTemplate(): string
    {
        return '';
    }

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator): array
    {
        return [];
    }

    public function createBell()
    {
        return Bell::create('store_new_title', 'store_new', 'fas fa-store-alt', [
            'href' => '/?page=fsbetrieb&id=' . $this->store->id
        ], [
            'user' => $this->authorName,
            'name' => $this->store->name
        ], BellType::createIdentifier(BellType::NEW_STORE, $this->store->id));
    }
}

class FetchWarningNotificationStragety implements NotificationStrategy
{
    public function __construct(private readonly Store $store, private readonly array $emptyPickups)
    {
    }

    public function hasEMailSupport(): bool
    {
        return true;
    }

    public function getEMailTemplate(): string
    {
        return 'chat/fetch_warning';
    }

    public function generateEMailTemplateParameter(array $receiverFs, TranslatorInterface $translator): array
    {
        return [
            'anrede' => $translator->trans('salutation.' . $receiverFs['geschlecht']),
            'name' => $receiverFs['name'],
            'betrieb' => $this->store->name,
            'link' => BASE_URL . '/?page=fsbetrieb&id=' . $this->store->id
        ];
    }

    public function createBell()
    {
        return Bell::create('store_fetch_warning_title', 'store_fetch_waring', 'fas fa-store-alt', [
            'href' => '/?page=fsbetrieb&id=' . $this->store->id
        ], [
            'user' => 'Foodsaver Platform',
            'name' => $this->store->name,
            'pickups' => $this->emptyPickups
        ], BellType::createIdentifier(BellType::NEW_STORE, $this->store->id));
    }
}

/**
 * Implement this interface if you want to subscribe to the BellUpdateTrigger.
 */
class NotificationTransaction
{
    public function __construct(
        private readonly BellGateway $bellGateway,
        private readonly EmailHelper $emailHelper,
        private readonly FoodsaverGateway $foodsaverGateway,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function sendNotification(array $foodsaversIds, NotificationStrategy $bellStrategy)
    {
        $fsIdsInformedByBell = [];
        foreach ($foodsaversIds as $foodsaverId) {
            $fsInfo = $this->foodsaverGateway->getFoodsaverBasics($foodsaverId);

            // Load store follower information and information types
            // Filter by user requested information way

            $fsIdsInformedByBell[] = $foodsaverId;

            if ($bellStrategy->hasEMailSupport()) {
                $this->emailHelper->tplMail($bellStrategy->getEMailTemplate(), $fsInfo['email'], $bellStrategy->generateEMailTemplateParameter($fsInfo, $this->translator));
            }
        }
        $this->bellGateway->addBell($fsIdsInformedByBell, $bellStrategy->createBell());
    }
}
