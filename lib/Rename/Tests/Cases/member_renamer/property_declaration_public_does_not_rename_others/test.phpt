<?php

require __DIR__ . '/ClassOne.php';

$one = new ClassOne('foobar');
if (!$two->foobar === 'foobar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
