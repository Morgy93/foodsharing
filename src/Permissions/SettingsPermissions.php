<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class SettingsPermissions
{
	private Session $session;

	public function __construct(
		Session $session
	) {
		$this->session = $session;
	}

	public function mayUseCalendarExport(): bool
	{
		return $this->session->may('fs');
	}

	public function mayUsePassportGeneration(): bool
	{
		return $this->session->may('fs') && $this->session->isVerified();
	}
}
