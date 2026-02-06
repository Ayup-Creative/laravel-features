<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FeatureDeprecated
{
    public function __construct(
        public string  $key,
        public ?string $removeBy = null
    )
    {
    }
}
