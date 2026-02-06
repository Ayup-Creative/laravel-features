<?php

namespace AyupCreative\Features\Evaluation;

use ReflectionClass;

class FeatureAttributeResolver
{
    public function resolve(object|string $target, ?string $method = null): array
    {
        $ref = new ReflectionClass($target);

        $attributes = array_map(fn($a) => $a->newInstance(), $ref->getAttributes());

        if ($method !== null && $ref->hasMethod($method)) {
            $attributes = array_merge(
                $attributes,
                array_map(fn($a) => $a->newInstance(), $ref->getMethod($method)->getAttributes())
            );
        }

        return $attributes;
    }
}
