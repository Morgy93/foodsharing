<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;
use Foodsharing\Modules\Settings\SettingsGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SettingsRestController extends AbstractFOSRestController
{
    private SettingsGateway $settingsGateway;
    private Session $session;

    public function __construct(
        SettingsGateway $settingsGateway,
        Session $session
    ) {
        $this->settingsGateway = $settingsGateway;
        $this->session = $session;
    }

    /**
     * Sets the current user's sleep mode. For the temporary mode, both 'from' and 'to' need to be given. Both are
     * assumed to be in the format 'd.m.Y'. For other modes the two fields will be ignored. Optionally, a message
     * can be added.
     *
     * @OA\Tag(name="user")
     * @OA\Response(response="200", description="Success")
     * @OA\Response(response="400", description="Invalid mode or parameters")
     * @OA\Response(response="401", description="Not logged in")
     * @Rest\Patch("user/sleepmode")
     * @Rest\RequestParam(name="mode", nullable=false, allowBlank=false, requirements="\d+", description="sleep mode as an integer")
     * @Rest\RequestParam(name="from", nullable=true, description="start date of the sleep interval")
     * @Rest\RequestParam(name="to", nullable=true, description="end date of the sleep interval")
     * @Rest\RequestParam(name="message", nullable=true, description="optional sleep mode message")
     */
    public function setSleepStatusAction(ParamFetcher $paramFetcher): Response
    {
        $userId = $this->session->id();
        if (!$userId) {
            throw new UnauthorizedHttpException('');
        }

        $mode = intval($paramFetcher->get('mode'));
        if (!SleepStatus::isValid($mode)) {
            throw new BadRequestHttpException('invalid sleep status');
        }

        // parse from and to date, if they are needed
        $from = null;
        $to = null;
        if ($mode == SleepStatus::TEMP) {
            try {
                $from = Carbon::createFromFormat('d.m.Y', $paramFetcher->get('from'));
                $to = Carbon::createFromFormat('d.m.Y', $paramFetcher->get('to'));
            } catch (Exception $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            if ($from == null || $to == null) {
                throw new BadRequestHttpException('invalid dates');
            }
        }

        $message = $paramFetcher->get('message');
        $this->settingsGateway->updateSleepMode($this->session->id(), $mode, $from, $to, $message);

        return $this->handleView($this->view([], 204));
    }
}
