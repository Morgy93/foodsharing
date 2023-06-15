<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForChainList;
use Foodsharing\Modules\StoreChain\DTO\StoreChainForUpdate;
use Foodsharing\Modules\StoreChain\StoreChainGateway;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use Foodsharing\Modules\StoreChain\StoreChainTransactions;
use Foodsharing\Permissions\StoreChainPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
     * Creates a new store.
     * The name must not be empty. All other parameters are
     * optional. Returns the created store chain.
     *
     * @OA\Tag(name="chain")
     * @Rest\Post("chain")
     * @Rest\RequestParam(name="name", nullable=false)
     * @Rest\RequestParam(name="headquarters_zip", nullable=true, requirements="\d{5}")
     * @Rest\RequestParam(name="headquarters_city", nullable=true)
     * @Rest\RequestParam(name="status", nullable=false)
     * @Rest\RequestParam(name="allow_press", nullable=false, default=false)
     * @Rest\RequestParam(name="forum_thread", nullable=true, requirements="\d+")
     * @Rest\RequestParam(name="notes", nullable=true)
     * @Rest\RequestParam(name="common_store_information", nullable=true)
     * @Rest\RequestParam(name="kams", nullable=true)
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     */
    public function createChainAction(ParamFetcher $paramFetcher): Response
    {
        if (!$this->session->mayRole()) {
            throw new UnauthorizedHttpException(self::NOT_LOGGED_IN);
        }
        if (!$this->permissions->mayCreateChain()) {
            throw new AccessDeniedHttpException();
        }

        $params = $this->validateChainParameters($paramFetcher);

        $id = $this->gateway->addStoreChain($params);

        return $this->handleView($this->view($this->gateway->getStoreChains($id)[0]));
    }

    /**
     * Updates a store.
     *
     * @OA\Tag(name="chain")
     * @Rest\Patch("chain/{chainId}", requirements={"chainId" = "\d+"})
     * @Rest\RequestParam(name="name", nullable=false)
     * @Rest\RequestParam(name="headquarters_zip", nullable=true, requirements="\d{5}")
     * @Rest\RequestParam(name="headquarters_city", nullable=true)
     * @Rest\RequestParam(name="status", nullable=false)
     * @Rest\RequestParam(name="allow_press", nullable=false, default=false)
     * @Rest\RequestParam(name="forum_thread", nullable=true, requirements="\d+")
     * @Rest\RequestParam(name="notes", nullable=true)
     * @Rest\RequestParam(name="common_store_information", nullable=true)
     * @Rest\RequestParam(name="kams", nullable=true)
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="401", description="Not logged in")
     * @OA\Response(response="403", description="Insufficient permissions")
     * @OA\Response(response="404", description="Chain does not exist")
     */
    public function updateChainAction($chainId, ParamFetcher $paramFetcher): Response
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

        $params = $this->validateChainParameters($paramFetcher);

        $updateKams = $this->permissions->mayEditKams($chainId);
        $this->transactions->updateStoreChain($params, $chainId, $updateKams);

        return $this->handleView($this->view($this->gateway->getStoreChains($chainId)[0]));
    }

    private function validateChainParameters(ParamFetcher $paramFetcher): StoreChainForUpdate
    {
        $name = trim(strip_tags($paramFetcher->get('name')));
        if (empty($name)) {
            throw new BadRequestHttpException('name must not be empty');
        }

        $headquarters_city = trim(strip_tags($paramFetcher->get('headquarters_city')));
        if (empty($headquarters_city)) {
            $headquarters_city = null;
        } elseif (strlen($headquarters_city) > 50) {
            throw new BadRequestHttpException('headquarters_city must not be longer than 50 chars');
        }

        $status = StoreChainStatus::tryFrom($paramFetcher->get('status'));
        if (!$status instanceof StoreChainStatus) {
            throw new BadRequestHttpException('status must be a valid status id');
        }

        $allow_press = (bool)$paramFetcher->get('allow_press');

        $forum_thread = $paramFetcher->get('forum_thread');
        if (!is_null($forum_thread)) {
            $forum_thread = (int)$forum_thread;
        }

        $notes = trim(strip_tags($paramFetcher->get('notes')));
        if (empty($notes)) {
            $notes = null;
        } elseif (mb_strlen($notes) > 200) {
            throw new BadRequestHttpException('notes must not be longer than 200 chars');
        }

        $kams = $paramFetcher->get('kams');
        if (empty($kams)) {
            $kams = [];
        } elseif (!is_array($kams)) {
            throw new BadRequestHttpException('kams must be an array of user ids');
        } else {
            foreach ($kams as $id) {
                if (!is_int($id) || $id < 0) {
                    throw new BadRequestHttpException('kams must be an array of user ids');
                }
            }
        }

        $chain = new StoreChainForUpdate();
        $chain->name = $name;
        $chain->status = $status->value;
        $chain->allow_press = $allow_press;
        $chain->headquarters_zip = $paramFetcher->get('headquarters_zip');
        $chain->headquarters_city = $headquarters_city;
        $chain->forum_thread = $forum_thread;
        $chain->notes = $notes;
        $chain->common_store_information = $paramFetcher->get('common_store_information');
        $chain->kams = $kams;

        return $chain;
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
}
