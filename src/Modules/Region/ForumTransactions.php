<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\RestApi\Models\Notifications\Thread;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\FlashMessageHelper;
use Foodsharing\Utility\Sanitizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForumTransactions
{
    public function __construct(
        private BellGateway $bellGateway,
        private FoodsaverGateway $foodsaverGateway,
        private ForumGateway $forumGateway,
        private ForumFollowerGateway $forumFollowerGateway,
        private Session $session,
        private RegionGateway $regionGateway,
        private Sanitizer $sanitizerService,
        private EmailHelper $emailHelper,
        private FlashMessageHelper $flashMessageHelper,
        private TranslatorInterface $translator,
        private GroupFunctionGateway $groupFunctionGateway
    ) {
    }

    public function url($regionId, $ambassadorForum, $threadId = null, $postId = null): string
    {
        $url = '/?page=bezirk&bid=' . $regionId . '&sub=' . ($ambassadorForum ? 'botforum' : 'forum');
        if ($threadId) {
            $url .= '&tid=' . $threadId;
        }
        if ($postId) {
            $url .= '&pid=' . $postId . '#post' . $postId;
        }

        return $url;
    }

    public function addPostToThread(int $foodsaverId, int $threadId, string $body): int
    {
        $rawBody = $body;
        $pid = $this->forumGateway->addPost($foodsaverId, $threadId, $body);
        $this->notifyFollowersViaMail($threadId, $rawBody, $foodsaverId, $pid);
        $this->notifyFollowersViaBell($threadId, $foodsaverId, $pid);

        return $pid;
    }

    public function deletePostFromThread(int $postId, int $authorId): void
    {
        $this->adjustBellNotification($postId, $authorId);
        $this->forumGateway->deletePost($postId);
    }

    private function notifyFollowersViaBell(int $threadId, int $authorId, int $postId): void
    {
        $subscribedGroups = $this->forumFollowerGateway->getThreadFollowersByLastUnseenBellForThread($threadId, $authorId);

        if (empty($subscribedGroups)) {
            return;
        }

        $info = $this->forumGateway->getThreadInfo($threadId);
        $regionName = $this->regionGateway->getRegionName($info['region_id']);

        foreach ($subscribedGroups as &$group) {
            if (empty($group['bellId'])) {
                $count = 1;
                $href = $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId);
            } else {
                $count = unserialize($group['vars'])['count'] + 1;
                $href = unserialize($group['attr'])['href'];
                $this->bellGateway->deleteBellForFoodsavers($group['bellId'], $group['foodsaverIds']);
            }

            $bell = $this->createForumBell($threadId, $postId, $count, $href, $regionName, $info['title']);
            $this->bellGateway->addBell($group['foodsaverIds'], $bell);
        }
    }

    private function adjustBellNotification(int $postId, int $authorId): void
    {
        $threadId = $this->forumGateway->getThreadForPost($postId);
        $groups = $this->forumFollowerGateway->getUsersWithUnseenBellIncludingDeletedPost($threadId, $postId, $authorId);

        if (empty($groups)) {
            return;
        }

        foreach ($groups as &$group) {
            $vars = unserialize($group['vars']);
            $href = unserialize($group['attr'])['href'];

            $this->bellGateway->deleteBellForFoodsavers($group['bellId'], $group['foodsaverIds']);

            if ($vars['count'] > 1) {
                $bell = $this->createForumBell($threadId, $postId, $vars['count'] - 1, $href, $vars['forum'], $vars['title']);
                $this->bellGateway->addBell($group['foodsaverIds'], $bell);
            }
        }
    }

    private function createForumBell(int $threadId, int $postId, int $count, string $href, string $regionName, string $title): Bell
    {
        return Bell::create(
            'forum_post_title',
            'forum_post.' . ($count == 1 ? 'one' : 'many'),
            'fas fa-comment' . ($count == 1 ? '' : 's'),
            ['href' => $href],
            [
                'forum' => $regionName,
                'title' => $title,
                'count' => $count,
                'user' => $this->session->user('name'),
            ],
            BellType::createIdentifier(BellType::NEW_FORUM_POST, $threadId, $postId, $count)
        );
    }

    public function createThread($fsId, $title, $body, $region, $ambassadorForum, $isActive, $sendMail)
    {
        $threadId = $this->forumGateway->addThread($fsId, $region['id'], $title, $body, $isActive, $ambassadorForum);
        if (!$isActive) {
            $this->notifyAdminsModeratedThread($region, $threadId, $body);
        } else {
            if ($sendMail) {
                $this->notifyMembersOfForumAboutNewThreadViaMail($region, $threadId, $ambassadorForum);
            } else {
                $this->flashMessageHelper->info($this->translator->trans('forum.thread.no_mail'));
            }
        }

        return $threadId;
    }

    private function sendNotificationMail(array $recipients, string $template, array $data): void
    {
        foreach ($recipients as $recipient) {
            $this->emailHelper->tplMail(
                $template,
                $recipient['email'],
                array_merge($data, [
                    'anrede' => $this->translator->trans('salutation.' . $recipient['geschlecht']),
                    'name' => $recipient['name'],
                ])
            );
        }
    }

    public function notifyFollowersViaMail($threadId, $rawPostBody, $postFrom, $postId): void
    {
        if ($follower = $this->forumFollowerGateway->getThreadEmailFollower($postFrom, $threadId)) {
            $info = $this->forumGateway->getThreadInfo($threadId);
            $posterName = $this->foodsaverGateway->getFoodsaverName($this->session->id());
            $data = [
                'link' => BASE_URL . $this->url($info['region_id'], $info['ambassador_forum'], $threadId, $postId),
                'thread' => $info['title'],
                'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
                'poster' => $posterName
            ];
            $this->sendNotificationMail($follower, 'forum/answer', $data);
        }
    }

    private function notifyAdminsModeratedThread($region, $threadId, $rawPostBody): void
    {
        $thread = $this->forumGateway->getThread($threadId);
        $posterName = $this->foodsaverGateway->getFoodsaverName($thread['creator_id']);
        $moderationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($region['id'], WorkgroupFunction::MODERATION);
        if (empty($moderationGroup)) {
            $moderators = $this->foodsaverGateway->getAdminsOrAmbassadors($region['id']);
        } else {
            $moderators = $this->foodsaverGateway->getAdminsOrAmbassadors($moderationGroup);
        }
        if ($moderators) {
            $data = [
                'link' => BASE_URL . $this->url($region['id'], false, $threadId),
                'thread' => $thread['title'],
                'post' => $this->sanitizerService->markdownToHtml($rawPostBody),
                'poster' => $posterName,
                'bezirk' => $region['name'],
            ];

            $this->sendNotificationMail($moderators, 'forum/activation', $data);
        }
    }

    private function notifyMembersOfForumAboutNewThreadViaMail(array $regionData, int $threadId, bool $isAmbassadorForum): void
    {
        $regionType = $this->regionGateway->getType($regionData['id']);
        if (!$isAmbassadorForum && in_array($regionType, [UnitType::COUNTRY, UnitType::FEDERAL_STATE])) {
            $this->flashMessageHelper->info($this->translator->trans('forum.thread.too_big_to_mail'));

            return;
        } else {
            $this->flashMessageHelper->info($this->translator->trans('forum.thread.with_mail'));
        }

        $thread = $this->forumGateway->getThread($threadId);
        $body = $this->forumGateway->getPost($thread['last_post_id'])['body'];

        $posterName = $this->foodsaverGateway->getFoodsaverName($thread['creator_id']);

        if ($isAmbassadorForum) {
            $recipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionData['id']);
        } else {
            $recipients = $this->foodsaverGateway->listActiveWithFullNameByRegion($regionData['id']);
        }

        $data = [
            'bezirk' => $regionData['name'],
            'poster' => $posterName,
            'thread' => $thread['title'],
            'link' => BASE_URL . $this->url($regionData['id'], $isAmbassadorForum, $threadId),
            'post' => $this->sanitizerService->markdownToHtml($body),
            ];
        $this->sendNotificationMail($recipients,
            $isAmbassadorForum ? 'forum/new_region_ambassador_message' : 'forum/new_message', $data);
    }

    public function addReaction($fsId, $postId, $key): void
    {
        if (!$fsId || !$postId || !$key) {
            throw new \InvalidArgumentException();
        }
        $this->forumGateway->addReaction($postId, $fsId, $key);
    }

    public function removeReaction($fsId, $postId, $key): void
    {
        if (!$fsId || !$postId || !$key) {
            throw new \InvalidArgumentException();
        }
        $this->forumGateway->removeReaction($postId, $fsId, $key);
    }

    /**
     * Updates the user's notification settings for a list of forum threads individually.
     *
     * @param Thread[] $threads
     */
    public function updateThreadNotifications(int $userId, array $threads): void
    {
        foreach ($threads as $thread) {
            $threadIdsToUnfollow = [];

            if ($thread->infotype == InfoType::NONE) {
                $threadIdsToUnfollow[] = $thread->id;
            }
            $this->forumFollowerGateway->updateInfoType($userId, $thread->id, $thread->infotype);
        }

        if (!empty($threadIdsToUnfollow)) {
            foreach ($threadIdsToUnfollow as $threadId) {
                $this->forumFollowerGateway->unfollowThreadByEmail($userId, $threadId);
            }
        }
    }
}
