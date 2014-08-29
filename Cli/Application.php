<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SensioLabs\Insight\Cli\Command as LocalCommand;
use SensioLabs\Insight\Sdk\Api;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    const APPLICATION_NAME = 'SensioLabs Insight CLI';
    const APPLICATION_VERSION = '1.1';

    private $api;
    private $apiConfig;
    private $enableLog;

    public function __construct()
    {
        $this->apiConfig = array(
            'base_url' => Api::ENDPOINT,
        );

        parent::__construct(static::APPLICATION_NAME, static::APPLICATION_VERSION);
    }

    public function getApi()
    {
        if ($this->api) {
            return $this->api;
        }

        $config = $this->apiConfig;
        if (array_key_exists('api_endpoint', $config)) {
            $config['base_url'] = $config['api_endpoint'];
        }
        $this->api = new Api($config);

        if ($this->enableLog) {
            if (!class_exists('Monolog\Logger')) {
                throw new \InvalidArgumentException('You must include monolog if you want to log (run "composer install --dev")');
            }

            $logger = new Logger('insight');
            $logger->pushHandler(new StreamHandler(getcwd().'/insight.log', Logger::DEBUG));

            $this->api->setLogger($logger);
        }

        return $this->api;
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption('api-token', null, InputOption::VALUE_REQUIRED, 'Your api token.'));
        $definition->addOption(new InputOption('user-uuid', null, InputOption::VALUE_REQUIRED, 'Your user uuid.'));
        $definition->addOption(new InputOption('api-endpoint', null, InputOption::VALUE_REQUIRED, 'The api endpoint.'));
        $definition->addOption(new InputOption('log', null, InputOption::VALUE_NONE, 'Add some log capability.'));

        return $definition;
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new LocalCommand\AnalysisCommand();
        $defaultCommands[] = new LocalCommand\AnalyzeCommand();
        $defaultCommands[] = new LocalCommand\ProjectsCommand();
        $defaultCommands[] = new LocalCommand\SelfUpdateCommand();

        return $defaultCommands;
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        $storagePath = getenv('INSIGHT_HOME');
        if (!$storagePath) {
            if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                if (!getenv('APPDATA')) {
                    throw new \RuntimeException('The APPDATA or INSIGHT_HOME environment variable must be set for insight to run correctly');
                }
                $storagePath = strtr(getenv('APPDATA'), '\\', '/') . '/Sensiolabs';
            } else {
                if (!getenv('HOME')) {
                    throw new \RuntimeException('The HOME or INSIGHT_HOME environment variable must be set for insight to run correctly');
                }
                $storagePath = rtrim(getenv('HOME'), '/') . '/.sensiolabs';
            }
        }
        if (!is_dir($storagePath) && ! @mkdir($storagePath, 0777, true)) {
            throw new \RuntimeException(sprintf('The directory "%s" does not exist and could not be created.', $storagePath));
        }

        if (!is_writable($storagePath)) {
            throw new \RuntimeException(sprintf('The directory "%s" is not writable.', $storagePath));
        }
        $configPath = $storagePath.'/insight.json';

        $config = array('api_token' => null, 'user_uuid' => null, 'api_endpoint' => Api::ENDPOINT);
        if (file_exists($configPath)) {
            $config = array_replace($config, json_decode(file_get_contents($configPath), true));
        }

        $newConfig = array(
            'api_token' => $this->getValue($input, $output, '--api-token', 'INSIGHT_API_TOKEN', 'api token', $config['api_token']),
            'user_uuid' => $this->getValue($input, $output, '--user-uuid', 'INSIGHT_USER_UUID', 'user uuid', $config['user_uuid']),
            'api_endpoint' => $this->getValue($input, $output, '--api-endpoint', 'INSIGHT_API_ENDPOINT', 'api endpoint', $config['api_endpoint']),
        );

        if ($config !== $newConfig && $input->isInteractive()) {
            $dialog = $this->getHelperSet()->get('dialog');
            if ($dialog->askConfirmation($output, sprintf('Do you want to store your configuration in "%s" <comment>[y/N]</comment>?', $storagePath), false)) {
                file_put_contents($configPath, json_encode($newConfig));
            }
        }

        $this->apiConfig = array_merge($this->apiConfig, $newConfig);

        $this->enableLog = false !== $input->getParameterOption('--log');

        return parent::doRunCommand($command, $input, $output);
    }

    private function getValue(InputInterface $input, OutputInterface $output, $cliVarName, $envVarName, $varname, $defaultValue = null)
    {
        $value = $input->getParameterOption($cliVarName, getenv($envVarName) ?: null);
        if ($defaultValue) {
            return $value ?: $defaultValue;
        }

        // The is not value on cli, env, nor default value, we fallback with dialog
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$value && $input->isInteractive()) {
            $value = $dialog->askAndValidate(
                $output,
                $question = sprintf('What is your %s? ', $varname),
                function ($v) use ($question) {
                    if (!$v) {
                        throw new \InvalidArgumentException($question);
                    }

                    return $v;
                }
            );
        }
        if (!$value) {
            throw new \InvalidArgumentException(sprintf('You should provide your %s', $varname));
        }

        return $value;
    }
}
