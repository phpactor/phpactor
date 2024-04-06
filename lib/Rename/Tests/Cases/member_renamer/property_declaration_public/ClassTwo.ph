<?php

class ClassTwo extends ClassOne
{
    public function barfoo(): string
    {
        $foobar = $this->foobar;

        return $this->found;
    }
}
