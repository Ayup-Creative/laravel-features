<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class WhenFeatureDisabled
{
    public function __construct(
        public string $action = 'skip' // skip|abort|fail|delay
    )
    {
    }
}
