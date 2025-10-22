<?php

namespace Phpactor\ReferenceFinder;

class NameSearcherType
{
    public const FUNCTION = 'function';
    public const CLASS_ = 'class';
    public const INTERFACE = 'interface';
    public const TRAIT = 'trait';
    public const ENUM = 'enum';
    public const ATTRIBUTE = 'attribute';
    public const ATTRIBUTE_TARGET_CLASS = 'attribute_target_class';
    public const ATTRIBUTE_TARGET_FUNCTION = 'attribute_target_function';
    public const ATTRIBUTE_TARGET_METHOD = 'attribute_target_method';
    public const ATTRIBUTE_TARGET_PROPERTY = 'attribute_target_property';
    public const ATTRIBUTE_TARGET_PROMOTED_PROPERTY = 'attribute_target_promoted_property';
    public const ATTRIBUTE_TARGET_CLASS_CONSTANT = 'attribute_target_class_constant';
    public const ATTRIBUTE_TARGET_PARAMETER = 'attribute_target_parameter';
    public const ATTRIBUTE_IS_REPEATABLE = 'attribute_is_repeatable';
}
