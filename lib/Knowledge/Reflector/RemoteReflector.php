<?php

namespace PhpActor\Knowledge\Reflector;

use Symfony\Component\Process\Process;
use PhpActor\Knowledge\Reflection\ReflectorInterface;
use PhpActor\Knowledge\Reflection\ClassHierarchy;
use PhpActor\Knowledge\Reflection\ClassReflection;
use PhpActor\Knowledge\Reflection\MethodReflection;
use PhpActor\Knowledge\Reflection\ParamReflection;

class RemoteReflector implements ReflectorInterface
{
    public function reflect(string $file, string $bootstrap = null): ClassHierarchy
    {
        $script = <<<EOT
<?php
\$bootstrap = '$bootstrap';
\$file = '$file';
if (\$bootstrap) {
    require_once(\$bootstrap);
}

\$originalClasses = get_declared_classes();
require(\$file);
\$classes = array_diff(get_declared_classes(), \$originalClasses);

\$reflections = array();

\$classMetas = array();
foreach (\$classes as \$class) {
    \$reflection = new ReflectionClass(\$class);
    \$classMeta = array();
    \$classMeta['name'] = \$reflection->getName();
    \$classMeta['short_name'] = \$reflection->getShortName();
    \$classMeta['doc'] = \$reflection->getDocComment();
    \$classMeta['namespace'] = \$reflection->getNamespaceName();
    if (\$reflection->getParentClass()) {
        \$classMeta['parent'] = \$reflection->getParentClass()->getName();
    }
    \$classMeta['methods'] = array();
    \$classMeta['file'] = \$reflection->getFileName();

    foreach (\$reflection->getMethods() as \$method) {
        \$methodMeta = array(
            'name' => \$method->getName(),
            'doc' => \$method->getDocComment()
        );

        \$methodMeta['params'] = array();
        foreach (\$method->getParameters() as \$param) {
            \$methodMeta['params'][] = array(
                'name' => \$param->getName(),
                'class' => \$param->getClass() ? \$param->getClass()->getName() : null
            );
        }

        \$classMeta['methods'][] = \$methodMeta;
    }
    \$classMetas[] = \$classMeta;
}

echo json_encode(\$classMetas);
exit(0);
EOT
        ;
        $tmpName = tempnam(sys_get_temp_dir(), 'phpfactor_reflection');
        file_put_contents($tmpName, $script);

        $process = new Process(PHP_BINARY . ' ' . $tmpName);
        $process->run();

        if (false === $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Error processing file "%s": %s %s',
                $file,
                $process->getErrorOutput(),
                $process->getOutput()
            ));
        }

        $output = $process->getOutput();

        if (!$output) {
            return null;
        }

        $data = json_decode($output, true);

        if (null === $data) {
            throw new \RuntimeException(sprintf(
                'Error decoding reflector output for file "%s": %s',
                $file,
                $output
            ));
        }

        return $this->hydrateReflection($data);
    }

    private function hydrateReflection(array $data): ClassHierarchy
    {
        $hierarchy = new ClassHierarchy();

        foreach ($data as $classData) {
            $class = new ClassReflection(
                $classData['name'],
                $classData['short_name'],
                $classData['doc'],
                $classData['namespace'],
                $classData['file'],
                isset($data['parent']) ? $data['parent'] : null
            );

            foreach ($classData['methods'] as $methodData) {
                $method = new MethodReflection(
                    $methodData['name'],
                    $methodData['doc']
                );

                foreach ($methodData['params'] as $paramData) {
                    $param = new ParamReflection(
                        $paramData['name']
                    );
                    $method->addParam($param);
                }

                $class->addMethod($method);
            }

            $hierarchy->addClass($class);
        }

        return $hierarchy;
    }
}
