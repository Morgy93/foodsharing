<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use Foodsharing\Modules\Core\DBConstants\Region\RegionOptionType;
use Foodsharing\Modules\Region\RegionGateway;

class PickupRules
{
    public function __construct(
        private readonly PickupGateway $pickupGateway,
        private readonly StoreGateway $storeGateway,
        private readonly RegionGateway $regionGateway
    ) {
    }

    /**
     * @param int $storeId Id of Store
     * @param Carbon $pickupDate Date of Pickup
     * @param int $fsId foodsaver ID
     *
     * @return bool true or false - true if no rule is violated, false if a rule is violated
     */
    public function observesPickupRules(int $storeId, Carbon $pickupDate, int $fsId): bool
    {
        $response['result'] = true; // default response, rule is passed
        $doesStoreUseRegionPickupRule = (bool)$this->storeGateway->getUseRegionPickupRule($storeId);

        if ($doesStoreUseRegionPickupRule) {
            $regionId = $this->storeGateway->getStoreRegionId($storeId);
            $isRegionPickupRuleActive = (bool)$this->regionGateway->getRegionOption($regionId, RegionOptionType::REGION_PICKUP_RULE_ACTIVE);

            if ($isRegionPickupRuleActive) {
                return $this->checkRegionPickupRule($regionId, $pickupDate, $fsId);
            }
        }

        return true;
    }

    private function checkRegionPickupRule($regionId, Carbon $pickupDate, int $fsId): bool
    {
        $timeUntilPickupToIgnoreRuleInHours = (int)$this->regionGateway->getRegionOption($regionId, RegionOptionType::REGION_PICKUP_RULE_INACTIVE_HOURS);
        $timeUntilPickupInHours = Carbon::now()->diffInHours($pickupDate);

        if ($timeUntilPickupInHours > $timeUntilPickupToIgnoreRuleInHours) {
            $timespanRegionRuleInDays = (int)$this->regionGateway->getRegionOption($regionId, RegionOptionType::REGION_PICKUP_RULE_TIMESPAN_DAYS);
            $numberAllowedPickupsPerTimespan = (int)$this->regionGateway->getRegionOption($regionId, RegionOptionType::REGION_PICKUP_RULE_LIMIT_NUMBER);
            $numberAllowedPickupsPerDay = (int)$this->regionGateway->getRegionOption($regionId, RegionOptionType::REGION_PICKUP_RULE_LIMIT_DAY_NUMBER);

            if ($numberAllowedPickupsPerDay < $numberAllowedPickupsPerTimespan
                && $this->pickupGateway->getNumberOfPickupsForUserWithStoreRulesSameDay($fsId, $pickupDate) >= $numberAllowedPickupsPerDay) {
                return false;
            }

            if ($numberAllowedPickupsPerTimespan == 1
                && $this->pickupGateway->getNumberOfPickupsForUserWithStoreRules($fsId, $pickupDate->copy()->subDays($timespanRegionRuleInDays), $pickupDate->copy()->addDays($timespanRegionRuleInDays)) >= $numberAllowedPickupsPerTimespan) {
                return false;
            }

            if ($numberAllowedPickupsPerTimespan > 1) {
                for ($i = 0; $i <= $timespanRegionRuleInDays; ++$i) {
                    $timespanStartDate = $pickupDate->copy()->subDays($timespanRegionRuleInDays - $i);
                    $timespanEndDate = $pickupDate->copy()->addDays($i);
                    $pickupsInTimespan = $this->pickupGateway->getNumberOfPickupsForUserWithStoreRules($fsId, $timespanStartDate, $timespanEndDate);

                    if ($pickupsInTimespan >= $numberAllowedPickupsPerTimespan) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
