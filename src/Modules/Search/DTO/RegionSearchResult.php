<?php

namespace Foodsharing\Modules\Search\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class RegionSearchResult extends SearchResult
{
    /**
     * Email address of the region.
     *
     * Does not include the "@foodsharing.network" ending.
     * 
     * @OA\Property(example="muenster")
     */
    public string $email;

    /**
     * Unique identifier of the regions parent region.
     *
     * @OA\Property(example=1)
     */
    public int $parent_id;

    /**
     * Name of the regions parent region.
     * 
     * @OA\Property(example="Nordrhein-Westfalen")
     */
    public string $parent_name;

    /**
     * Whether the searching user is member in the region.
     * 
     * @OA\Property(example=true)
     */
    public bool $is_member;

    /**
     * Ambassadors of the region. 
     * 
     * @var array<FoodsaverForAvatar> Array of Ambassadors
     *
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref=@Model(type=FoodsaverForAvatar::class))
     * )
     */
    public array $ambassadors;

    public static function createFromArray(array $data): RegionSearchResult
    {
        $result = new RegionSearchResult();
        $result->id = $data['id'];
        $result->name = $data['name'];
        $result->email = $data['email'];
        $result->parent_id = $data['parent_id'];
        $result->parent_name = $data['parent_name'];
        $result->is_member = boolval($data['is_member']);
        $result->ambassadors = self::formatUserList($data, 'ambassador');
        return $result;
    }
}