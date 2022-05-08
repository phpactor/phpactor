<?php

namespace Phpactor\Indexer\Model\Query;

use Phpactor\Indexer\Model\Query\Criteria\AndCriteria;
use Phpactor\Indexer\Model\Query\Criteria\FileAbsolutePathBeginsWith;
use Phpactor\Indexer\Model\Query\Criteria\IsClassType;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameContains;
use Phpactor\Indexer\Model\Query\Criteria\ExactShortName;
use Phpactor\Indexer\Model\Query\Criteria\FqnBeginsWith;
use Phpactor\Indexer\Model\Query\Criteria\IsClass;
use Phpactor\Indexer\Model\Query\Criteria\IsConstant;
use Phpactor\Indexer\Model\Query\Criteria\IsFunction;
use Phpactor\Indexer\Model\Query\Criteria\IsMember;
use Phpactor\Indexer\Model\Query\Criteria\OrCriteria;
use Phpactor\Indexer\Model\Query\Criteria\ShortNameBeginsWith;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;

abstract class Criteria
{
    abstract public function isSatisfiedBy(Record $record): bool;

    public static function exactShortName(string $name): ExactShortName
    {
        return new ExactShortName($name);
    }

    public static function shortNameBeginsWith(string $name): ShortNameBeginsWith
    {
        return new ShortNameBeginsWith($name);
    }

    public static function fqnBeginsWith(string $name): FqnBeginsWith
    {
        return new FqnBeginsWith($name);
    }

    public static function and(Criteria ...$criterias): AndCriteria
    {
        return new AndCriteria(...$criterias);
    }

    public static function or(Criteria ...$criterias): OrCriteria
    {
        return new OrCriteria(...$criterias);
    }

    public static function isClass(): IsClass
    {
        return new IsClass();
    }

    public static function isClassConcrete(): IsClassType
    {
        return new IsClassType(ClassRecord::TYPE_CLASS);
    }

    public static function isClassInterface(): IsClassType
    {
        return new IsClassType(ClassRecord::TYPE_INTERFACE);
    }

    public static function isClassTrait(): IsClassType
    {
        return new IsClassType(ClassRecord::TYPE_TRAIT);
    }

    public static function isClassTypeUndefined(): IsClassType
    {
        return new IsClassType(null);
    }

    public static function isClassEnum(): IsClassType
    {
        return new IsClassType(ClassRecord::TYPE_ENUM);
    }

    public static function isMember(): IsMember
    {
        return new IsMember();
    }

    public static function isFunction(): IsFunction
    {
        return new IsFunction();
    }

    public static function isConstant(): IsConstant
    {
        return new IsConstant();
    }

    public static function shortNameContains(string $substr): ShortNameContains
    {
        return new ShortNameContains($substr);
    }

    public static function fileAbsolutePathBeginsWith(string $prefix): FileAbsolutePathBeginsWith
    {
        return new FileAbsolutePathBeginsWith($prefix);
    }
}
