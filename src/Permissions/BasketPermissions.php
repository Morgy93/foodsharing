<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class BasketPermissions
{
	private Session $session;

	public function __construct(
		Session $session
	) {
		$this->session = $session;
	}

	public function mayRequest(int $basket_fs_id): bool
	{
		if ($basket_fs_id != $this->session->id()) {
			return true;
		}

		return false;
	}

	public function mayAdd(): bool
	{
		return $this->session->may();
	}

	public function mayEdit(int $basket_fs_id): bool
	{
		if ($basket_fs_id == $this->session->id()) {
			return true;
		}

		return false;
	}

	public function mayDelete(int $basket_fs_id): bool
	{
		if ($this->session->isOrgaTeam()) {
			return true;
		}

		if ($basket_fs_id == $this->session->id()) {
			return true;
		}

		return false;
	}
}
