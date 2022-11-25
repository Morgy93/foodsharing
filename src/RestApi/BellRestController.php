<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function is_array;

class BellRestController extends AbstractFOSRestController
{
	private const NO_BELLS_UPDATED = 0;

	public function __construct(
		private readonly BellGateway $bellGateway,
		private readonly Session $session
	) {
	}

	/**
	 * Returns all bells for the current user.
	 *
	 * @OA\Tag(name="bells")
	 * @Rest\Get("bells")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="How many bells to return.")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="Offset for returned bells.")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function listBells(ParamFetcher $paramFetcher): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new UnauthorizedHttpException('');
		}

		$limit = $paramFetcher->get('limit');
		$offset = $paramFetcher->get('offset');
		$bells = $this->bellGateway->listBells($id, $limit, $offset);

		return $this->handleView($this->view($bells, Response::HTTP_OK));
	}

	/**
	 * Marks one or more bells as read.
	 *
	 * @OA\Tag(name="bells")
	 * @Rest\Patch("bells")
	 * @Rest\RequestParam(name="ids")
	 * @OA\Parameter(name="bellId", in="path", @OA\Schema(type="integer"), description="which bell to mark as read")
	 * @OA\Response(response=Response::HTTP_OK, description="At least one of the bells was successfully marked.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_BAD_REQUEST, description="If the list of IDs is empty or none of the bells could be marked.")
	 */
	public function markBellsAsReadAction(ParamFetcher $paramFetcher): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new UnauthorizedHttpException('');
		}

		$bellIds = $paramFetcher->get('ids');
		if (!is_array($bellIds) || empty($bellIds)) {
			throw new BadRequestHttpException();
		}

		$countBellUpdates = $this->bellGateway->setBellsAsSeen($bellIds, $id);

		if ($countBellUpdates === self::NO_BELLS_UPDATED) {
			return $this->handleView($this->view([], Response::HTTP_BAD_REQUEST));
		} else {
			return $this->handleView($this->view([
				'marked' => $countBellUpdates
			], Response::HTTP_OK));
		}
	}

	/**
	 * Deletes a bell.
	 *
	 * @OA\Tag(name="bells")
	 * @Rest\Delete("bells/{bellId}", requirements={"bellId" = "\d+"})
	 * @OA\Parameter(name="bellId", in="path", @OA\Schema(type="integer"), description="which bell to delete")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 * @OA\Response(response=Response::HTTP_NOT_FOUND, description="The user does not have a bell with that ID.")
	 */
	public function deleteBellAction(int $bellId): Response
	{
		$id = $this->session->id();
		if (!$id) {
			throw new UnauthorizedHttpException('');
		}

		$deleted = $this->bellGateway->delBellForFoodsaver($bellId, $id);

		return $this->handleView($this->view([], $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND));
	}
}
