<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Unit\UnitType;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\RegionAdmin\DTO\RegionDetails;
use Foodsharing\RestApi\Models\Region\RegionUpdateModel;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegionAdminTransactions
{
    public function __construct(
        private readonly RegionGateway $regionGateway,
        private readonly GroupFunctionGateway $groupFunctionGateway,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Returns details about a region or working group or null if the id does not exist.
     */
    public function getRegionDetails(int $regionId): ?RegionDetails
    {
        $region = $this->regionGateway->getRegion($regionId);
        if (empty($region)) {
            return null;
        }

        $workingGroupFunction = $region['type'] === UnitType::WORKING_GROUP
            ? $this->groupFunctionGateway->getRegionGroupFunctionId($region['id'], $region['parent_id'])
            : null;

        return RegionDetails::create(
            $regionId,
            $region['name'],
            $region['parent_id'],
            $region['type'],
            $workingGroupFunction,
            $region['email'],
            $region['email_name']
        );
    }

    /**
     * Adds a new region and returns details about that region.
     */
    public function addRegion(int $parentId): RegionDetails
    {
        $parentRegion = $this->regionGateway->getRegion($parentId);
        if (empty($parentRegion)) {
            throw new BadRequestHttpException('parent region does not exist');
        }

        $regionId = $this->regionGateway->addRegion($parentId, 'Neue Region');
        $this->regionGateway->setRegionHasChildren($parentId, true);

        return $this->getRegionDetails($regionId);
    }

    /**
     * Updates the properties of an existing region.
     *
     * @param int $regionId id of the region to be updated
     * @param RegionUpdateModel $model new properties of the region
     * @param array $oldRegionData old properties of the region
     *
     * @throws BadRequestHttpException if the region type or the working group function is not valid or if the working
     *                                 group function already exists in the parent region
     */
    public function updateRegion(int $regionId, RegionUpdateModel $model, array $oldRegionData): void
    {
        if (!UnitType::isValid($model->type)) {
            throw new BadRequestHttpException('invalid region type');
        }

        $parentId = $oldRegionData['parent_id'];

        // Update the working group function if the region is a working group and if the function is different from the existing value
        if (UnitType::isGroup($model->type)) {
            $oldWorkingGroupFunction = $this->groupFunctionGateway->getRegionGroupFunctionId($parentId, $regionId);
            if ($model->workingGroupFunction !== $oldWorkingGroupFunction) {
                $this->updateWorkingGroupFunction($regionId, $parentId, $model->workingGroupFunction, $oldWorkingGroupFunction);
            }
        }
    }

    /**
     * Updates the working group function of a specific group. This assumes that the region is indeed a working group
     * and that the old and new function are different ({@see WorkgroupFunction}).
     *
     * @param int $regionId the working group to be changed
     * @param int $parentId the group's parent region
     * @param int|null $newFunction a new function value or null if the group shall not have a function anymore
     * @param int|null $oldFunction the old value or null if the group does not have a function yet
     *
     * @throws \Exception
     */
    private function updateWorkingGroupFunction(int $regionId, int $parentId, ?int $newFunction, ?int $oldFunction): void
    {
        // Make sure that the new value is valid and that there is only one working group with the specified function in the parent region
        if (!is_null($newFunction)) {
            if (!WorkgroupFunction::isValidFunction($newFunction)) {
                throw new BadRequestHttpException('invalid working group function');
            }

            $existingGroupId = $this->groupFunctionGateway->getRegionFunctionGroupId($parentId, WorkgroupFunction::WELCOME);
            if ($existingGroupId && $existingGroupId !== $regionId) {
                $translationByFunction = [
                    WorkgroupFunction::WELCOME => 'duplicate_welcome_team',
                    WorkgroupFunction::VOTING => 'duplicate_voting_team',
                    WorkgroupFunction::FSP => 'duplicate_fsp_team',
                    WorkgroupFunction::STORES_COORDINATION => 'duplicate_stores_team',
                    WorkgroupFunction::REPORT => 'duplicate_report_team',
                    WorkgroupFunction::MEDIATION => 'duplicate_mediation_team',
                    WorkgroupFunction::ARBITRATION => 'duplicate_arbitration_team',
                    WorkgroupFunction::FSMANAGEMENT => 'duplicate_fsmanagement_team',
                    WorkgroupFunction::PR => 'duplicate_pr_team',
                    WorkgroupFunction::MODERATION => 'duplicate_moderation_team',
                    WorkgroupFunction::BOARD => 'duplicate_board_team',
                ];
                throw new BadRequestHttpException($this->translator->trans('group.function.' . $translationByFunction[$newFunction]));
            }
        }

        // If everything is fine, remove the old function and add the new one
        if (!is_null($oldFunction)) {
            $this->groupFunctionGateway->deleteRegionFunction($regionId, $oldFunction);
        }
        if (!is_null($newFunction)) {
            $this->groupFunctionGateway->addRegionFunction($parentId, $regionId, $newFunction);
        }
    }
}
