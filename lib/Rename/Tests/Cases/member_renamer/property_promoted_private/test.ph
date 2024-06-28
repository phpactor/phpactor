<?php

require __DIR__ . '/ClassOne.php';

$two = new ClassOne('foobar', 'foobar');

// ensure that the method call will still work
// after the renaming
if (!$two->bar() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}

// this method call ensure that we did not replace
// all property names
if (!$two->foo() === 'barfoo') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
