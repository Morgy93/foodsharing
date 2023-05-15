<?php

namespace Foodsharing\Modules\Legal;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\View;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegalControl extends Control
{
    private LegalGateway $gateway;
    private FormFactoryInterface $formFactory;

    public function __construct(LegalGateway $gateway, View $view)
    {
        $this->view = $view;
        $this->gateway = $gateway;

        parent::__construct();
    }

    /**
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function index(Request $request, Response $response): void
    {
        $privacyPolicyDate = $this->gateway->getPpVersion();
        $privacyNoticeDate = $this->gateway->getPnVersion();

        if ($this->session->id()) {
            $privacyNoticeNeccessary = $this->session->user('rolle') >= 2;
            $privacyPolicyAcknowledged = $this->session->user('privacy_policy_accepted_date') == $privacyPolicyDate;
            $privacyNoticeAcknowledged = $this->session->user('privacy_notice_accepted_date') == $privacyNoticeDate;
            $data = new LegalData($privacyPolicyAcknowledged, $privacyNoticeNeccessary ? $privacyNoticeAcknowledged : true);
        } else {
            $privacyNoticeNeccessary = false;
            $data = new LegalData(false, true);
        }
        $form = $this->formFactory->create(LegalForm::class, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gateway->agreeToPp($this->session->id(), $privacyPolicyDate);
            if ($privacyNoticeNeccessary) {
                if ($data->isPrivacyNoticeAcknowledged()) {
                    $this->gateway->agreeToPn($this->session->id(), $privacyNoticeDate);
                    $this->emailHelper->tplMail('user/privacy_notice', $this->session->user('email'), ['vorname' => $this->session->user('name')]);
                } else {
                    $this->gateway->downgradeToFoodsaver($this->session->id());
                }
            }

            try {
                $this->session->refreshFromDatabase();
                $this->routeHelper->goSelfAndExit();
            } catch (\Exception $e) {
                $this->routeHelper->goPageAndExit('logout');
            }
        }

        $response->setContent($this->render('pages/Legal/page.twig', [
            'privacyPolicyContent' => $this->gateway->getPp(),
            'privacyNoticeContent' => $this->gateway->getPn(),
            'showPrivacyNotice' => $privacyNoticeNeccessary,
            'loggedIn' => $this->session->mayRole(),
            'form' => $form->createView()]));
    }
}
