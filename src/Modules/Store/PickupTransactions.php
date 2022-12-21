<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Store\DTO\RegularPickup;

class PickupTransactions
{
    public function __construct(
        private StoreTransactions $storeTransactions,
        private RegularPickupGateway $regularPickupGateway
    ) {
    }

    /**
     * Return all regular pickup for an store.
     *
     * @return RegularPickup[] List of regular pickups
     */
    public function getRegularPickup(int $storeId): array
    {
        return $this->regularPickupGateway->getRegularPickup($storeId);
    }

    /**
     * Replace the regular pick up of an store.
     *
     * @param int $storeId Store for replacement
     * @param RegularPickup[] $regularPickups List of new regular pickups
     *
     * @return RegularPickup[] List of the new regular pickups
     */
    public function replaceRegularPickup(int $storeId, array $regularPickups)
    {
        if (!$this->storeTransactions->existStore($storeId)) {
            throw new PickupValidationException(PickupValidationException::INVALID_STORE);
        }

        $timestamps = [];
        foreach ($regularPickups as $key => $pickup) {
            if (($pickup->maxCountOfSlots < 0) ||
                (StoreTransactions::MAX_SLOTS_PER_PICKUP < $pickup->maxCountOfSlots)) {
                throw new PickupValidationException(PickupValidationException::MAX_SLOT_COUNT_OUT_OF_RANGE, $key);
            }
            $timestamps[] = $pickup->weekday . $pickup->startTimeOfPickup;
        }
        $uniqueTimestamps = array_unique($timestamps);
        if (count($regularPickups) != count($uniqueTimestamps)) {
            throw new PickupValidationException(PickupValidationException::DUPLICATE_PICKUP_DAY_TIME);
        }

        $this->regularPickupGateway->deleteAllRegularPickups($storeId);
        foreach ($regularPickups as $regularPickup) {
            $this->regularPickupGateway->insertOrUpdateRegularPickup($storeId, $regularPickup);
        }
        $this->storeTransactions->triggerBellForRegularPickupChanged($storeId);

        return $this->getRegularPickup($storeId);
    }
}
