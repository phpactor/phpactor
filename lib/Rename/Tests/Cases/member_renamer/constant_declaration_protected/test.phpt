<?php

require __DIR__ . '/ClassOne.php';
require __DIR__ . '/ClassTwo.php';

$two = new ClassTwo();
if ($two->barfoo() !== 'bar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
