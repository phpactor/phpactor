<?php

namespace Phpactor\Complete;

class Suggestion
{
    const TYPE_PROPERTY = 'm';
    const TYPE_METHOD = 'f';
    const TYPE_VARIABLE = 'v';

    public $name;
    public $type;
    public $info;

    final public function __construct()
    {
    }

    public static function create(string $name, string $type, string $info = null)
    {
        $suggestion = new Suggestion();
        $suggestion->name = $name;
        $suggestion->type = $type;

        // do not give a NULL value to VIM, it causes this issue
        // https://github.com/Valloric/YouCompleteMe/pull/147
        $suggestion->info = $info ?: '<no info>';

        return $suggestion;
    }

    public function __toString()
    {
        return $this->name;
    }
}
