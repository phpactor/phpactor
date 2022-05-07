<?php

require __DIR__ . '/ClassOne.php';

$two = new ClassOne('foobar');
if (!$two->foobar() === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
