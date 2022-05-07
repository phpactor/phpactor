<?php

require __DIR__ . '/ClassOne.php';
require __DIR__ . '/ClassTwo.php';

$two = new ClassTwo('foobar');
if (!$two->barfoo() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
