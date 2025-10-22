<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\Core\Trust\Trust;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Amp\call;

class ProjectConfigTrustListener implements ListenerProviderInterface
{
    const RESP_YES = 'Yes: I trust it. It\'s mine';
    const RESP_NO = 'No: don\'t load it and don\'t ask me again';
    const RESP_MAYBE = 'No: don\'t load it but ask again next time';


    /**
     * @param list<string> $projectConfigCandidates
     */
    public function __construct(
        private ClientApi $clientApi,
        private array $projectConfigCandidates,
        private Trust $trust,
    ) {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            return [[$this, 'handleTrustConfig']];
        }

        return [];
    }

    /**
     * @return Success<null>
     */
    public function handleTrustConfig(): Promise
    {
        return call(function () {
            foreach ($this->projectConfigCandidates as $path) {
                $dir = dirname($path);

                if (!file_exists($path)) {
                    continue;
                }

                // directory is trusted
                if ($this->trust->hasTrust($dir)) {
                    continue;
                }

                $yes = new MessageActionItem(self::RESP_YES);
                $no = new MessageActionItem(self::RESP_NO);
                $notSure = new MessageActionItem(self::RESP_MAYBE);
                $response = yield $this->clientApi->window()->showMessageRequest()->warning(
                    sprintf(
                        <<<'EOT'
                            Directory "%s" has a "%s" configuration file that could be used for arbitrary
                            code execution. Do you trust this file?
                            EOT,
                        $dir,
                        basename($path)
                    ),
                    $yes,
                    $no,
                    $notSure,
                );
                assert($response instanceof MessageActionItem);
                if ($response == $notSure) {
                    return new Success();
                }
                $trust = ($response == $yes ? true : false);

                $this->trust->setTrusted($dir, $trust);

                if (false === $trust) {
                    $this->clientApi->window()->showMessage()->info(sprintf(
                        'Config not trusted and will not be loaded. You can change this decision by editing "%s" or running `phpactor config:trust`',
                        $this->trust->path,
                    ));
                    return new Success();
                }

                $this->clientApi->window()->showMessage()->info(
                    'Config has been trusted. Restart the language server for changes to take affect'
                );
            }

            return new Success();
        });
    }
}
