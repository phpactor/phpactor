<?php

namespace Phpactor\Extension\LanguageServer\Status;

interface StatusProvider
{
    public function title(): string;
    /**
     * Return key => value status report
     *
     * @return array<string,string|int>
     */
    public function provide(): array;
}
