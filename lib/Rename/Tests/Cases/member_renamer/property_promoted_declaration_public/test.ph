<?php

require __DIR__ . '/ClassOne.php';
require __DIR__ . '/ClassTwo.php';

$two = new ClassTwo('foobar');
$one = new Test\ClassOne('foobar');
if (!$two->barfoo() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
if (!$two->foobar === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
if (!$two->barfoo() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
