<?php

namespace AyupCreative\Features\Evaluation;

use ReflectionClass;

class FeatureAttributeResolver
{
    public function resolve(object|string $target, ?string $method = null): array
    {
        $ref = new ReflectionClass($target);
        $attributes = [];

        $current = $ref;
        while ($current) {
            $attributes = array_merge(
                $attributes,
                array_map(fn($a) => $a->newInstance(), $current->getAttributes())
            );
            $current = $current->getParentClass();
        }

        if ($method !== null && $ref->hasMethod($method)) {
            $attributes = array_merge(
                $attributes,
                array_map(fn($a) => $a->newInstance(), $ref->getMethod($method)->getAttributes())
            );
        }

        return $attributes;
    }
}
