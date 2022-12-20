<?php

namespace Foodsharing\RestApi;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\Response;

class ChangelogRestController extends AbstractFOSRestController
{
	/**
	 * Returns the changelog since 2023
	 * @OA\Response(response="200", description="Success")
	 */
	#[Get('changelog/')]
	#[Tag('system')]
	public function getChangelogAction(): Response
	{
		return $this->handleView($this->view([]));
	}
}
