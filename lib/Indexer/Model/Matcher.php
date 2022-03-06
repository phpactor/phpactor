<?php

namespace Phpactor\Indexer\Model;

interface Matcher
{
    public function match(string $subject, string $query): bool;
}
