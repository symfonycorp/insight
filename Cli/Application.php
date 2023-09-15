<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SensioLabs\Insight\Cli\Command as LocalCommand;
use SensioLabs\Insight\Cli\Helper\ConfigurationHelper;
use SensioLabs\Insight\Cli\Helper\FailConditionHelper;
use SensioLabs\Insight\Sdk\Api;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyApplication
{
    const APPLICATION_NAME = 'SymfonyInsight CLI';
    const APPLICATION_VERSION = '1.7.4';

    private $api;
    private $apiConfig;
    private $logFile;

    public function __construct()
    {
        $this->apiConfig = [
            'base_url' => Api::ENDPOINT,
        ];

        parent::__construct(static::APPLICATION_NAME, static::APPLICATION_VERSION);
    }

    public function getApi()
    {
        if ($this->api) {
            return $this->api;
        }

        $config = $this->apiConfig;
        if (\array_key_exists('api_endpoint', $config)) {
            $config['base_url'] = $config['api_endpoint'];
        }

        $this->api = new Api($config);

        if ($this->logFile) {
            if (!class_exists('Monolog\Logger')) {
                throw new \InvalidArgumentException('You must include monolog if you want to log (run "composer install --dev")');
            }
            $logger = new Logger('insight');
            $logger->pushHandler(new StreamHandler($this->logFile, Logger::DEBUG));

            $this->api->setLogger($logger);
        }

        return $this->api;
    }

    public function getLongVersion(): string
    {
        $version = parent::getLongVersion().' by <comment>Symfony</comment>';
        $commit = '@git-commit@';

        if ('@'.'git-commit@' !== $commit) {
            $version .= ' ('.substr($commit, 0, 7).')';
        }

        return $version;
    }

    protected function getDefaultHelperSet(): HelperSet
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new ConfigurationHelper(Api::ENDPOINT));
        $helperSet->set(new FailConditionHelper());

        return $helperSet;
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption('api-token', null, InputOption::VALUE_REQUIRED, 'Your api token.'));
        $definition->addOption(new InputOption('user-uuid', null, InputOption::VALUE_REQUIRED, 'Your user uuid.'));
        $definition->addOption(new InputOption('api-endpoint', null, InputOption::VALUE_REQUIRED, 'The api endpoint.'));
        $definition->addOption(new InputOption('log', null, InputOption::VALUE_OPTIONAL, 'Add some log capability. Specify a log file if you want to change the log location.'));

        return $definition;
    }

    protected function getDefaultCommands(): array
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new LocalCommand\AnalysisCommand();
        $defaultCommands[] = new LocalCommand\AnalyzeCommand();
        $defaultCommands[] = new LocalCommand\ConfigureCommand();
        $defaultCommands[] = new LocalCommand\ProjectsCommand();
        $defaultCommands[] = new LocalCommand\SelfUpdateCommand();

        return $defaultCommands;
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (!$command instanceof LocalCommand\NeedConfigurationInterface) {
            return parent::doRunCommand($command, $input, $output);
        }

        $configuration = $this->getHelperSet()->get('configuration')->getConfiguration($input, $output);
        $this->apiConfig = array_merge($this->apiConfig, $configuration->toArray());

        if (false !== $input->getParameterOption('--log')) {
            $this->logFile = $input->getParameterOption('--log') ?: getcwd().'/insight.log';
        }

        return parent::doRunCommand($command, $input, $output);
    }
}
