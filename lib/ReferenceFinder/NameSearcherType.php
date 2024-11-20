<?php

namespace Phpactor\ReferenceFinder;

enum NameSearcherType: string
{
    case FUNCTION = 'function';
    case CLASS_ = 'class';
    case INTERFACE = 'interface';
    case TRAIT = 'trait';
    case ENUM = 'enum';
    case ATTRIBUTE = 'attribute';
}
