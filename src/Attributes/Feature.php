<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Feature
{
    public function __construct(
        public string $key
    )
    {
    }
}
