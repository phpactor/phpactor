<?php

namespace Phpactor\Indexer\Extension\Command;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\SearchClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSearchCommand extends Command
{
    public const ARG_SEARCH = 'search';
    public const OPT_FQN_BEGINS = 'fqn-begins';
    public const OPT_SHORT_NAME_BEGINS = 'short-name-begins';
    public const OPT_SHORT_NAME = 'short-name';
    public const OPT_IS_FUNCTION = 'is-function';
    public const OPT_IS_CLASS = 'is-class';
    public const OPT_IS_MEMBER = 'is-member';
    public const OPT_IS_CONSTANT = 'is-constant';
    public const OPT_LIMIT = 'limit';

    private SearchClient $searchClient;

    public function __construct(SearchClient $searchClient)
    {
        parent::__construct();
        $this->searchClient = $searchClient;
    }

    protected function configure(): void
    {
        $this->setDescription('Search the index');

        $this->addOption(self::OPT_FQN_BEGINS, null, InputOption::VALUE_REQUIRED, 'FQN begins with');
        $this->addOption(self::OPT_SHORT_NAME_BEGINS, null, InputOption::VALUE_REQUIRED, 'Short-name begins with');
        $this->addOption(self::OPT_SHORT_NAME, null, InputOption::VALUE_REQUIRED, 'Exact short name');
        $this->addOption(self::OPT_IS_FUNCTION, null, InputOption::VALUE_NONE, 'Functions only');
        $this->addOption(self::OPT_IS_CONSTANT, null, InputOption::VALUE_NONE, 'Constants only');
        $this->addOption(self::OPT_IS_CLASS, null, InputOption::VALUE_NONE, 'Classes only');
        $this->addOption(self::OPT_LIMIT, 'l', InputOption::VALUE_REQUIRED, 'Limit number of results');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shortName = $input->getOption(self::OPT_SHORT_NAME);
        $shortNameBegins = $input->getOption(self::OPT_SHORT_NAME_BEGINS);
        $fqnBegins = $input->getOption(self::OPT_FQN_BEGINS);
        $isFunction = $input->getOption(self::OPT_IS_FUNCTION);
        $isConstant = $input->getOption(self::OPT_IS_CONSTANT);
        $isClass = $input->getOption(self::OPT_IS_CLASS);
        $limitRaw = $input->getOption(self::OPT_LIMIT);
        $limit = is_numeric($limitRaw) ? (int)$limitRaw : null;

        $criterias = [];

        if ($shortName) {
            $criterias[] = Criteria::exactShortName($shortName);
        }

        if ($shortNameBegins) {
            $criterias[] = Criteria::shortNameBeginsWith($shortNameBegins);
        }

        if ($fqnBegins) {
            $criterias[] = Criteria::fqnBeginsWith($fqnBegins);
        }

        if ($isFunction) {
            $criterias[] = Criteria::isFunction();
        }

        if ($isConstant) {
            $criterias[] = Criteria::isConstant();
        }

        if ($isClass) {
            $criterias[] = Criteria::isClass();
        }

        foreach ($this->searchClient->search(Criteria::and(...$criterias)) as $index => $result) {
            if ($limit && $index === $limit) {
                break;
            }
            $output->writeln(sprintf(
                '<comment>%s</> <fg=cyan>#</> %s%s',
                $result->recordType(),
                $result->identifier(),
                $result instanceof ClassRecord ? sprintf(' (%s)', $result->type()) : '',
            ));
        }

        return 0;
    }
}
