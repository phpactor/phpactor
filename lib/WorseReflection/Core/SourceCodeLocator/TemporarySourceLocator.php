<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

/**
 * Source locator for keeping track of source code provided at call time.
 *
 * Note this locator IS NOT SUITABLE FOR LONG RUNNING PROCESSES due to the way
 * it handles source code without a path.
 *
 * During a given process many calls maybe made to the reflector, and any code
 * provided directly (rather than located from the filesystem) should have
 * precedence over other code.
 *
 * Because it's possible to provide source without a path (which is a mistake)
 * we have no way of identifying code which is _updated_ so we just push to an
 * array with a numerical index in this case.
 *
 * In a long-running process (i.e. a server) this will result in an every
 * growing stack of outdated data.
 *
 * This locator should ONLY be used on short-lived processes (i.e. web request,
 * or RPC request) or in situations where you are confident you provide the
 * path for all source code.
 */
class TemporarySourceLocator implements SourceCodeLocator
{
    const BUFFER_SIZE = 10;

    /**
     * @var SourceCode[]
     */
    private array $sources = [];

    public function __construct(
        private SourceCodeReflector $reflector,
        private bool $locateFunctions = false
    ) {
    }

    public function pushSourceCode(SourceCode $source): void
    {
        if (count($this->sources) > self::BUFFER_SIZE) {
            array_shift($this->sources);
        }

        $this->sources[] = $source;
    }

    public function locate(Name $name): SourceCode
    {
        foreach ($this->sources as $source) {
            $classes = $this->reflector->reflectClassesIn($source);

            if ($classes->has((string) $name)) {
                return $source;
            }

            if ($this->locateFunctions) {
                $functions = $this->reflector->reflectFunctionsIn($source);

                if ($functions->has((string) $name)) {
                    return $source;
                }
            }
        }

        throw new SourceNotFound(sprintf(
            'Class "%s" not found',
            (string) $name
        ));
    }
}
