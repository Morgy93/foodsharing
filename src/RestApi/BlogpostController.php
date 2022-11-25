<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Blog\BlogGateway;
use Foodsharing\Permissions\BlogPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function boolval;

class BlogpostController extends AbstractFOSRestController
{
	public function __construct(
		private readonly BlogGateway $blogGateway,
		private readonly BlogPermissions $blogPermissions,
		private readonly Session $session
	) {
	}

	/**
	 * Publishes (isPublished=1) or depublishes (isPublished=0) a blogpost.
	 *
	 * @OA\Tag(name="blog")
	 * @Rest\Patch("blog/{blogId}", requirements={"blogId" = "\d+"})
	 * @Rest\RequestParam(name="isPublished", requirements="(0|1)")
	 * @OA\Parameter(name="blogId", in="path", @OA\Schema(type="integer"), description="which post to (de)publish")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions to manage this blogpost.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="Blogpost not found.")
	 */
	public function setBlogpostPublishedAction(int $blogId, ParamFetcher $paramFetcher): Response
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new UnauthorizedHttpException('', 'Not logged in.');
		}

		$author = $this->blogGateway->getAuthorOfPost($blogId);
		if ($author === false) {
			throw new NotFoundHttpException('Blogpost not found.');
		}
		if (!$this->blogPermissions->mayPublish($blogId)) {
			throw new AccessDeniedHttpException();
		}

		$newPublishedState = boolval($paramFetcher->get('isPublished'));
		$this->blogGateway->setPublished($blogId, $newPublishedState);

		return $this->handleView($this->view([], Response::HTTP_OK));
	}

	/**
	 * Removes one blogpost from the database.
	 *
	 * @OA\Tag(name="blog")
	 * @Rest\Delete("blog/{blogId}", requirements={"blogId" = "\d+"})
	 * @OA\Parameter(name="blogId", in="path", @OA\Schema(type="integer"), description="which post to delete")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_FORBIDDEN, description="Insufficient permissions to remove this blogpost.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="Blogpost not found.")
	 */
	public function removeBlogpostAction(int $blogId): Response
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new UnauthorizedHttpException('', 'Not logged in.');
		}

		$author = $this->blogGateway->getAuthorOfPost($blogId);
		if ($author === false) {
			throw new NotFoundHttpException('Blogpost not found.');
		}
		if (!$this->blogPermissions->mayDelete($blogId)) {
			throw new AccessDeniedHttpException();
		}

		$this->blogGateway->del_blog_entry($blogId);

		return $this->handleView($this->view([], Response::HTTP_OK));
	}
}
