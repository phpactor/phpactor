<?php

namespace Phpactor\Search\Adapter\Symfony;

use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchRenderer;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

final class ConsoleMatchRenderer implements MatchRenderer
{
    private OutputInterface $output;

    private string $cwd;


    public function __construct(OutputInterface $output, string $cwd)
    {
        $this->output = $output;
        $this->cwd = $cwd;
    }

    public function render(DocumentMatches $matches): void
    {
        $document = $matches->document();
        foreach ($matches as $match) {
            $this->output->write(sprintf(
                '%s:',
                Path::makeRelative(
                    $document->uri()->path(),
                    $this->cwd
                )
            ));

            $startLineCol = LineCol::fromByteOffset($document, $match->range()->start());
            $endLineCol = LineCol::fromByteOffset($document, $match->range()->end());

            $highlighted = $this->highlight($document->__toString(), $match);
            $output = (sprintf(
                '%d:%d,%d:%d %s',
                $startLineCol->line(),
                $startLineCol->col(),
                $endLineCol->line(),
                $endLineCol->col(),
                substr(
                    $highlighted,
                    (new LineCol($startLineCol->line(), 1))->toByteOffset($highlighted)->toInt()
                )
            ));

            $output = str_replace("\n", ' ', $output);

            $this->output->writeln($output);
        }
    }

    public function highlight(string $document, PatternMatch $match): string
    {
        $endLineCol = LineCol::fromByteOffset($document, $match->range()->end());
        $endLineCol = new LineCol($endLineCol->line() + 1, 1);
        $document = substr($document, 0, $endLineCol->toByteOffset($document)->toInt());
        $secret = uniqid();
        $edits = [
            TextEdit::create($match->range()->start()->toInt(), 0, sprintf('__%s_gray__', $secret)),
        ];
        $colors = [ 'yellow', 'green', 'blue', 'cyan' ];
        $placeholderColors = [];
        foreach ($match->tokens()->placeholders() as $index => $placeholder) {
            $placeholderColors[$placeholder] = $colors[$index % count($colors)];
        }

        foreach ($match->tokens() as $placeholder => $token) {
            $edits[] = TextEdit::create(
                $token->range->start(),
                $token->range->length(),
                sprintf(
                    '__%s_%s__%s__%sEND__',
                    $secret,
                    $placeholderColors[$placeholder],
                    $token->text,
                    $secret
                )
            );
        }

        $edits[] = TextEdit::create($match->range()->end()->toInt(), 0, '__' . $secret . 'END__');

        $document = TextEdits::fromTextEdits($edits)->apply($document);

        // it seems that `\` is not escaped by the output formatter and
        // namespace `\` escapes the colorisation tags.
        $slashToken = '__SLASH_'. $secret;
        $document = str_replace('\\', $slashToken, $document);

        $document = OutputFormatter::escape($document);

        $document = preg_replace_callback('{__' . $secret . '_([a-z]+)__}', function (array $matches) {
            return sprintf('<fg=%s>', $matches[1]);
        }, $document);

        $document = str_replace('__' . $secret . 'END__', '</>', $document);

        // replace the backslash with a unicode backslash to avoid above
        // mentioned escaping bug.
        $document = str_replace($slashToken, '', $document);

        $document .= '</>';

        return $document;
    }
}
