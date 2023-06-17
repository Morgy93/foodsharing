<?php

namespace Foodsharing\Modules\StoreChain\DTO;

use Foodsharing\Validator\NoHtml;
use Foodsharing\Validator\NoMarkdown;
use Foodsharing\Validator\NoMultiLineText;
use JMS\Serializer\Annotation\Type;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class that represents the data of a store chain, in a format in which it is sent to the client.
 * This is not an entity class, it does not provide any domain logic nor does it contain any access
 * logic. You can see it more like a Data Transfer Object (DTO) used to pass a chains data between
 * parts of the application in a unified format.
 */
class PatchStoreChain
{
    /**
     * Name of the chain.
     *
     * @OA\Property(example="MyChain GmbH")
     * @Assert\Length(max=120)
     *
     * @NoHtml
     *
     * @NoMultiLineText
     *
     * @NoMarkdown
     */
    public ?string $name;

    /**
     * Indicates the cooperation status of this chain.
     * - '0' - Not Cooperating
     * - '1' - Waiting, i.e. in negotiation
     * - '2' - Cooperating.
     *
     * @OA\Property(enum={0, 1, 2}, example=2)
     * @Assert\Range (min = 0, max = 2)
     */
    public ?int $status;

    /**
     * ZIP code of the chains headquater.
     *
     * @OA\Property(example="48149", nullable=true)
     * @Assert\Length(max=5)
     *
     * @NoHtml
     *
     * @NoMultiLineText
     *
     * @NoMarkdown
     */
    public ?string $headquartersZip;

    /**
     * City of the chains headquater.
     *
     * @OA\Property(example="Münster", nullable=true)
     * @Assert\Length(max=50)
     *
     * @NoHtml
     *
     * @NoMultiLineText
     *
     * @NoMarkdown
     */
    public ?string $headquartersCity;

    /**
     * Whether the chain can be referred to in press releases.
     */
    public ?bool $allowPress;

    /**
     * Identifier of a forum thread related to this chain.
     *
     * @OA\Property(example=12345)
     * @Assert\Range (min = 0)
     */
    public ?int $forumThread;

    /**
     * Miscellaneous notes.
     *
     * @OA\Property(example="Cooperating since 2021", nullable=true)
     * @Assert\Length(max=200)
     *
     * @NoHtml
     *
     * @NoMultiLineText
     *
     * @NoMarkdown
     */
    public ?string $notes;

    /**
     * Information about the chain to be displayed on every related stores page.
     *
     * @OA\Property(example="Pickup times between 10:00 and 12:15", nullable=true)
     * @Assert\Length(max=16777215)
     */
    public ?string $commonStoreInformation;

    /**
     * Identifiers of key account managers.
     *
     * @OA\Property(type="array", description="Managers of this chain",	items={"type"="integer"})
     * @Assert\All(@Assert\Positive())
     *
     * @var int[] List of grocerie which are provided by the store
     *
     * @Type("array<int>")
     */
    public ?array $kams;
}
