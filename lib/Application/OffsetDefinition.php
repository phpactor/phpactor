<?php

namespace Phpactor\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\Core\GotoDefinition\GotoDefinition;
use Phpactor\Application\Response\OffsetDefinitionResponse;
use Phpactor\Core\GotoDefinition\GotoDefinitionResult;

class OffsetDefinition
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Helper\FilesystemHelper
     */
    private $filesystemHelper;

    /**
     * @var Helper\ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var GotoDefinition
     */
    private $gotoDefinition;

    public function __construct(
        Reflector $reflector,
        Helper\ClassFileNormalizer $classFileNormalizer
    ) {
        $this->reflector = $reflector;
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystemHelper = new Helper\FilesystemHelper();
        $this->gotoDefinition = new GotoDefinition($reflector);
    }

    public function gotoDefinition(string $sourcePath, int $offset, $showFrame = false): GotoDefinitionResult
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromString(
                $this->filesystemHelper->contentsFromFileOrStdin($sourcePath)
            ),
            Offset::fromInt($offset)
        );

        $symbolInformation = $result->symbolInformation();

        return $this->gotoDefinition->gotoDefinition($symbolInformation);
    }
}
