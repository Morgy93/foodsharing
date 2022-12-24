<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;

class ApplicationTransactions
{
    private ApplicationGateway $applicationGateway;
    private BellGateway $bellGateway;

    public function __construct(
        ApplicationGateway $applicationGateway,
        BellGateway $bellGateway,
    ) {
        $this->applicationGateway = $applicationGateway;
        $this->bellGateway = $bellGateway;
    }

    public function acceptApplication(array $group, int $userId): void
    {
        $this->applicationGateway->acceptApplication($group['id'], $userId);

        $bellData = Bell::create('workgroup_request_accept_title', 'workgroup_request_accept', 'fas fa-user-check', [
            'href' => '/?page=bezirk&bid=' . $group['id']
        ], [
            'name' => $group['name']
        ], BellType::createIdentifier(BellType::WORK_GROUP_REQUEST_ACCEPTED, $userId));
        $this->bellGateway->addBell($userId, $bellData);
    }

    /**
     * Deletes a request for joining a work group and creates a bell notification for the applicant.
     */
    public function declineApplication(array $group, int $userId): void
    {
        $this->applicationGateway->denyApplication($group['id'], $userId);

        $bellData = Bell::create('workgroup_request_decline_title', 'workgroup_request_decline', 'fas fa-user-times', [
            'href' => '/?page=groups&p=' . $group['parent_id']
        ], [
            'name' => $group['name']
        ], BellType::createIdentifier(BellType::WORK_GROUP_REQUEST_DENIED, $userId));
        $this->bellGateway->addBell($userId, $bellData);
    }
}
