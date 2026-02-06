<?php

namespace AyupCreative\Features\Tests\Unit\Resolution;

use AyupCreative\Features\Evaluation\FeatureAttributeResolver;
use AyupCreative\Features\Tests\TestCase;
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\WhenFeatureDisabled;

class FeatureAttributeResolverTest extends TestCase
{
    protected FeatureAttributeResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new FeatureAttributeResolver();
    }

    public function test_it_resolves_class_attributes()
    {
        $target = new #[Feature('test-feature')] class {};
        $attributes = $this->resolver->resolve($target);

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Feature::class, $attributes[0]);
        $this->assertEquals('test-feature', $attributes[0]->key);
    }

    public function test_it_resolves_method_attributes()
    {
        $target = new class {
            #[Feature('method-feature')]
            public function handle() {}
        };

        $attributes = $this->resolver->resolve($target, 'handle');

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Feature::class, $attributes[0]);
        $this->assertEquals('method-feature', $attributes[0]->key);
    }

    public function test_it_merges_class_and_method_attributes()
    {
        $target = new #[Feature('class-feature')] class {
            #[Feature('method-feature')]
            public function handle() {}
        };

        $attributes = $this->resolver->resolve($target, 'handle');

        $this->assertCount(2, $attributes);
        $this->assertEquals('class-feature', $attributes[0]->key);
        $this->assertEquals('method-feature', $attributes[1]->key);
    }

    public function test_it_throws_exception_on_multiple_disabled_actions()
    {
        $target = new class {
            #[WhenFeatureDisabled('skip')]
            #[WhenFeatureDisabled('abort')]
            public function handle() {}
        };

        $this->expectException(\Error::class);
        $this->expectExceptionMessage(sprintf('Attribute "%s" must not be repeated', WhenFeatureDisabled::class));

        $this->resolver->resolve($target, 'handle');
    }

    public function test_it_handles_string_targets()
    {
        $attributes = $this->resolver->resolve(StubClassWithAttribute::class);

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(Feature::class, $attributes[0]);
    }
}

#[Feature('test-feature')]
class StubClassWithAttribute {}
