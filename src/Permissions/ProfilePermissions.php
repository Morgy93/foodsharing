<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;

class ProfilePermissions
{
    private Session $session;
    private RegionGateway $regionGateway;
    private FoodsaverGateway $foodsaverGateway;

    public function __construct(Session $session, RegionGateway $regionGateway, FoodsaverGateway $foodsaverGateway)
    {
        $this->session = $session;
        $this->regionGateway = $regionGateway;
        $this->foodsaverGateway = $foodsaverGateway;
    }

    public function mayAdministrateUserProfile(int $userId, ?int $regionId = null): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        if (!$this->session->isAmbassador()) {
            return false;
        }

        if ($regionId !== null && $this->session->isAdminFor($regionId)) {
            return true;
        }

        $regionIds = $this->regionGateway->getFsRegionIds($userId);

        return $this->session->isAmbassadorForRegion($regionIds, false, true);
    }

    public function hasApplicant(int $userId): bool
    {
        return $this->session->mayRole(Role::STORE_MANAGER) && $this->foodsaverGateway->isApplicant($userId, $this->session->id());
    }

    public function mayEditUserProfile(int $userId): bool
    {
        return $this->session->id() === $userId || $this->mayAdministrateUserProfile($userId);
    }

    public function mayCancelSlotsFromProfile(int $userId): bool
    {
        return $this->session->id() != $userId && $this->mayAdministrateUserProfile($userId);
    }

    public function mayChangeUserVerification(int $userId): bool
    {
        return $this->mayAdministrateUserProfile($userId);
    }

    public function maySeeHistory(int $fsId): bool
    {
        return $this->mayAdministrateUserProfile($fsId);
    }

    public function maySeeUserNotes(int $userId): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function maySeePickups(int $fsId): bool
    {
        if (!$this->session->mayRole(Role::FOODSAVER)) {
            return false;
        }

        return $this->maySeeAllPickups($fsId) || $this->mayAdministrateUserProfile($fsId);
    }

    public function maySeeAllPickups(int $fsId): bool
    {
        return $this->session->id() == $fsId;
    }

    public function maySeeStores(int $fsId): bool
    {
        if (!$this->session->mayRole(Role::FOODSAVER)) {
            return false;
        }

        return
            $this->session->id() == $fsId ||
            $this->hasApplicant($fsId) ||
            $this->mayAdministrateUserProfile($fsId);
    }

    public function maySeeCommitmentsStat(int $fsId): bool
    {
        if ($this->session->id() == $fsId) {
            return true;
        }

        if ($this->mayAdministrateUserProfile($fsId)) {
            return true;
        }

        if ($this->session->mayRole(Role::STORE_MANAGER)) {
            if ($this->foodsaverGateway->getCountCommonStores($this->session->id(), $fsId) > 0) {
                return true;
            }
            $getFsID = $this->foodsaverGateway->getFoodsaverBasics($fsId);
            if ($getFsID['bezirk_id'] == $this->session->getCurrentRegionId()) {
                return true;
            }
        }

        return false;
    }

    public function maySeeEmailAddress(int $fsId): bool
    {
        if ($this->session->mayRole(Role::ORGA)) {
            return true;
        }

        return $this->session->id() == $fsId;
    }

    public function maySeePrivateEmail(int $userId): bool
    {
        return $this->session->id() === $userId || $this->session->mayRole(Role::ORGA);
    }

    public function maySeelastActivity(int $userId): bool
    {
        return $this->session->mayRole(Role::ORGA);
    }

    public function maySeeRegistrationDate(int $userId): bool
    {
        return $this->session->id() === $userId || $this->session->mayRole(Role::ORGA);
    }

    public function mayDeleteUser(int $userId): bool
    {
        return $this->session->id() == $userId || $this->session->mayRole(Role::ORGA);
    }

    public function maySeeBounceWarning(int $userId): bool
    {
        return $this->session->id() == $userId || $this->mayRemoveFromBounceList($userId);
    }

    public function mayDeleteBanana(int $recipientId): bool
    {
        // users , orga and admin of IT-Support can delete bananas that were given to them by someone else
        return $this->session->isAdminFor(RegionIDs::IT_SUPPORT_GROUP) || $this->session->id() == $recipientId;
    }

    public function mayRemoveFromBounceList(int $userId): bool
    {
        return $this->session->id() == $userId || $this->session->mayRole(Role::ORGA) || $this->session->isAdminFor(RegionIDs::IT_SUPPORT_GROUP);
    }
}
