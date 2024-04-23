<?php

declare(strict_types=1);

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Cases;
use Phpactor\CodeBuilder\Domain\Prototype\EnumPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;

class EnumBuilder extends ClassLikeBuilder
{
    /**
     * @var CaseBuilder[]
    */
    protected array $cases = [];

    public static function childNames(): array
    {
        return array_merge(parent::childNames(), ['cases']);
    }

    public function case(string $name): CaseBuilder
    {
        if (!isset($this->cases[$name])) {
            $this->cases[$name] = new CaseBuilder($this, $name);
        }

        return $this->cases[$name];
    }

    public function build(): EnumPrototype
    {
        $updatePolicy = UpdatePolicy::fromModifiedState($this->isModified());
        return new EnumPrototype(
            $this->name,
            Cases::fromCases(array_map(function (CaseBuilder $case) { return $case->build(); }, $this->cases)),
            Methods::fromMethods(array_map(function (MethodBuilder $builder) { return $builder->build(); }, $this->methods)),
            $updatePolicy
        );
    }
}
