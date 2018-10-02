<?php

namespace SymfonyCorp\Insight\Cli\Descriptor;

use SymfonyCorp\Insight\Sdk\Model\Analysis;
use SymfonyCorp\Insight\Sdk\Model\Violation;

class PmdDescriptor extends AbstractDescriptor
{
    const PHPMD_PRIORITY_HIGH = 1;
    const PHPMD_PRIORITY_MEDIUM_HIGH = 2;
    const PHPMD_PRIORITY_MEDIUM = 3;
    const PHPMD_PRIORITY_MEDIUM_LOW = 4;
    const PHPMD_PRIORITY_LOW = 5;

    protected function describeAnalysis(Analysis $analysis, array $options = array())
    {
        $output = $options['output'];

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xpath = new \DOMXPath($xml);

        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = true;

        $pmd = $xml->createElement('pmd');
        $pmd->setAttribute('timestamp', $analysis->getEndAt()->format('c'));

        $xml->appendChild($pmd);

        $violations = $analysis->getViolations();
        if ($violations) {
            foreach ($violations as $violation) {
                /*
                 * @var $violation \SymfonyCorp\Insight\Sdk\Model\Violation
                 */
                $filename = $violation->getResource();

                $nodes = $xpath->query(sprintf('//file[@name="%s"]', $filename));

                if ($nodes->length > 0) {
                    $node = $nodes->item(0);
                } else {
                    $node = $xml->createElement('file');
                    $node->setAttribute('name', $filename);

                    $pmd->appendChild($node);
                }

                $violationNode = $xml->createElement('violation', $violation->getMessage());
                $node->appendChild($violationNode);

                $violationNode->setAttribute('beginline', $violation->getLine());
                $violationNode->setAttribute('endline', $violation->getLine());
                $violationNode->setAttribute('rule', $violation->getTitle());
                $violationNode->setAttribute('ruleset', $violation->getCategory());
                $violationNode->setAttribute('priority', $this->getPriority($violation));
            }
        }

        $output->writeln($xml->saveXML());
    }

    private function getPriority(Violation $violation)
    {
        switch ($violation->getSeverity()) {
            case 'critical':
                return self::PHPMD_PRIORITY_HIGH;

            case 'major':
                return self::PHPMD_PRIORITY_MEDIUM;

            default:
                return self::PHPMD_PRIORITY_LOW;
        }
    }
}
