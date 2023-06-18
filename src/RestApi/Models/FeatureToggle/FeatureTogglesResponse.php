<?php

declare(strict_types=1);

namespace Foodsharing\RestApi\Models\FeatureToggle;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;

class FeatureTogglesResponse
{
    #[Property(
        description: 'All existing feature toggles',
        type: 'array',
        items: new Items(ref: new Model(type: FeatureToggle::class))
    )]
    public readonly array $featureToggles;

    public function __construct(array $featureToggles)
    {
        $this->featureToggles = $featureToggles;
    }
}
