<?php

namespace SensioLabs\Insight\Cli\Descriptor;

use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\Violation;

class MarkdownDescriptor extends AbstractDescriptor
{
    protected function describeAnalysis(Analysis $argument, array $options = [])
    {
        $lines = [
            '| Title | Category | Severity | Message | File | Line |',
            '|-------|----------|----------|---------|------|------|',
        ];

        foreach ($argument->getViolations() as $violation) {
            $lines[] = $this->buildRow($violation);
        }

        $table = implode("\n", $lines);
        $options['output']->writeln($table);
    }

    private function buildRow(Violation $violation): string
    {
        $title = $this->escape($violation->getTitle() ?? $violation->getMessage() ?? 'N/A');
        $category = $violation->getCategory() ?? 'N/A';
        $severity = $violation->getSeverity() ?? 'N/A';
        $message = $this->escape($violation->getMessage() ?? 'N/A');
        $file = \is_string($violation->getResource()) ? $violation->getResource() : 'N/A';
        $line = $violation->getLine() ?: 'N/A';

        return sprintf('| %s | %s | %s | %s | %s | %s |', $title, $category, $severity, $message, $file, $line);
    }

    private function escape(string $text): string
    {
        return str_replace(['|', "\r", "\n"], ['/', ' ', ' '], $text);
    }
}
