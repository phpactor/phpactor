<?php

namespace Phpactor\Extension\PhpSpec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\PhpSpec\PhpSpecExtension;
use Phpactor\Extension\PhpSpec\Provider\ObjectBehaviorMemberProvider;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

final class PhpSpecExtensionTest extends TestCase
{
    private PhpSpecExtension $sut;

    public function setUp(): void
    {
        $this->sut = new PhpSpecExtension();
    }

    /**
     * @dataProvider provideParameterDefaultValue
     */
    public function testParameterDefaultValue(string $name, mixed $value): void
    {
        $schema = new Resolver();
        $this->sut->configure($schema);

        $this->assertEquals($value, $schema->resolve([])[$name]);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public function provideParameterDefaultValue(): iterable
    {
        return [
            'enabled = false' => [PhpSpecExtension::PARAM_ENABLED, false],
            'prefix = spec' => [PhpSpecExtension::PARAM_SPEC_PREFIX, 'spec'],
        ];
    }

    public function testMemberProviderIsNotRegisteredWhenExtensionIsDisabled(): void
    {
        $container = new PhpactorContainer([
            PhpSpecExtension::PARAM_ENABLED => false,
        ]);

        $this->sut->load($container);

        $memberProviderIds = array_keys($container->getServiceIdsForTag(WorseReflectionExtension::TAG_MEMBER_PROVIDER));
        $this->assertCount(1, $memberProviderIds);

        $this->assertNull($container->get($memberProviderIds[0]));
    }

    public function testMemberProviderIsRegisteredWhenExtensionIsEnabled(): void
    {
        $container = new PhpactorContainer([
            PhpSpecExtension::PARAM_ENABLED => true,
            PhpSpecExtension::PARAM_SPEC_PREFIX => 'spec',
        ]);

        $this->sut->load($container);

        $memberProviderIds = array_keys($container->getServiceIdsForTag(WorseReflectionExtension::TAG_MEMBER_PROVIDER));
        $this->assertCount(1, $memberProviderIds);

        $this->assertInstanceOf(ObjectBehaviorMemberProvider::class, $container->get($memberProviderIds[0]));
    }
}
