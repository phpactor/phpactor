<?php

namespace Phpactor\CodeTransform\Domain;

interface GenerateNew extends Generator
{
    /**
     * Examples:
     *
     * - New class
     * - Interface from existing class
     */
    public function generateNew(ClassName $targetName): SourceCode;
}
