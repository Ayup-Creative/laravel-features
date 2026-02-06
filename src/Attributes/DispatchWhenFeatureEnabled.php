<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class DispatchWhenFeatureEnabled
{
    public function __construct(
        public string $key
    )
    {
    }
}
