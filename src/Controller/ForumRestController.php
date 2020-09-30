<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Region\ForumGateway;
use Foodsharing\Modules\Region\ForumTransactions;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Utility\Sanitizer;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForumRestController extends AbstractFOSRestController
{
	private Session $session;
	private RegionGateway $regionGateway;
	private ForumGateway $forumGateway;
	private ForumFollowerGateway $forumFollowerGateway;
	private ForumPermissions $forumPermissions;
	private ForumTransactions $forumTransactions;
	private Sanitizer $sanitizerService;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		ForumPermissions $forumPermissions,
		ForumTransactions $forumTransactions,
		Sanitizer $sanitizerService
	) {
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->forumGateway = $forumGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumPermissions = $forumPermissions;
		$this->forumTransactions = $forumTransactions;
		$this->sanitizerService = $sanitizerService;
	}

	private function normalizeThread(array $thread): array
	{
		$normalizedThread = [
			'id' => $thread['id'],
			'regionId' => $thread['regionId'],
			'regionSubId' => $thread['regionSubId'],
			'title' => $thread['title'],
			'createdAt' => str_replace(' ', 'T', $thread['time']),
			'isSticky' => boolval($thread['sticky'] ?? false),
			'isActive' => boolval($thread['active'] ?? true),
			'lastPost' => [
				'id' => $thread['last_post_id'],
			],
			'creator' => [
				'id' => $thread['creator_id'],
			]
		];
		if (isset($thread['post_time'])) {
			$normalizedThread['lastPost']['createdAt'] = str_replace(' ', 'T', $thread['post_time']);
			$normalizedThread['lastPost']['body'] = $this->sanitizerService->markdownToHtml($thread['post_body']);
			$normalizedThread['lastPost']['author'] = RestNormalization::normalizeUser($thread, 'foodsaver_');
		}
		if (isset($thread['creator_name'])) {
			$normalizedThread['creator'] = RestNormalization::normalizeUser($thread, 'creator_');
		}

		return $normalizedThread;
	}

	private function normalizePost(array $post): array
	{
		return [
			'id' => $post['id'],
			'body' => $this->sanitizerService->markdownToHtml($post['body']),
			'createdAt' => str_replace(' ', 'T', $post['time']),
			'author' => RestNormalization::normalizeUser($post, 'author_'),
			'reactions' => $post['reactions'] ?: new \ArrayObject(),
			'mayDelete' => $this->forumPermissions->mayDeletePost($post)
		];
	}

	/**
	 * Gets available threads including their last post.
	 *
	 * @SWG\Parameter(name="forumId", in="path", type="integer",
	 *   description="which forum to return threads for (region or group)")
	 * @SWG\Parameter(name="forumSubId", in="path", type="integer",
	 *   description="each region/group has another namespace to separate different forums with the same base id (region/group id, here: forumId). So with any forumId, there is (currently) 2, possibly infinite, actual forums (list of threads).
	 * 0: Forum, 1: Ambassador forum")
	 * @SWG\Parameter(name="limit", in="query", type="integer", description="how many search results to return")
	 * @SWG\Parameter(name="offset", in="query", type="integer", description="starting with which result")
	 * @SWG\Response(response="200", description="Success",
	 *     @SWG\Schema(type="object", @SWG\Property(property="data", type="array", @SWG\Items(type="object",
	 *     @SWG\Property(property="id", type="integer", description="thread id"),
	 *     @SWG\Property(property="regionId", type="integer", description="region/forum id"),
	 *     @SWG\Property(property="regionSubId", type="integer", description="region/forum sub id"),
	 *     @SWG\Property(property="title", type="string", description="thread title"),
	 *     @SWG\Property(property="createdAt", type="integer", description="region/forum sub id"),
	 *     @SWG\Property(property="isSticky", type="integer", description="region/forum sub id"),
	 *     @SWG\Property(property="isActive", type="integer", description="region/forum sub id"),
	 *     @SWG\Property(property="lastPost", type="object", @SWG\Items()),
	 *     @SWG\Property(property="creator", type="object", @SWG\Items()),
	 *
	 * ))))
	 * @SWG\Response(response="403", description="Insufficient permissions to view that forum.")
	 * @SWG\Tag(name="forum")
	 * @Rest\Get("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="how many search results to return")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="starting with which result")
	 */
	public function listThreadsAction(int $forumId, int $forumSubId, ParamFetcher $paramFetcher): SymfonyResponse
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}

		$limit = intval($paramFetcher->get('limit'));
		$offset = intval($paramFetcher->get('offset'));

		$threads = $this->getNormalizedThreads($forumId, $forumSubId, $limit, $offset);

		$view = $this->view([
			'data' => $threads
		], 200);

		return $this->handleView($view);
	}

	private function getNormalizedThreads(int $forumId, int $forumSubId, int $limit, int $offset): array
	{
		$threads = $this->forumGateway->listThreads($forumId, $forumSubId, $limit, $offset);
		$threads = array_map(function ($thread) {
			return $this->normalizeThread($thread);
		}, $threads);

		return $threads;
	}

	/**
	 * Get a single forum thread including some of its messages.
	 *
	 * @SWG\Parameter(name="threadId", in="path", type="integer",
	 *   description="which ID to return threads for")
	 *
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to view that forum/thread")
	 * @SWG\Response(response="404", description="Thread does not exist.")
	 * @SWG\Tag(name="forum")
	 * @Rest\Get("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 */
	public function getThreadAction(int $threadId): SymfonyResponse
	{
		$thread = $this->forumGateway->getThread($threadId);

		if (!$thread) {
			throw new HttpException(404);
		}

		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$thread = $this->normalizeThread($thread);
		$posts = $this->forumGateway->listPosts($threadId);

		$thread['isFollowingEmail'] = $this->forumFollowerGateway->isFollowingEmail($this->session->id(), $threadId);
		$thread['isFollowingBell'] = $this->forumFollowerGateway->isFollowingBell($this->session->id(), $threadId);
		$thread['mayModerate'] = $this->forumPermissions->mayModerate($threadId);
		$thread['posts'] = array_map(function ($post) {
			return $this->normalizePost($post);
		}, $posts);

		$view = $this->view([
			'data' => $thread
		], 200);

		return $this->handleView($view);
	}

	/**
	 * Create a post inside a thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Post("forum/thread/{threadId}/posts", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="body", description="post message")
	 */
	public function createPostAction(int $threadId, ParamFetcher $paramFetcher): SymfonyResponse
	{
		if (!$this->forumPermissions->mayPostToThread($threadId)) {
			throw new HttpException(403);
		}

		$body = $paramFetcher->get('body');
		$this->forumTransactions->addPostToThread($this->session->id(), $threadId, $body);

		return $this->handleView($this->view());
	}

	/**
	 * Create a thread inside a forum.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Post("forum/{forumId}/{forumSubId}", requirements={"forumId" = "\d+", "forumSubId" = "\d"})
	 * @Rest\RequestParam(name="title", description="title of thread")
	 * @Rest\RequestParam(name="body", description="post message")
	 */
	public function createThreadAction(int $forumId, int $forumSubId, ParamFetcher $paramFetcher): SymfonyResponse
	{
		if (!$this->forumPermissions->mayAccessForum($forumId, $forumSubId)) {
			throw new HttpException(403);
		}

		$body = $paramFetcher->get('body');
		$title = $paramFetcher->get('title');
		$regionDetails = $this->regionGateway->getRegionDetails($forumId);
		$postActiveWithoutModeration = ($this->session->user('verified') && !$regionDetails['moderated']) || $this->session->isAmbassadorForRegion([$forumId]);

		$threadId = $this->forumTransactions->createThread($this->session->id(), $title, $body, $regionDetails, $forumSubId, $postActiveWithoutModeration, true);

		return $this->getThreadAction($threadId);
	}

	/**
	 * Change attributes for a thread: Stickyness, activate thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Patch("forum/thread/{threadId}", requirements={"threadId" = "\d+"})
	 * @Rest\RequestParam(name="isSticky", nullable=true, default=null, description="should thread be pinned to the top of forum?")
	 * @Rest\RequestParam(name="isActive", nullable=true, default=null, description="should a thread in a moderated forum be activated?")
	 */
	public function patchThreadAction(int $threadId, ParamFetcher $paramFetcher): SymfonyResponse
	{
		if (!$this->forumPermissions->mayModerate($threadId)) {
			throw new HttpException(403);
		}

		$isSticky = $paramFetcher->get('isSticky');
		if (!is_null($isSticky)) {
			if ($isSticky === true) {
				$this->forumGateway->stickThread($threadId);
			} else {
				$this->forumGateway->unstickThread($threadId);
			}
		}
		$isActive = $paramFetcher->get('isActive');
		if ($isActive === true) {
			$this->forumTransactions->activateThread($threadId);
		}

		return $this->getThreadAction($threadId);
	}

	/**
	 * request email notifications for activities in at thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Post("forum/thread/{threadId}/follow/email", requirements={"threadId" = "\d+"})
	 */
	public function followThreadByEmailAction(int $threadId): SymfonyResponse
	{
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}
		$this->forumFollowerGateway->followThreadByEmail($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * request bell notifications for activities in a thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Post("forum/thread/{threadId}/follow/bell", requirements={"threadId" = "\d+"})
	 */
	public function followThreadByBellAction(int $threadId): SymfonyResponse
	{
		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$this->forumFollowerGateway->followThreadByBell($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * Remove email notifications for activities in a thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @Rest\Delete("forum/thread/{threadId}/follow/email", requirements={"threadId" = "\d+"})
	 */
	public function unfollowThreadByEmailAction(int $threadId): SymfonyResponse
	{
		$this->forumFollowerGateway->unfollowThreadByEmail($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * Remove bell notifications for activities in a thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @Rest\Delete("forum/thread/{threadId}/follow/bell", requirements={"threadId" = "\d+"})
	 */
	public function unfollowThreadByBellAction(int $threadId): SymfonyResponse
	{
		$this->forumFollowerGateway->unfollowThreadByBell($this->session->id(), $threadId);

		return $this->handleView($this->view([]));
	}

	/**
	 * Delete a forum post.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="404", description="Post does not exist")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Delete("forum/post/{postId}", requirements={"postId" = "\d+"})
	 */
	public function deletePostAction(int $postId): SymfonyResponse
	{
		$post = $this->forumGateway->getPost($postId);
		if (!$post) {
			throw new HttpException(404);
		}
		if (!$this->forumPermissions->mayDeletePost($post)) {
			throw new HttpException(403);
		}

		$this->forumGateway->deletePost($postId);

		return $this->handleView($this->view([]));
	}

	/**
	 * Deletes a forum thread.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Parameter(name="threadId", in="path", type="integer", description="ID of the thread that will be deleted")
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to delete that thread or thread is already active")
	 * @SWG\Response(response="404", description="Thread does not exist.")
	 * @Rest\Delete("forum/thread/{threadId}", requirements={"postId" = "\d+"})
	 */
	public function deleteThreadAction(int $threadId): SymfonyResponse
	{
		$thread = $this->forumGateway->getThread($threadId);
		if (!$thread) {
			throw new HttpException(404);
		}
		if (!$this->forumPermissions->mayDeleteThread($thread)) {
			throw new HttpException(403);
		}

		$this->forumGateway->deleteThread($threadId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Adds an emoji reaction to a post. An emoji is an arbitrary string but needs to be supported by the frontend.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Post("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function addReactionAction(int $postId, string $emoji): SymfonyResponse
	{
		$threadId = $this->forumGateway->getThreadForPost($postId);

		if (!$this->forumPermissions->mayAccessThread($threadId)) {
			throw new HttpException(403);
		}

		$this->forumTransactions->addReaction($this->session->id(), $postId, $emoji);

		return $this->handleView($this->view([]));
	}

	/**
	 * Remove an emoji reaction the logged in user has given from a post.
	 *
	 * @SWG\Tag(name="forum")
	 * @SWG\Response(response="200", description="success")
	 * @Rest\Delete("forum/post/{postId}/reaction/{emoji}", requirements={"postId" = "\d+", "emoji" = "\w+"})
	 */
	public function deleteReactionAction(int $postId, string $emoji): SymfonyResponse
	{
		$this->forumTransactions->removeReaction($this->session->id(), $postId, $emoji);

		return $this->handleView($this->view([]));
	}
}
