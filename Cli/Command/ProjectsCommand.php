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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectsCommand extends Command implements NeedConfigurationInterface
{
    protected function configure()
    {
        $this
            ->setName('projects')
            ->setDescription('List all your projects')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getApplication()->getApi();

        $projectsResource = $api->getProjects();
        $projects = $projectsResource->getProjects();
        $nbPage = ceil($projectsResource->getTotal() / 10);
        $page = 1;
        while ($page < $nbPage) {
            $page++;
            $projects = array_merge($projects, $api->getProjects($page)->getProjects());
        }

        if (!$projects) {
            $output->writeln('There are no projects');
        }

        $rows = array();
        foreach ($projects as $project) {
            if ($project->getLastAnalysis()) {
                $lastAnalysis = $project->getLastAnalysis()->getGrade();
            } else {
                $lastAnalysis = 'This project has no analyses';
            }
            $rows[] = array($project->getName(), $project->getUuid(), $lastAnalysis);
        }

        $this
            ->getHelperSet()->get('table')
            ->setHeaders(array('name', 'uuid', 'grade of last analysis'))
            ->setRows($rows)
            ->render($output)
        ;
    }
}
