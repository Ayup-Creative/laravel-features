<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DispatchWhenFeatureEnabled
{
    public function __construct(
        public string $key
    )
    {
    }
}
