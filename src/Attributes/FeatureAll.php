<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FeatureAll
{
    public function __construct(
        public array $keys
    )
    {
    }
}
