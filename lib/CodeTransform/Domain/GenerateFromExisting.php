<?php

namespace Phpactor\CodeTransform\Domain;

interface GenerateFromExisting extends Generator
{
    /**
     * - Test for existing class
     * - Trait from existing
     */
    public function generateFromExisting(ClassName $existingClass, ClassName $targetName): SourceCode;
}
