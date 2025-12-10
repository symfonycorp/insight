<?php

namespace SensioLabs\Insight\Cli\Formatter;

use SensioLabs\Insight\Sdk\Model\Violation;
use SensioLabs\Insight\Sdk\Model\Violations;

class MarkdownTableBuilder
{
    public static function build(Violations $violations): string
    {
        $lines = [
            '| Title | Category | Severity | Message | File | Line |',
            '|-------|----------|----------|---------|------|------|',
        ];

        foreach ($violations as $violation) {
            $lines[] = self::buildRow($violation);
        }

        return implode("\n", $lines);
    }

    private static function buildRow(Violation $violation): string
    {
        $title = self::escape($violation->getTitle() ?? $violation->getMessage() ?? 'N/A');
        $category = $violation->getCategory() ?? 'N/A';
        $severity = $violation->getSeverity() ?? 'N/A';
        $message = self::escape($violation->getMessage() ?? 'N/A');
        $file = \is_string($violation->getResource()) ? $violation->getResource() : 'N/A';
        $line = $violation->getLine() ?: 'N/A';

        return sprintf('| %s | %s | %s | %s | %s | %s |', $title, $category, $severity, $message, $file, $line);
    }

    private static function escape(string $text): string
    {
        return str_replace(['|', "\r", "\n"], ['/', ' ', ' '], $text);
    }
}
