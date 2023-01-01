<?php

namespace Phpactor\Extension\ClassMover\Rpc;

use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\ClassMover\Application\ClassReferences;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\FileReferencesResponse;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\ClassMover\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use InvalidArgumentException;
use RuntimeException;

/**
 * TODO: Extract the responsiblities of this class, see
 *       https://github.com/phpactor/phpactor/issues/440
 */
class ReferencesHandler extends AbstractHandler
{
    const NAME = 'references';
    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_MODE = 'mode';
    const PARAMETER_PATH = 'path';
    const PARAMETER_FILESYSTEM = 'filesystem';
    const MODE_FIND = 'find';
    const MODE_REPLACE = 'replace';
    const PARAMETER_REPLACEMENT = 'replacement';
    const MESSAGE_NO_REFERENCES_FOUND = 'No references found';

    public function __construct(
        private Reflector $reflector,
        private ClassReferences $classReferences,
        private ClassMemberReferences $classMemberReferences,
        private FilesystemRegistry $registry,
        private string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_GIT
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAMETER_MODE => self::MODE_FIND,
            self::PARAMETER_FILESYSTEM => $this->defaultFilesystem,
            self::PARAMETER_REPLACEMENT => null,
        ]);
        $resolver->setRequired([
            self::PARAMETER_PATH,
            self::PARAMETER_OFFSET,
            self::PARAMETER_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(
            SourceCode::fromPathAndString(
                $arguments[self::PARAMETER_PATH],
                $arguments[self::PARAMETER_SOURCE]
            ),
            Offset::fromInt($arguments[self::PARAMETER_OFFSET])
        );
        $symbolContext = $offset->symbolContext();

        if (null === $arguments[self::PARAMETER_FILESYSTEM]) {
            $this->requireInput(ChoiceInput::fromNameLabelChoicesAndDefault(
                self::PARAMETER_FILESYSTEM,
                sprintf('%s "%s" in:', ucfirst($symbolContext->symbol()->symbolType()), $symbolContext->symbol()->name()),
                array_combine($this->registry->names(), $this->registry->names()),
                $this->defaultFilesystem
            ));
        }

        if ($arguments[self::PARAMETER_MODE] === self::MODE_REPLACE) {
            $this->requireInput(TextInput::fromNameLabelAndDefault(
                self::PARAMETER_REPLACEMENT,
                'Replacement: ',
                $this->defaultReplacement($symbolContext)
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }
        return match ($arguments[self::PARAMETER_MODE]) {
            self::MODE_FIND => $this->findReferences($symbolContext, $arguments['filesystem']),
            self::MODE_REPLACE => $this->replaceReferences(
                $symbolContext,
                $arguments['filesystem'],
                $arguments[self::PARAMETER_REPLACEMENT],
                $arguments[self::PARAMETER_PATH],
                $arguments[self::PARAMETER_SOURCE]
            ),
            default => throw new InvalidArgumentException(sprintf(
                'Unknown references mode "%s"',
                $arguments['mode']
            )),
        };
    }

    private function findReferences(NodeContext $symbolContext, string $filesystem)
    {
        [$source, $references] = $this->performFindOrReplaceReferences($symbolContext, $filesystem);

        if (count($references) === 0) {
            return EchoResponse::fromMessage(self::MESSAGE_NO_REFERENCES_FOUND);
        }

        $references = array_filter($references, function (array $referenceList) {
            return false === empty($referenceList['references']);
        });

        return CollectionResponse::fromActions([
            $this->echoMessage('Found', $symbolContext, $filesystem, $references),
            FileReferencesResponse::fromArray($references),
        ]);
    }

    private function replaceReferences(
        NodeContext $symbolContext,
        string $filesystem,
        string $replacement,
        string $path,
        string $source
    ) {
        $originalSource = $source;
        [$source, $references] = $this->performFindOrReplaceReferences(
            $symbolContext,
            $filesystem,
            $source,
            $replacement
        );

        if (count($references) === 0) {
            return EchoResponse::fromMessage(self::MESSAGE_NO_REFERENCES_FOUND);
        }

        $actions = [
            $this->echoMessage('Replaced', $symbolContext, $filesystem, $references),
        ];

        if ($source) {
            // renaming methods modifies files on disk. some editors track if
            // the file has been modified on the disk and issue a warning if
            // the open file is not in sync. below we reload the file before
            // applying changes (the changes from the rename operation,
            // including any changes made after the file was last saved).
            if (file_exists($path)) {
                $actions[] = OpenFileResponse::fromPath($path)->withForcedReload(true);
                $originalSource = file_get_contents($path);
            }
            $actions[] = UpdateFileSourceResponse::fromPathOldAndNewSource($path, $originalSource, $source);
        }

        if (count($references)) {
            $actions[] = FileReferencesResponse::fromArray($references);
        }

        return CollectionResponse::fromActions($actions);
    }

    private function classReferences(string $filesystem, NodeContext $symbolContext, string $source = null, string $replacement = null)
    {
        $classType = (string) $symbolContext->type();
        $references = $this->classReferences->findOrReplaceReferences($filesystem, $classType, $replacement);

        $updatedSource = null;
        if ($source) {
            $updatedSource = $this->classReferences->replaceInSource(
                $source,
                $classType,
                $replacement
            );
        }


        return [$updatedSource, $references['references']];
    }

    private function memberReferences(
        string $filesystem,
        NodeContext $symbolContext,
        string $memberType,
        string $source = null,
        string $replacement = null
    ) {
        $classType = (string) $symbolContext->containerType();

        $references = $this->classMemberReferences->findOrReplaceReferences(
            $filesystem,
            $classType,
            $symbolContext->symbol()->name(),
            $memberType,
            $replacement
        );

        $updatedSource = null;
        if ($source && $replacement) {
            $updatedSource = $this->classMemberReferences->replaceInSource(
                $source,
                $classType,
                $symbolContext->symbol()->name(),
                $memberType,
                $replacement
            );
        }

        return [$updatedSource, $references['references']];
    }

    private function performFindOrReplaceReferences(
        NodeContext $symbolContext,
        string $filesystem,
        string $source = null,
        string $replacement = null
    ) {
        [$source, $references] = $this->doPerformFindOrReplaceReferences(
            $symbolContext,
            $filesystem,
            $source,
            $replacement,
        );

        return [$source, $this->sortReferences($references)];
    }

    private function doPerformFindOrReplaceReferences(NodeContext $symbolContext, string $filesystem, string $source = null, string $replacement = null)
    {
        return match ($symbolContext->symbol()->symbolType()) {
            Symbol::CLASS_ => $this->classReferences($filesystem, $symbolContext, $source, $replacement),
            Symbol::METHOD => $this->memberReferences($filesystem, $symbolContext, ClassMemberQuery::TYPE_METHOD, $source, $replacement),
            Symbol::PROPERTY => $this->memberReferences($filesystem, $symbolContext, ClassMemberQuery::TYPE_PROPERTY, $source, $replacement),
            Symbol::CONSTANT => $this->memberReferences($filesystem, $symbolContext, ClassMemberQuery::TYPE_CONSTANT, $source, $replacement),
            default => throw new RuntimeException(sprintf(
                'Cannot find references for symbol type "%s"',
                $symbolContext->symbol()->symbolType()
            )),
        };
    }

    private function sortReferences(array $fileReferences): array
    {
        // Sort the references for each file
        array_walk($fileReferences, function (array &$fileReference): void {
            if (empty($fileReference['references'])) {
                return; // Do nothing if there is no references
            }

            usort($fileReference['references'], function (array $first, array $second) {
                return $first['start'] - $second['start'];
            });
        });

        // Sort the list by file
        usort($fileReferences, function (array $first, array $second) {
            return strcmp($first['file'], $second['file']);
        });

        return $fileReferences;
    }

    private function echoMessage(string $action, NodeContext $symbolContext, string $filesystem, array $references): EchoResponse
    {
        $count = array_reduce($references, function ($count, $result) {
            $count += count($result['references']);
            return $count;
        }, 0);

        $riskyCount = array_reduce($references, function ($count, $result) {
            if (!isset($result['risky_references'])) {
                return $count;
            }
            $count += count($result['risky_references']);
            return $count;
        }, 0);

        $risky = '';
        if ($riskyCount > 0) {
            $risky = sprintf(' (%s risky references not listed)', $riskyCount);
        }

        return EchoResponse::fromMessage(sprintf(
            '%s %s literal references to %s "%s" using FS "%s"%s',
            $action,
            $count,
            $symbolContext->symbol()->symbolType(),
            $symbolContext->symbol()->name(),
            $filesystem,
            $risky
        ));
    }

    private function defaultReplacement(NodeContext $symbolContext): string
    {
        $type = $symbolContext->type()->expandTypes()->classLike()->firstOrNull();
        if ($type instanceof ClassType) {
            return $type->name()->__toString();
        }

        return $symbolContext->symbol()->name();
    }
}
