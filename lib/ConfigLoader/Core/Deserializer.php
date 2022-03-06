<?php

namespace Phpactor\ConfigLoader\Core;

interface Deserializer
{
    public function deserialize(string $contents): array;
}
