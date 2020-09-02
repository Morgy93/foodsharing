<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

final class QuizPermissions
{
	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayEditQuiz(): bool
	{
		return $this->session->may('orga') || $this->session->isAdminFor(RegionIDs::QUIZ_AND_REGISTRATION_WORK_GROUP);
	}
}
