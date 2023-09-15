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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('configure')
            ->setDescription('(Re-)Configure your credentials.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getHelperSet()->get('configuration')->updateConfigurationManually($input, $output);

        return 0;
    }
}
