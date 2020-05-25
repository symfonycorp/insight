<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Command;

use SensioLabs\Insight\Cli\Helper\DescriptorHelper;
use SensioLabs\Insight\Sdk\Api;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnalyzeCommand extends Command implements NeedConfigurationInterface
{
    protected function configure()
    {
        $this
            ->setName('analyze')
            ->addArgument('project-uuid', InputArgument::REQUIRED)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'To output in other formats', 'txt')
            ->addOption('reference', null, InputOption::VALUE_REQUIRED, 'The git reference to analyze')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'The analysis current branch')
            ->addOption('show-ignored-violations', null, InputOption::VALUE_NONE, 'Show ignored violations')
            ->addOption('fail-condition', null, InputOption::VALUE_REQUIRED, '')
            ->setDescription('Analyze a project')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectUuid = $input->getArgument('project-uuid');

        /** @var Api $api */
        $api = $this->getApplication()->getApi();

        if (false !== strpos($api->getBaseUrl(), '.sensiolabs.com')) {
            $io = new SymfonyStyle($input, $output);
            $io->warning('You are using the legacy URL of SymfonyInsight which may stop working in the future. You should reconfigure this tool by running the "configure" command and use "https://insight.symfony.com" as endpoint.');
        }

        $analysis = $api->analyze($projectUuid, $input->getOption('reference'), $input->getOption('branch'));

        $chars = ['-', '\\', '|', '/'];
        $noAnsiStatus = 'Analysis queued';
        $output->getErrorOutput()->writeln($noAnsiStatus);

        $position = 0;

        while (true) {
            // we don't check the status too often
            if (0 === $position % 2) {
                $analysis = $api->getAnalysisStatus($projectUuid, $analysis->getNumber());
            }

            if ('txt' === $input->getOption('format')) {
                if (!$output->isDecorated()) {
                    if ($noAnsiStatus !== $analysis->getStatusMessage()) {
                        $output->writeln($noAnsiStatus = $analysis->getStatusMessage());
                    }
                } else {
                    $output->write(sprintf("%s %-80s\r", $chars[$position % 4], $analysis->getStatusMessage()));
                }
            }

            if ($analysis->isFinished()) {
                break;
            }

            usleep(200000);

            ++$position;
        }

        $analysis = $api->getAnalysis($projectUuid, $analysis->getNumber());
        if ($analysis->isFailed()) {
            $output->writeln(sprintf('There was an error: "%s"', $analysis->getFailureMessage()));

            return 1;
        }

        $helper = new DescriptorHelper($api->getSerializer());
        $helper->describe($output, $analysis, $input->getOption('format'), $input->getOption('show-ignored-violations'));

        if ('txt' === $input->getOption('format') && OutputInterface::VERBOSITY_VERBOSE > $output->getVerbosity()) {
            $output->writeln('');
            $output->writeln(sprintf('Run <comment>%s %s %s -v</comment> to get the full report', $_SERVER['PHP_SELF'], 'analysis', $projectUuid));
        }

        if (!$expr = $input->getOption('fail-condition')) {
            return;
        }

        return $this->getHelperSet()->get('fail_condition')->evaluate($analysis, $expr);
    }
}
