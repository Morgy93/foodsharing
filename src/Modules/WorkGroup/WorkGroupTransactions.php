<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Message\MessageTransactions;
use Foodsharing\Modules\Region\ForumFollowerGateway;

class WorkGroupTransactions
{
    private WorkGroupGateway $workGroupGateway;
    private ForumFollowerGateway $forumFollowerGateway;
    private MessageTransactions $messageTransactions;
    private Session $session;

    public function __construct(
        WorkGroupGateway $workGroupGateway,
        ForumFollowerGateway $forumFollowerGateway,
        MessageTransactions $messageTransactions,
        Session $session
    ) {
        $this->workGroupGateway = $workGroupGateway;
        $this->forumFollowerGateway = $forumFollowerGateway;
        $this->messageTransactions = $messageTransactions;
        $this->session = $session;
    }

    /**
     * Removes a user from a working group and cancels the forum subscriptions.
     *
     * @throws \Exception
     */
    public function removeMemberFromGroup(int $groupId, int $memberId, ?string $message): void
    {
        $this->forumFollowerGateway->deleteForumSubscription($groupId, $memberId);
        $this->workGroupGateway->removeFromGroup($groupId, $memberId);
        if ($this->session->id() !== $memberId) {
            $params = ['{regionName}' => $this->workGroupGateway->getGroup($groupId)['name']];
            $this->messageTransactions->sendRequiredMessageToUser($memberId, $this->session->id(), 'kick_from_working_group', $message, $params);
        }
    }
}
