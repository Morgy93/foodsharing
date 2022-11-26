<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\UserOptionType;
use Foodsharing\Modules\Settings\SettingsGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LocaleRestController extends AbstractFOSRestController
{
	public function __construct(
		private readonly SettingsGateway $settingsGateway,
		private readonly Session $session
	) {
	}

	/**
	 * Returns the locale setting for the current session.
	 *
	 * @OA\Tag(name="locale")
	 * @Rest\Get("locale")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function getLocaleAction(): Response
	{
		if (!$this->session->mayRole()) {
			throw new UnauthorizedHttpException('');
		}

		$locale = $this->session->getLocale();

		return $this->handleView($this->view(['locale' => $locale], Response::HTTP_OK));
	}

	/**
	 * Sets the locale for the current session.
	 *
	 * @OA\Tag(name="locale")
	 * @Rest\Post("locale")
	 * @Rest\RequestParam(name="locale")
	 * @OA\Response(response=Response::HTTP_OK, description="Success.")
	 * @OA\Response(response=Response::HTTP_UNAUTHORIZED, description="Not logged in.")
	 */
	public function setLocaleAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->mayRole()) {
			throw new UnauthorizedHttpException('');
		}

		$locale = $paramFetcher->get('locale');
		if (empty($locale)) {
			$locale = Session::DEFAULT_LOCALE;
		}

		$this->session->set('locale', $locale);
		$this->settingsGateway->setUserOption($this->session->id(), UserOptionType::LOCALE, $locale);

		return $this->getLocaleAction();
	}
}
