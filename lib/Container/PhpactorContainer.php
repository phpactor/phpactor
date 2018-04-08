<?php

namespace Phpactor\Container;

use Pimple\Container as InnerPimpleContainer;
use Closure;
use InvalidArgumentException;
use RuntimeException;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;

class PhpactorContainer implements Container, ContainerBuilder
{
    /**
     * @var array
     */
    private $tags;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $factories;

    /**
     * @var array
     */
    private $services;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new RuntimeException(sprintf(
                'No service with ID "%s" exists',
                $id
            ));
        }

        $this->services[$id] = $this->factories[$id]($this);

        return $this->services[$id];
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return isset($this->services[$id]);
    }

    public function getServiceIdsForTag(string $tag): array
    {
        if (false === isset($this->tags[$tag])) {
            return [];
        }

        return $this->tags[$tag];
    }

    public function register(string $serviceId, Closure $factory, array $tags = [])
    {
        $this->factories[$serviceId] = $factory;

        foreach ($tags as $tagName => $tagAttrs) {

            if (false === isset($this->tags[$tagName])) {
                $this->tags[$tagName] = [];
            }

            $this->tags[$tagName][$serviceId] = $tagAttrs;
        }
    }

    public function getParameter(string $name)
    {
        if (!isset($this->parameters[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown parameter "%s", known parameters "%s"',
                $name, implode('", "', array_keys($this->parameters))
            ));
        }

        return $this->parameters[$name];
    }

    public function build(array $parameters): Container
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
