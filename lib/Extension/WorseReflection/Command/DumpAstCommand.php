<?php

namespace Phpactor\Extension\WorseReflection\Command;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class DumpAstCommand extends Command
{
    const ARG_PATH = 'path';

    public function __construct(private Parser $parser)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Dump and profile the ast for a given file');
        $this->addArgument(self::ARG_PATH, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $path */
        $path = $input->getArgument(self::ARG_PATH);
        $contents = file_get_contents($path);
        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file %s',
                $path
            ));
        }
        $parseStart = microtime(true);
        $rootNode = $this->parser->parseSourceFile($contents);
        $parseEnd = microtime(true);

        $traveralStart = microtime(true);
        $out = '';
        $this->dump($output, $rootNode, $out);
        $traversalEnd = microtime(true);

        $output->writeln($out);
        $output->writeln('');
        $output->writeln(sprintf('Parsing time: %ss', number_format($parseEnd - $parseStart, 4)));
        $output->writeln(sprintf('Traversal time: %ss', number_format($traversalEnd - $traveralStart, 4)));

        return 0;
    }

    private function dump(OutputInterface $output, Node $node, string &$out, int $depth = 0): void
    {
        foreach ($node->getChildNodesAndTokens() as $child) {
            if ($child instanceof Node) {
                $out .= sprintf('<info><%s ↓%s></>', $child->getNodeKindName(), $depth);
                $this->dump($output, $child, $out, $depth + 1);
            }
            if ($child instanceof Token) {
                $out .= $child->getFullText($node->getFileContents());
            }
        }
    }
}
