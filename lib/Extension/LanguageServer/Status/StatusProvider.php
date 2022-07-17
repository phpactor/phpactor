<?php

namespace Phpactor\Extension\LanguageServer\Status;

interface StatusProvider
{
    public function title(): string;
    /**
     * Return key => value status report
     *
     * @return array<string,string>
     */
    public function provide(): array;
}
