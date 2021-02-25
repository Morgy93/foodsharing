<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Region\RegionGateway;

final class RegionPermissions
{
	private RegionGateway $regionGateway;
	private Session $session;

	public function __construct(RegionGateway $regionGateway, Session $session)
	{
		$this->regionGateway = $regionGateway;
		$this->session = $session;
	}

	public function mayJoinRegion(int $regionId): bool
	{
		$type = $this->regionGateway->getType($regionId);

		return $this->session->may('fs') && Type::isAccessibleRegion($type);
	}

	public function mayAdministrateRegions(): bool
	{
		return $this->session->may('orga');
	}

	public function mayAdministrateRestrictedWorkgroupFunctions(int $wgfunction): bool
	{
		if (WorkgroupFunction::isRestrictedWorkgroupFunction($wgfunction))
		{
			return $this->session->may('orga') && $this->session->isAdminFor(RegionIDs::CREATING_WORK_GROUPS_WORK_GROUP);
		}
	}

	public function mayAccessStatisticCountry(): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return false;
	}

	public function mayHandleFoodsaverRegionMenu(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->isAmbassadorForRegion([$regionId], false, false);
	}

	public function hasConference(int $regionType): bool
	{
		return in_array($regionType, [Type::COUNTRY, Type::FEDERAL_STATE, Type::CITY, TYPE::WORKING_GROUP, Type::PART_OF_TOWN, Type::DISTRICT, Type::REGION, Type::BIG_CITY]);
	}

	public function mayDeleteFoodsaverFromRegion(int $regionId): bool
	{
		return $this->mayHandleFoodsaverRegionMenu($regionId);
	}
}
