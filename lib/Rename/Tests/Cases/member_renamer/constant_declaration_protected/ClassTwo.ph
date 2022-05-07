<?php

class ClassTwo extends ClassOne
{
    public function barfoo(): string
    {
        return parent::BAR;;
    }
}
