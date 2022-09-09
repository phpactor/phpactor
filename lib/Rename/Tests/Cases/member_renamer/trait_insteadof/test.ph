<?php

require __DIR__ . '/One.php';

$talker = new Talker();
if ($talker->foobar() !== 'a') {
    echo sprintf('expected "a" but didn\'t but got "%s"', $talker->foobar());
    exit(127);
}
