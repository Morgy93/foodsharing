<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\StoreChain\DTO\PatchStoreChain;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForChainList;
use Foodsharing\Modules\StoreChain\StoreChainGateway;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use Foodsharing\Modules\StoreChain\StoreChainTransactions;
use Foodsharing\Permissions\StoreChainPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class StoreChainRestController extends AbstractFOSRestController
{
    // literal constants
    private const NOT_LOGGED_IN = 'not logged in';

    public function __construct(
        private readonly Session $session,
        private readonly StoreChainGateway $gateway,
        private readonly StoreChainTransactions $transactions,
        private readonly StoreChainPermissions $permissions
    ) {
    }

    /**
     * Returns the list of store chains.
     *
     * @OA\Tag(name="chain")
     * @Rest\Get("chains")
     * @OA\Response(
     * 		response="200",
     * 		description="Success.",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=StoreChainForChainList::class))
     *      )
     * )
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function getStoreChainsAction(): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->maySeeChainList()) {
            throw new AccessDeniedHttpException();
        }

        return $this->handleView($this->view($this->gateway->getStoreChains(), 200));
    }

    /**
     * Returns a specific store chain.
     *
     * @OA\Tag(name="chain")
     * @Rest\Get("chain/{chainId}", requirements={"chainId" = "\d+"})
     * @OA\Response(
     * 		response="200",
     * 		description="Success.",
     *      @Model(type=StoreChainForChainList::class)
     * )
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function getStoreChainAction($chainId): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->maySeeChainList()) {
            throw new AccessDeniedHttpException();
        }

        return $this->handleView($this->view($this->gateway->getStoreChains($chainId), 200));
    }

    /**
     * Creates a new store.
     * The name must not be empty. All other parameters are
     * optional. Returns the created store chain.
     *
     * @OA\Tag(name="chain")
     * @Rest\Post("chain")
     * @ParamConverter("storeModel", converter="fos_rest.request_body")
     * @OA\RequestBody(@Model(type=PatchStoreChain::class))
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function createChainAction(StoreChain $storeModel, ConstraintViolationListInterface $validationErrors): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->mayCreateChain()) {
            throw new AccessDeniedHttpException();
        }

        $this->throwBadRequestExceptionOnError($validationErrors);

        $id = $this->gateway->addStoreChain($storeModel);

        return $this->handleView($this->view($this->gateway->getStoreChains($id)[0]));
    }

    /**
     * Updates a store.
     *
     * @OA\Tag(name="chain")
     * @Rest\Patch("chain/{chainId}", requirements={"chainId" = "\d+"})
     * @OA\RequestBody(@Model(type=PatchStoreChain::class))
     * @ParamConverter("storeModel", converter="fos_rest.request_body")
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="404", description="Chain does not exist")
     */
    public function updateChainAction($chainId, PatchStoreChain $storeModel, ConstraintViolationListInterface $validationErrors): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->gateway->chainExists($chainId)) {
            throw new NotFoundHttpException('chain does not exist');
        }
        if (!$this->permissions->mayEditChain($chainId)) {
            throw new AccessDeniedHttpException();
        }

        $this->throwBadRequestExceptionOnError($validationErrors);
        $params = $this->gateway->getStoreChains($chainId)[0]->chain;
        $params->id = $chainId;
        if (!empty($storeModel->name)) {
            $params->name = $storeModel->name;
            if (empty(trim(strip_tags($params->name)))) {
                throw new BadRequestHttpException('name must not be empty');
            }
        }

        if (!empty($storeModel->status)) {
            $status = StoreChainStatus::tryFrom($storeModel->status);
            if (!$status instanceof StoreChainStatus) {
                throw new BadRequestHttpException('status must be a valid status id');
            }
            $params->status = $status->value;
        }
        if (!empty($storeModel->headquarters_zip)) {
            $params->headquarters_zip = $storeModel->headquarters_zip;
        }
        if (!empty($storeModel->headquarters_city)) {
            $params->headquarters_city = $storeModel->headquarters_city;
        }
        if (!empty($storeModel->allow_press)) {
            $params->allow_press = $storeModel->allow_press;
        }
        if (!empty($storeModel->forum_thread)) {
            $params->forum_thread = $storeModel->forum_thread;
        }
        if (!empty($storeModel->notes)) {
            $params->notes = $storeModel->notes;
        }
        if (!empty($storeModel->common_store_information)) {
            $params->common_store_information = $storeModel->common_store_information;
        }
        if (!empty($storeModel->kams)) {
            $params->kams = $storeModel->kams;
        }

        $updateKams = $this->permissions->mayEditKams($chainId);
        $this->transactions->updateStoreChain($params, $updateKams);

        return $this->handleView($this->view($this->gateway->getStoreChains($chainId)[0]));
    }


    /**
     * Returns the list of stores that are part of a given chain.
     *
     * @OA\Tag(name="chain")
     * @Rest\Get("chain/{chainId}/stores", requirements={"chainId" = "\d+"})
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function getChainStoresAction($chainId): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->maySeeChainStores($chainId)) {
            throw new AccessDeniedHttpException();
        }

        return $this->handleView($this->view($this->gateway->getChainStores($chainId), 200));
    }

    /**
     * Check if a Constraint violation is found and if it exist it throws an BadRequestExeption.
     *
     * @param ConstraintViolationListInterface $errors Validation result
     *
     * @throws BadRequestHttpException if violation is detected
     */
    private function throwBadRequestExceptionOnError(ConstraintViolationListInterface $errors): void
    {
        if ($errors->count() > 0) {
            $firstError = $errors->get(0);
            $relevantErrorContent = ['field' => $firstError->getPropertyPath(), 'message' => $firstError->getMessage()];
            throw new BadRequestHttpException(json_encode($relevantErrorContent));
        }
    }
}
