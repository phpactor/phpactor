<?php

namespace Phpactor\Extension\ExtensionManager\Model;

interface ExtensionConfigLoader
{
    public function load(): ExtensionConfig;
}
