<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Content\DTO\Content;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ContentRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly ContentGateway $contentGateway,
        private readonly Session $session
    ) {
    }

    /**
     * Returns the content entry for a specific id.
     *
     * @OA\Response(response="200", description="Success", @Model(type=Content::class))
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="404", description="Content id does not exist")
     * @OA\Tag(name="content")
     * @Rest\Get("content/{contentId}", requirements={"contentId" = "\d+", "status" = "[0-1]"})
     */
    public function getContentAction(int $contentId): Response
    {
        if (!$this->session->id()) {
            throw new UnauthorizedHttpException('');
        }

        $content = $this->contentGateway->getContent($contentId);
        if ($content == null) {
            throw new NotFoundHttpException('content id does not exist');
        }

        return $this->handleView($this->view($content, 200));
    }
}
