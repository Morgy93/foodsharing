<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Services\SearchIndexGenerator;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SearchRestController extends AbstractFOSRestController
{
	private $session;
	private $searchService;

	public function __construct(Session $session, SearchIndexGenerator $searchService)
	{
		$this->session = $session;
		$this->searchService = $searchService;
	}

	/**
	 * @Rest\Get("search/legacyindex")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getSearchLegacyIndexAction()
	{
		if (!$this->session->may()) {
			throw new HttpException(403);
		}
		$data = $this->searchService->generateIndex($this->session->id());

		$view = $this->view($data, 200);

		return $this->handleView($view);
	}
}
