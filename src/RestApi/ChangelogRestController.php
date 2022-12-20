<?php

namespace Foodsharing\RestApi;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Martincodes\YamlChangelogGenerator\YamlChangelogCreator;
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
		$changelogGenerator = new YamlChangelogCreator(
			__DIR__ . '/../../changelog/',
			['release.yaml', 'readme.md'],
			'release.yaml',
			'-fs-release-'
		);

		return $this->handleView($this->view($changelogGenerator->getChangelog()));
	}
}
