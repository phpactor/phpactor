<?php

namespace Phpactor\Extension\ObjectRenderer;

use Closure;
use Phpactor\ObjectRenderer\Adapter\Twig\TwigObjectRenderer;
use Phpactor\ObjectRenderer\Model\ObjectRenderer;
use Phpactor\ObjectRenderer\Model\ObjectRenderer\TolerantObjectRenderer;
use Phpactor\ObjectRenderer\Model\TemplateCandidateProvider;
use Phpactor\ObjectRenderer\Model\TemplateProvider\AncestoralClassTemplateProvider;
use Phpactor\ObjectRenderer\Model\TemplateProvider\ClassNameTemplateProvider;
use Phpactor\ObjectRenderer\Model\TemplateProvider\InterfaceTemplateProvider;
use Phpactor\ObjectRenderer\Model\TemplateProvider\SuffixAppendingTemplateProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Object Renderer builder.
 *
 * A new instance of the builder is returned for each modification.
 */
final class ObjectRendererBuilder
{
    /**
     * @var array<string>
     */
    private array $templatePaths = [];

    private string $suffix = '.twig';

    private bool $renderEmptyOnNotFound = false;

    private LoggerInterface $logger;

    /**
     * @var bool|string|callable
     */
    private mixed $escaping = false;

    private bool $enableAncestoralCandidates = false;

    private bool $enableInterfaceCandidates = false;

    /**
     * @var ?Closure(Environment): Environment
     */
    private ?Closure $twigConfigurator;

    private function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * Create a new instance of the builder.
     * Call build() to create a new ObejctRenderer.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * When renderEmptyOnNotFound() is set, use this
     * logger to log template not found messages.
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $new = clone $this;
        $new->logger = $logger;

        return $new;
    }

    /**
     * Suffix of the twig files, `.twig` by default
     */
    public function setTemplateSuffix(string $suffix): self
    {
        $new = clone $this;
        $new->suffix = $suffix;

        return $new;
    }

    /**
     * Add a template path. Can be called multiple times.
     */
    public function addTemplatePath(string $path): self
    {
        $new = clone $this;
        $new->templatePaths[] = $path;

        return $new;
    }

    /**
     * Set the Twig escaping strategy:
     *
     * - false: disable auto-escaping
     * - html, js: set the autoescaping to one of the supported strategies
     * - name: set the autoescaping strategy based on the template name extension
     * - PHP callback: a PHP callback that returns an escaping strategy based on the template "name"
     *
     * @param bool|string|callable $escaping
     */
    public function setEscaping($escaping): self
    {
        $new = clone $this;
        $new->escaping = $escaping;

        return $new;
    }

    /**
     * Instead of throwing an exception when a template is not found, return
     * empty. If a logger is provided, via. setLogger, log the exception
     * message.
     */
    public function renderEmptyOnNotFound(): self
    {
        $new = clone $this;
        $new->renderEmptyOnNotFound = true;

        return $new;
    }

    /**
     * Determine templates from the class of the current object and then the
     * class of each of its ancestors.
     */
    public function enableAncestoralCandidates(): self
    {
        $new = clone $this;
        $new->enableAncestoralCandidates = true;

        return $new;
    }

    /**
     * Determine templates from the class of the current object and then the
     * class of each of its ancestors.
     */
    public function enableInterfaceCandidates(): self
    {
        $new = clone $this;
        $new->enableInterfaceCandidates = true;

        return $new;
    }

    /**
     * Build the object renderer
     */
    public function build(): ObjectRenderer
    {
        return $this->buildRenderer();
    }

    /**
     * @param null|Closure(Environment): Environment $configurator
     */
    public function configureTwig(?Closure $configurator): ObjectRendererBuilder
    {
        $this->twigConfigurator = $configurator;

        return $this;
    }

    private function buildRenderer(): ObjectRenderer
    {
        $renderer = new TwigObjectRenderer(
            $this->buildTwig(),
            $this->buildTemplateProvider()
        );

        if ($this->renderEmptyOnNotFound) {
            $renderer = new TolerantObjectRenderer($renderer, $this->logger);
        }

        return $renderer;
    }

    private function buildTwig(): Environment
    {
        $env = new Environment(
            new FilesystemLoader($this->templatePaths),
            [
                'autoescape' => $this->escaping,
                'strict_variables' => true,
            ]
        );

        if ($this->twigConfigurator) {
            return $this->twigConfigurator->__invoke($env);
        }

        return $env;
    }

    private function buildTemplateProvider(): TemplateCandidateProvider
    {
        $provider = new ClassNameTemplateProvider();

        if ($this->enableAncestoralCandidates) {
            $provider = new AncestoralClassTemplateProvider($provider);
        }

        if ($this->enableInterfaceCandidates) {
            $provider = new InterfaceTemplateProvider($provider);
        }

        $provider = new SuffixAppendingTemplateProvider($provider, $this->suffix);

        return $provider;
    }
}
