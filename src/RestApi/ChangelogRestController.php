<?php

namespace Foodsharing\RestApi;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Martincodes\YamlChangelogGenerator\YamlChangelogCreator;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Tag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Yaml\Yaml;

final class ChangelogRestController extends AbstractFOSRestController
{
    /**
     * Returns the changelog since 2023.
     *
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="500", description="Error")
     */
    #[Get('changelog/')]
    #[Tag('system')]
    public function getChangelogAction(): Response
    {
        $pathOfChangelogDirectory = __DIR__ . '/../../changelog/';

        try {
            $changelogConfiguration = Yaml::parseFile($pathOfChangelogDirectory . 'config.yaml');

            $changelogGenerator = new YamlChangelogCreator(
                $pathOfChangelogDirectory,
                ['release.yaml', 'readme.md'],
                'release.yaml',
                '-fs-release-'
            );

            return $this->handleView($this->view([
                'changelog' => $changelogGenerator->getChangelog(),
                'meta' => $changelogConfiguration,
            ]));
        } catch (\Exception $e) {
            throw new ServiceUnavailableHttpException();
        }
    }
}
