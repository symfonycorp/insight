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
            ->addOption('commits'       , null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'List of commits hashes')
            ->addOption('no-global'     , null, InputOption::VALUE_NONE, 'Disable showing global violations', null)
            ->addOption('ignore-assets' , null, InputOption::VALUE_NONE, 'Ignore assets violations(*.css, *.js, *.coffee, *.less, *.sass)', null)
            ->setDescription('Check for violations occurred in one or multiple commits in the last analysis.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectUuid = $input->getArgument('project-uuid');
        $ignoreAssets = $input->getOption('ignore-assets');
        $noGlobalViolations = $input->getOption('no-global');

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
                if ($ignoreAssets && $this->isAsset($file->getNewName())) {
                    continue;
                }

                $changedFiles[] = $file->getNewName();
            }
        }

        $output->writeln(sprintf('<info>Found [%d] changed files</info>', count($changedFiles)));
        $output->writeln(sprintf('<info>Get violations from latest SensioLabs insight analysis...</info>', count($changedFiles)));

        $sensioInsightAnalysis = $api->getProject($input->getArgument('project-uuid'))->getLastAnalysis();
        $analysisLinkPage = sprintf('%s/projects/%s/analyses/%d', $api::ENDPOINT, $projectUuid, $sensioInsightAnalysis->getNumber());

        $output->writeln(
            sprintf('<info>Last analysis number is [%d], link page <%s></info>', $sensioInsightAnalysis->getNumber(), $analysisLinkPage)
        );

        if (null === $sensioInsightAnalysis->getEndAt()) {
            $output->writeln(
                sprintf(
                    '<error>The analysis number [%d], started at [%s] is not yet finished</error>',
                    $sensioInsightAnalysis->getNumber(),
                    $sensioInsightAnalysis->getBeginAt()->format('d D Y, H:i:s')
                )
            );

            return 1;
        }

        if (!$sensioInsightAnalysis->getViolations()) {
            $output->writeln(
                sprintf('<info>No violations found in the last analysis [%d]</info>', $sensioInsightAnalysis->getNumber())
            );

            return;
        }

        $violationsByResources = array();
        $globalViolations = array();
        foreach ($sensioInsightAnalysis->getViolations() as $violation) {
            if (!$violation->getResource()) {
                $globalViolations[] = $violation->getMessage();
                continue;
            }

            if (in_array($violation->getResource(), $changedFiles)) {
                $violationsByResources[$violation->getResource()][] = $violation->getMessage();
            }
        }

        if (!$violationsByResources && !$globalViolations) {
            $output->writeln(
                sprintf('<info>No violations found in the last analysis [%d]</info>', $sensioInsightAnalysis->getNumber())
            );

            return;
        }

        foreach ($violationsByResources as $resource => $violations) {
            $output->writeln(sprintf("\r\n<error>%s</error>", $resource));
            foreach ($violations as $violationIndex => $violation) {
                $output->writeln(sprintf('<fg=yellow>    %d > %s</fg=yellow>', $violationIndex + 1, $violation));
            }
        }

        if (!$noGlobalViolations && $globalViolations) {
            $output->writeln(sprintf("\r\n<error>Global Violations</error>", $resource));

            foreach ($globalViolations as $violationIndex => $violation) {
                $output->writeln(sprintf('<fg=yellow>    %d > %s</fg=yellow>', $violationIndex + 1, $violation));
            }
        }
    }

    private function isAsset($filename)
    {
        return preg_match('/\.(css|js|coffee|less|sass)$/i', $filename);
    }
}
