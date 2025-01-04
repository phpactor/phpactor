<?php

require __DIR__ . '/ClassOne.php';
require __DIR__ . '/ClassTwo.php';

$one = new ClassOne('foobar');
$two = new ClassTwo('foobar');
if (!$two->barfoo() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
if (ClassOne::FOO !== 'bar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
if (ClassOne::ZOO !== 'bar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
