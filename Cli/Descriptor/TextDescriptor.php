<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Cli\Descriptor;

use SymfonyCorp\Insight\Sdk\Model\Analysis;
use Symfony\Component\Console\Output\OutputInterface;

class TextDescriptor extends AbstractDescriptor
{
    protected function describeAnalysis(Analysis $analysis, array $options = array())
    {
        $output = $options['output'];
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
            $output->write(sprintf('Began at: <comment>%s</comment>', $analysis->getBeginAt()->format('Y-m-d h:i:s')));
        }
        if (!$analysis->isFinished()) {
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('');
            }
            $output->writeln('The analysis is not finished yet.');

            return;
        }
        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
            $output->write(sprintf(' Ended at: <comment>%s</comment>', $analysis->getEndAt()->format('Y-m-d h:i:s')));
            $output->writeln(sprintf(' Real duration: <comment>%s</comment>.', $analysis->getEndAt()->format('Y-m-d h:i:s')));
            $output->writeln('');
        }
        $output->write(sprintf(
            'The project has <comment>%d violations</comment>, it got the <comment>%s grade</comment>.',
            $analysis->getNbViolations(),
            $analysis->getGrade()
        ));

        $grades = $analysis->getGrades();
        $bestGrade = end($grades);
        if ($bestGrade == $analysis->getGrade()) {
            $output->writeln('');

            return;
        }

        $output->writeln(sprintf(
            ' <comment>%d hours</comment> to get the <comment>%s grade</comment> and %d hours to get the %s grade',
            $analysis->getRemediationCostForNextGrade(),
            $analysis->getNextGrade(),
            $analysis->getRemediationCost(),
            $bestGrade
        ));
        $output->writeln('');

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity() && $analysis->getViolations()) {
            $template = <<<EOL
Resource: <comment>{{ resource }}:{{ line }}</comment>
Category: <comment>{{ category }}</comment> Severity: <comment>{{ severity }}</comment>
Title:    <comment>{{ title }}</comment>{{ ignored }}
Message:  <comment>{{ message }}</comment>

EOL;
            foreach ($analysis->getViolations() as $violation) {
                $output->writeln(strtr($template, array(
                    '{{ resource }}' => $violation->getResource(),
                    '{{ line }}' => $violation->getLine(),
                    '{{ category }}' => $violation->getCategory(),
                    '{{ severity }}' => $violation->getSeverity(),
                    '{{ title }}' => $violation->getTitle(),
                    '{{ message }}' => $violation->getMessage(),
                    '{{ ignored }}' => $violation->isIgnored() ? ' (ignored)' : null,
                )));
            }
        }

        foreach ($analysis->getLinks() as $link) {
            if ('self' == $link->getRel() && 'text/html' == $link->getType()) {
                $output->writeln(sprintf('You can get the full report at <info>%s</info>', $link->getHref()));

                break;
            }
        }
    }
}
