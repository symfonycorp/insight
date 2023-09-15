<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectsCommand extends Command implements NeedConfigurationInterface
{
    protected function configure(): void
    {
        $this
            ->setName('projects')
            ->setDescription('List all your projects')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $api = $this->getApplication()->getApi();

        $projectsResource = $api->getProjects();
        $projects = $projectsResource->getProjects();
        $nbPage = ceil($projectsResource->getTotal() / 10);
        $page = 1;
        while ($page < $nbPage) {
            ++$page;
            $projects = array_merge($projects, $api->getProjects($page)->getProjects());
        }

        if (!$projects) {
            $output->writeln('There are no projects');
        }

        $rows = [];
        foreach ($projects as $project) {
            if ($project->getLastAnalysis()) {
                $grade = $project->getLastAnalysis()->getGrade();
            } else {
                $grade = 'This project has no analyses';
            }
            $rows[] = [$project->getName(), $project->getUuid(), $grade];
        }

        $table = new Table($output);
        $table->setHeaders(['name', 'uuid', 'grade'])
            ->setRows($rows)
            ->render()
        ;

        return 0;
    }
}
