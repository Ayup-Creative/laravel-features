<?php

namespace AyupCreative\Features\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LimitFeatureUsage
{
    public function __construct(public int $max, public int $perSeconds)
    {
    }
}
