<?php

namespace Foodsharing\RestApi;

use Codeception\Util\HttpCode;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\Pagination;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\Store\DTO\MinimalStoreIdentifier;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\StoreChain\DTO\PatchStoreChain;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForChainList;
use Foodsharing\Modules\StoreChain\StoreChainGateway;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use Foodsharing\Modules\StoreChain\StoreChainTransactionException;
use Foodsharing\Modules\StoreChain\StoreChainTransactions;
use Foodsharing\Permissions\StoreChainPermissions;
use Foodsharing\RestApi\Models\StoreChain\CreateStoreChainModel;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
        private readonly StoreGateway $storeGateway,
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
     * @Rest\QueryParam(name="pageSize" , description="Count of chains on page", requirements="\d+", default=0, strict=true)
     * @Rest\QueryParam(name="offset" , description="Offset of items", requirements="\d+", default=0, strict=true)
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
    public function getStoreChainsAction(ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->maySeeChainList()) {
            throw new AccessDeniedHttpException();
        }

        $pagination = new Pagination();
        $pagination->pageSize = $paramFetcher->get('pageSize');
        $pagination->offset = $paramFetcher->get('offset');

        return $this->handleView($this->view($this->gateway->getStoreChains(null, $pagination), 200));
    }

    /**
     * Returns a specific store chain.
     *
     * @OA\Tag(name="chain")
     * @Rest\Get("chains/{chainId}", requirements={"chainId" = "\d+"})
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
     * @Rest\Post("chains")
     * @ParamConverter("storeModel", converter="fos_rest.request_body")
     * @OA\RequestBody(@Model(type=CreateStoreChainModel::class))
     * @OA\Response(
     * 		response="201",
     * 		description="Success.",
     *      @Model(type=StoreChainForChainList::class)
     * )
     * @OA\Response(response="400", description="Bad content")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function createChainAction(CreateStoreChainModel $storeModel, ConstraintViolationListInterface $validationErrors): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->mayCreateChain()) {
            throw new AccessDeniedHttpException();
        }

        $this->throwBadRequestExceptionOnError($validationErrors);
        try {
            $id = $this->transactions->addStoreChain($storeModel->toCreateStore());

            return $this->handleView($this->view($this->gateway->getStoreChains($id)[0], HttpCode::CREATED));
        } catch (StoreChainTransactionException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    /**
     * Updates a store.
     *
     * @OA\Tag(name="chain")
     * @Rest\Patch("chains/{chainId}", requirements={"chainId" = "\d+"})
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

        $changed = false;
        $params = $this->gateway->getStoreChains($chainId)[0]->chain;
        $params->id = $chainId;
        if (!empty($storeModel->name)) {
            $params->name = $storeModel->name;
            if (empty(trim(strip_tags($params->name)))) {
                throw new BadRequestHttpException('name must not be empty');
            }
            $changed = true;
        }

        if (!empty($storeModel->status)) {
            $status = StoreChainStatus::tryFrom($storeModel->status);
            if (!$status instanceof StoreChainStatus) {
                throw new BadRequestHttpException('status must be a valid status id');
            }
            $params->status = $status;
            $changed = true;
        }
        if (!empty($storeModel->headquartersZip)) {
            $params->headquartersZip = $storeModel->headquartersZip;
            $changed = true;
        }
        if (!empty($storeModel->headquartersCity)) {
            $params->headquartersCity = $storeModel->headquartersCity;
            $changed = true;
        }
        if (!empty($storeModel->allowPress)) {
            $params->allowPress = $storeModel->allowPress;
            $changed = true;
        }
        if (!empty($storeModel->forumThread)) {
            $params->forumThread = $storeModel->forumThread;
            $changed = true;
        } else {
            throw new BadRequestHttpException('forumThread is required');
        }
        if (!empty($storeModel->notes)) {
            $params->notes = $storeModel->notes;
            $changed = true;
        }
        if (!empty($storeModel->commonStoreInformation)) {
            $params->commonStoreInformation = $storeModel->commonStoreInformation;
            $changed = true;
        }
        if (!empty($storeModel->kams)) {
            $params->kams = array_map(function ($kam) {
                $obj = new FoodsaverForAvatar();
                $obj->id = $kam;

                return $obj;
            }, $storeModel->kams);
            $changed = true;
        }

        if ($changed) {
            $updateKams = $this->permissions->mayEditKams($chainId);
            $this->transactions->updateStoreChain($params, $updateKams);

            return $this->handleView($this->view($this->gateway->getStoreChains($chainId)[0]));
        } else {
            throw new BadRequestException('No information changed.');
        }
    }

    /**
     * Returns the list of stores that are part of a given chain.
     *
     * @Rest\QueryParam(name="pageSize" , description="Count of chains on page", requirements="\d+", default=0, strict=true)
     * @Rest\QueryParam(name="offset" , description="Offset of items", requirements="\d+", default=0, strict=true)
     * @OA\Tag(name="chain")
     * @Rest\Get("chains/{chainId}/stores", requirements={"chainId" = "\d+"})
     * @OA\Response(
     * 		response="200",
     * 		description="Success.",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=MinimalStoreIdentifier::class))
     *      )
     * )
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function getChainStoresAction(int $chainId, ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->maySeeChainStores($chainId)) {
            throw new AccessDeniedHttpException();
        }

        $pagination = new Pagination();
        $pagination->pageSize = $paramFetcher->get('pageSize');
        $pagination->offset = $paramFetcher->get('offset');

        return $this->handleView($this->view($this->storeGateway->findAllStoresOfStoreChain($chainId, $pagination), 200));
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
