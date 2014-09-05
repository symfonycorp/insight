<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Command;

use SensioLabs\Insight\Cli\Helper\DescriptorHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AnalysisCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('analysis')
            ->addArgument('project-uuid', InputArgument::REQUIRED)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'To output in other formats', 'txt')
            ->addOption('fail-condition', null, InputOption::VALUE_REQUIRED, '')
            ->setDescription('Show the last project analysis')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getApplication()->getApi();
        $projectUuid = $input->getArgument('project-uuid');

        $analysis = $api->getProject($projectUuid)->getLastAnalysis();

        if (!$analysis) {
            $output->writeln('<error>There are no analyses</error>');

            return 1;
        }

        $status = $api->getAnalysisStatus($projectUuid, $analysis->getNumber());
        $api->getMergerAnalysis()->merge($status, $analysis);

        $helper = new DescriptorHelper($api->getSerializer());
        $helper->describe($output, $analysis, $input->getOption('format'));

        if ('txt' === $input->getOption('format') && OutputInterface::VERBOSITY_VERBOSE > $output->getVerbosity()) {
            $output->writeln('');
            $output->writeln('Re-run this command with <comment>-v</comment> option to get the full report');
        }

        if (!$expr = $input->getOption('fail-condition')) {
            return;
        }

        $el = new ExpressionLanguage();
        $counts = array();
        foreach ($analysis->getViolations() as $violation) {
            if (!isset($counts[$violation->getCategory()])) {
                $counts[$violation->getCategory()] = 0;
            }
            ++$counts[$violation->getCategory()];

            if (!isset($counts[$violation->getSeverity()])) {
                $counts[$violation->getSeverity()] = 0;
            }
            ++$counts[$violation->getSeverity()];
        };
        $vars = array(
           'analysis' => $analysis,
           'counts'   => (object) $counts,
        );

        if ($el->evaluate($expr, $vars)) {
            return 70;
        }
    }
}
