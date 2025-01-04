<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class SourceCode extends Prototype
{
    private QualifiedName $namespace;

    private UseStatements $useStatements;

    private Classes $classes;

    private Interfaces $interfaces;

    private Traits $traits;

    private Enums $enums;

    public function __construct(
        ?QualifiedName $namespace = null,
        ?UseStatements $useStatements = null,
        ?Classes $classes = null,
        ?Interfaces $interfaces = null,
        ?Traits $traits = null,
        ?Enums $enums = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->namespace = $namespace ?: NamespaceName::fromString('');
        $this->useStatements = $useStatements ?: UseStatements::empty();
        $this->classes = $classes ?: Classes::empty();
        $this->interfaces = $interfaces ?: Interfaces::empty();
        $this->traits = $traits ?: Traits::empty();
        $this->enums = $enums ?: Enums::empty();
    }

    public function namespace(): QualifiedName
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

    public function enums(): Enums
    {
        return $this->enums;
    }
}
