<?php

namespace Phpactor\Extension\ExtensionManager\Model;

interface Installer
{
    public function install(): void;

    public function installForceUpdate(): void;
}
