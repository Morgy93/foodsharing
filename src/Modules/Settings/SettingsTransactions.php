<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Mails\MailsGateway;
use Foodsharing\RestApi\Models\Settings\EmailChangeRequest;
use Foodsharing\Utility\EmailHelper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingsTransactions
{
    public function __construct(
        private readonly FoodsaverGateway $foodsaverGateway,
        private readonly LoginGateway $loginGateway,
        private readonly SettingsGateway $settingsGateway,
        private readonly MailsGateway $mailsGateway,
        private readonly EmailHelper $emailHelper,
        private readonly TranslatorInterface $translator,
        private readonly Session $session,
    ) {
    }

    /**
     * Stores the request for changing the user's email address in the database and sends confirmation emails to the
     * old and the new address. After this, the change still needs to be confirmed by the link in the confirmation
     * email.
     *
     * @param EmailChangeRequest $request the request containing the new email address and the user's password
     *
     * @throws AccessDeniedHttpException if the password is wrong
     * @throws BadRequestHttpException if the new email address is not valid
     */
    public function requestEmailChange(EmailChangeRequest $request): void
    {
        // check that the password is correct
        $currentEmail = $this->foodsaverGateway->getEmailAddress($this->session->id());
        if (!$this->loginGateway->checkClient($currentEmail, $request->password)) {
            throw new AccessDeniedHttpException();
        }

        // check that the new address is valid and not in use
        if (!$this->emailHelper->validEmail($request->email)
            || $this->emailHelper->isFoodsharingEmailAddress($request->email)
            || $this->foodsaverGateway->emailExists($request->email)) {
            throw new BadRequestHttpException();
        }

        // store a random token in the database
        $token = bin2hex(random_bytes(16));
        $this->settingsGateway->addNewMail($this->session->id(), $request->email, $token);

        // send a notification about the change to the old address
        $user = $this->foodsaverGateway->getFoodsaverBasics($this->session->id());
        $this->mailsGateway->removeBounceForMail($currentEmail);
        $this->emailHelper->tplMail('user/change_email_notification', $currentEmail, [
            'anrede' => $this->translator->trans('salutation.' . $user['geschlecht']),
            'name' => $user['name'],
            'address' => $request->email,
            'link' => BASE_URL . '/content?sub=contact'
        ], false, true);

        // send a confirmation email to the new address
        $this->mailsGateway->removeBounceForMail($request->email);
        $this->emailHelper->tplMail('user/change_email', $request->email, [
            'anrede' => $this->translator->trans('salutation.' . $user['geschlecht']),
            'name' => $user['name'],
            'link' => BASE_URL . '/?page=settings&sub=general&newmail=' . $token
        ], false, true);
    }
}
