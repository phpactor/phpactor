<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class SourceCode extends Prototype
{
    /**
     * @var NamespaceName
     */
    private $namespace;

    /**
     * @var UseStatements
     */
    private $useStatements;

    /**
     * @var Classes
     */
    private $classes;

    /**
     * @var Interfaces
     */
    private $interfaces;

    /**
     * @var Traits
     */
    private $traits;

    public function __construct(
        NamespaceName $namespace = null,
        UseStatements $useStatements = null,
        Classes $classes = null,
        Interfaces $interfaces = null,
        Traits $traits = null,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: UseStatements::empty();
        $this->classes = $classes ?: Classes::empty();
        $this->interfaces = $interfaces ?: Interfaces::empty();
        $this->traits = $traits ?: Traits::empty();
    }

    public function namespace(): NamespaceName
    {
        return $this->namespace;
    }

    public function useStatements(): UseStatements
    {
        return $this->useStatements;
    }

    public function classes(): Classes
    {
        return $this->classes;
    }

    public function interfaces(): Interfaces
    {
        return $this->interfaces;
    }

    public function traits(): Traits
    {
        return $this->traits;
    }
}
