<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FeatureContext
{
    public function __construct(
        public string $context // user|team|account|env
    )
    {
    }
}
