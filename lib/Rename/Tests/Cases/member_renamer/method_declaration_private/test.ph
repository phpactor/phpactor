<?php

require __DIR__ . '/ClassOne.php';

$one = new Foo\ClassOne();
if (!$one->hello() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}

