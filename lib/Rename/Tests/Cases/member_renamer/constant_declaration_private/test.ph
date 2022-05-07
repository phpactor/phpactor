<?php

require __DIR__ . '/ClassOne.php';

$one = new ClassOne();
if ($one->foobar() !== 'bar') {
    echo 'expected "foobar" but didn\'t get it';
    exit(127);
}
