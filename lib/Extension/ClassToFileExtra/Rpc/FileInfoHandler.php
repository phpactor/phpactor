<?php

namespace Phpactor\Extension\ClassToFileExtra\Rpc;

use Phpactor\Extension\ClassToFileExtra\Application\FileInfo;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\MapResolver\Resolver;

class FileInfoHandler extends AbstractHandler
{
    const NAME = 'file_info';
    const PARAM_PATH = 'path';

    public function __construct(private readonly FileInfo $fileInfo)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        $fileInfo = $this->fileInfo->infoForFile($arguments[self::PARAM_PATH]);

        return ReturnResponse::fromValue($fileInfo);
    }
}
