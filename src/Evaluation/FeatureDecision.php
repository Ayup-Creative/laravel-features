<?php

namespace AyupCreative\Features\Evaluation;

class FeatureDecision
{
    public function __construct(public bool $allowed, public string $action = 'abort')
    {
    }

    public static function allow(): self
    {
        return new self(true);
    }

    public static function deny(string $action = 'abort'): self
    {
        return new self(false, $action);
    }
}
