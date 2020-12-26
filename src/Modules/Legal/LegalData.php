<?php

namespace Foodsharing\Modules\Legal;

use Symfony\Component\Validator\Constraints as Assert;

class LegalData
{
	/**
	 * @Assert\IsTrue(message="legal.must_accept_pp")
	 */
	private bool $privacyPolicyAcknowledged;
	private bool $privacyNoticeAcknowledged;

	public function __construct(bool $privacyPolicyAcknowledged = false, bool $privacyNoticeAcknowledged = false)
	{
		$this->privacyPolicyAcknowledged = $privacyPolicyAcknowledged;
		$this->privacyNoticeAcknowledged = $privacyNoticeAcknowledged;
	}

	public function isPrivacyNoticeAcknowledged(): bool
	{
		return $this->privacyNoticeAcknowledged;
	}
}
