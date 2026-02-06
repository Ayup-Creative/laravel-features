<?php

namespace AyupCreative\Features\Attributes\Semantic;

use Attribute;
use AyupCreative\Features\Attributes\WhenFeatureDisabled;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FailWhenFeatureDisabled extends WhenFeatureDisabled
{
    public function __construct()
    {
        parent::__construct('fail');
    }
}
