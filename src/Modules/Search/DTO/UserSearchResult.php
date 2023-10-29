<?php

namespace Foodsharing\Modules\Search\DTO;

use OpenApi\Annotations as OA;

class UserSearchResult extends SearchResult
{
    /**
     * URL of the users avatar.
     * 
     * May be null.
     * 
     * @OA\Property(example=null)
     */
    public ?string $avatar;

    /**
     * Zip code of the food share points adress.
     * 
     * @OA\Property(example="Oskar-Michels-Ring 29")
     */
    public ?string $last_name;

    /**
     * City of the food share points adress.
     * 
     * @OA\Property(example="Münster")
     */
    public ?string $mobile;

    /**
     * Whether the searching user and the found user are buddies.
     * 
     * @OA\Property(example=true)
     */
    public bool $is_buddy;

    /**
     * Unique identifier of the food share points region.
     *
     * @OA\Property(example=1)
     */
    public int $region_id;

    /**
     * Name of the food share points region.
     * 
     * @OA\Property(example="Münster")
     */
    public string $region_name;

    public static function createFromArray(array $data): UserSearchResult
    {
        $result = new UserSearchResult();
        $result->id = $data['id'];
        $result->name = $data['name'];
        $result->avatar = $data['photo'];
        $result->region_id = $data['region_id'];
        $result->region_name = $data['region_name'];
        $result->last_name = $data['last_name'];
        $result->mobile = $data['mobile'];
        $result->is_buddy = $data['is_buddy'];
        return $result;
    }
}