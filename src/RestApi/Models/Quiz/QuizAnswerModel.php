<?php

namespace Foodsharing\RestApi\Models\Quiz;

use DateTime;
use Foodsharing\Modules\Foodsaver\DTO\FoodsaverForAvatar;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use JMS\Serializer\Annotation\Type;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class that represents the answers a user gives on a quiz.
 *
 * @OA\Schema(required={"answers"})
 */
class QuizAnswerModel
{
    /**
     * Identifiers of key account managers.
     *
     * @OA\Property(type="array", description="IDs of the selected answers", items={"type"="integer"})
     * @Assert\All(@Assert\Positive())
     * @Type("array<int>")
     */
    public array $answers = [];
}
