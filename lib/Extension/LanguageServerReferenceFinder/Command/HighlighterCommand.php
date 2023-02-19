<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Command;

use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\TextDocument\ByteOffset;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Amp\Promise\wait;

class HighlighterCommand extends Command
{
    public const NAME = 'language-server:highlights';
    public const OFFSET = 'offset';

    public function __construct(private Highlighter $highlighter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Internal: resolve LSP highlights in JSON for document provided over STDIN');
        $this->addArgument(self::OFFSET, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var int $offset */
        $offset = $input->getArgument(self::OFFSET);

        $highlights = wait($this->highlighter->highlightsFor($this->stdin(), ByteOffset::fromInt($offset)));
        $decoded = json_encode(iterator_to_array($highlights));
        if (false === $decoded) {
            throw new RuntimeException(
                'Could not encode highlight',
            );
        }
        $output->write($decoded);
        return 0;
    }

    private function stdin(): string
    {
        $in = '';

        while (false !== $line = fgets(STDIN)) {
            $in .= $line;
        }

        return $in;
    }
}
