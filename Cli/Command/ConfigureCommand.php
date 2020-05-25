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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('(Re-)Configure your credentials.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->get('configuration')->updateConfigurationManually($input, $output);
    }
}
