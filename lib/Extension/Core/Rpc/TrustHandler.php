<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Extension\Core\Trust\Trust;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\MapResolver\Resolver;

final class TrustHandler implements Handler
{
    const NAME = 'trust';
    const PARAM_TRUST = 'trust';

    public function __construct(private Trust $status, private string $projectDir)
    {
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            self::PARAM_TRUST
        ]);
    }

    /**
     * @param array{trust:int} $arguments
     */
    public function handle(array $arguments): Response
    {
        $trust = (bool)$arguments['trust'];
        $this->status->setTrusted($this->projectDir, $trust);

        if ($trust) {
            return EchoResponse::fromMessage(sprintf('Project directory "%s" is trusted. Configuration will be loaded from it.', $this->projectDir));
        }
        return EchoResponse::fromMessage(sprintf('Project directory "%s" is not trusted. Configuration will not be loaded from it.', $this->projectDir));

    }

    public function name(): string
    {
        return self::NAME;
    }
}
