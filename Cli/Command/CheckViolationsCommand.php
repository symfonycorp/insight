<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Gitonomy\Git\Repository as GitRepository;
use Gitonomy\Git\RevisionList as GitRevisionList;

class CheckViolationsCommand extends Command implements NeedConfigurationInterface
{
    protected function configure()
    {
        $this
            ->setName('check-violations')
            ->addArgument('project-uuid', InputArgument::REQUIRED, 'SensioLabs insight project UUID')
            ->addOption('repository'    , null, InputOption::VALUE_REQUIRED, 'The GIT repository path, default to current working directory', getcwd())
            ->addOption('commits'       , null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'List of commits')
            ->addOption('no-global'     , null, InputOption::VALUE_NONE, 'Show global violations', null)
            ->addOption('with-assets'   , null, InputOption::VALUE_NONE, 'Ignore assets violations(css, js, coffee, less, sass)', null)
            ->setDescription('Check the last commit or between given commits for SensioLabs insight violations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectUuid = $input->getArgument('project-uuid');
        $api = $this->getApplication()->getApi();
        $gitRepository = new GitRepository($input->getOption('repository'));

        if ($gitRepository->isBare()) {
            throw new \Exception(
                sprintf('No Git repository found on this path "%s"', $input->getOption('repository'))
            );
        }

        $output->writeln(sprintf('<info>Found GIT repository in <%s></info>', $gitRepository->getGitDir()));

        if ($input->getOption('commits')) {
            $revisionsList = new GitRevisionList($gitRepository, $input->getOption('commits'));
            $diff = $gitRepository->getDiff($revisionsList);

        } else {
            $diff = $gitRepository->getHeadCommit()->getDiff();
        }

        if (!$diff) {
            $output->writeln('<info>No diff found.</info>');

            return;
        }

        $changedFiles = array();
        foreach ($diff->getFiles() as $file) {
            if ($file->getNewName()) {
                if (!$input->getOption('with-assets') && $this->isAsset($file->getNewName())) {
                    continue;
                }

                $changedFiles[] = $file->getNewName();
            }
        }

        $output->writeln(sprintf('<info>Found [%d] changed files</info>', count($changedFiles)));
        $output->writeln(sprintf('<info>Get violations from latest SensioLabs insight analysis...</info>', count($changedFiles)));

        $senioInsightAnalysis = $api->getProject($input->getArgument('project-uuid'))->getLastAnalysis();
        $analysisLinkPage = sprintf('%s/projects/%s/analyses/%d', $api::ENDPOINT, $projectUuid, $senioInsightAnalysis->getNumber());

        $output->writeln(
            sprintf('<info>Last analysis number is [%d], link page <%s></info>', $senioInsightAnalysis->getNumber(), $analysisLinkPage)
        );

        if (!($senioInsightAnalysis->getEndAt() instanceof \DateTime)) {
            $output->writeln(
                sprintf(
                    '<bg=yellow;fg=red>The analysis number [%d], started at [%s] is not yet finished</bg=yellow;fg=red>',
                    $senioInsightAnalysis->getNumber(),
                    $senioInsightAnalysis->getBeginAt()->format('d D Y, H:i:s')
                )
            );

            return;
        }

        if (!$senioInsightAnalysis->getViolations()) {
            $output->writeln(
                sprintf('<info>No violations found in the last analysis [%d]</info>', $senioInsightAnalysis->getNumber())
            );

            return;
        }

        $violationsByResources = array();
        $globalViolations = array();
        foreach ($senioInsightAnalysis->getViolations() as $violation) {
            if ('' == $violation->getResource()) {
                $globalViolations[] = $violation->getMessage();
                continue;
            }

            if (!$input->getOption('with-assets') && $this->isAsset($violation->getResource())) {
                continue;
            }

            if (in_array($violation->getResource(), $changedFiles)) {
                if (!isset($violationsByResources[$violation->getResource()])) {
                    $violationsByResources[$violation->getResource()] = array();
                }

                $violationsByResources[$violation->getResource()][] = $violation->getMessage();
            }
        }

        if (!count($violationsByResources)) {
            $output->writeln(
                sprintf('<info>No violations found in the last analysis [%d]</info>', $senioInsightAnalysis->getNumber())
            );

            return;
        }

        foreach ($violationsByResources as $resource => $violations) {
            $output->writeln(sprintf("\r\n<bg=red>%s</bg=red>", $resource));
            foreach ($violations as $violationIndex => $violation) {
                $output->writeln(sprintf('<fg=yellow>    %d > %s</fg=yellow>', $violationIndex + 1, $violation));
            }
        }

        if (!$input->getOption('no-global')) {
            $output->writeln(sprintf("\r\n<bg=red>Global Violations</bg=red>", $resource));

            foreach ($globalViolations as $violationIndex => $violation) {
                $output->writeln(sprintf('<fg=yellow>    %d > %s</fg=yellow>', $violationIndex + 1, $violation));
            }
        }
    }

    private function isAsset($filename)
    {
        return preg_match('/\.(css|js|coffee|less|sass)/i', $filename);
    }
}
