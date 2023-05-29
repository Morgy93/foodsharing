<?php

namespace Foodsharing\RestApi\Models\StoreChain;

use DateTime;
use Foodsharing\Modules\StoreChain\DTO\StoreChain;
use Foodsharing\Modules\StoreChain\StoreChainStatus;
use Foodsharing\Validator\NoHtml;
use JMS\Serializer\Annotation\Type;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class that represents the data of a store chain, in a format in which it is sent to the client.
 */
class CreateStoreChainModel
{
    /**
     * Name of the chain.
     *
     * @OA\Property(example="MyChain GmbH")
     * @Assert\NotNull()
     * @Assert\Length(max=120)
     *
     * @NoHtml
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
    public int $status;

    /**
     * ZIP code of the chains headquater.
     *
     * @OA\Property(example="48149", nullable=true)
     * @Assert\NotNull()
     * @Assert\Length(max=5)
     *
     * @NoHtml
     */
    public ?string $headquarters_zip;

    /**
     * City of the chains headquater.
     *
     * @OA\Property(example="Münster", nullable=true)
     * @Assert\NotNull()
     * @Assert\Length(max=50)
     *
     * @NoHtml
     */
    public ?string $headquarters_city;

    /**
     * Whether the chain can be referred to in press releases.
     */
    public ?bool $allow_press;

    /**
     * Identifier of a forum thread related to this chain.
     *
     * @OA\Property(example=12345)
     * @Assert\Range (min = 0)
     * @Assert\NotNull()
     */
    public ?int $forum_thread;

    /**
     * Miscellaneous notes.
     *
     * @OA\Property(example="Cooperating since 2021", nullable=true)
     * @Assert\Length(max=200)
     *
     * @NoHtml
     */
    public ?string $notes;

    /**
     * Information about the chain to be displayed on every related stores page.
     *
     * @OA\Property(example="Pickup times between 10:00 and 12:15", nullable=true)
     * @Assert\Length(max=16777215)
     */
    public ?string $common_store_information;

    /**
     * Identifiers of key account managers.
     *
     * @OA\Property(type="array", description="Managers of this chain",	items={"type"="integer"})
     * @Assert\All(@Assert\Positive())
     * @Type("array<int>")
     */
    public array $kams = [];

    public function toCreateStore(): StoreChain
    {
        $obj = new StoreChain();
        $obj->name = $this->name;
        $obj->status = StoreChainStatus::from($this->status);
        $obj->allow_press = $this->allow_press;
        $obj->headquarters_zip = $this->headquarters_zip;
        $obj->headquarters_city = $this->headquarters_city;
        $obj->modification_date = new DateTime();
        $obj->forum_thread = $this->forum_thread;
        $obj->notes = $this->notes;
        $obj->common_store_information = $this->common_store_information;
        $obj->kams = $this->kams;

        return $obj;
    }
}
