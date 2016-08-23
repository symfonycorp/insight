<?php

namespace SensioLabs\Insight\Cli\Helper;

use SensioLabs\Insight\Cli\Configuration;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationHelper extends Helper
{
    private $apiEndpoint;

    public function __construct($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function updateConfigurationManually(InputInterface $input, OutputInterface $output)
    {
        $configuration = new Configuration();

        if ($apiToken = $input->getOption('api-token')) {
            $configuration->setApiToken($apiToken);
        }

        if ($userUuid = $input->getOption('user-uuid')) {
            $configuration->setUserUuid($userUuid);
        }

        if ($apiEndpoint = $input->getOption('api-endpoint')) {
            $configuration->setApiEndpoint($apiEndpoint);
        }

        $configuration->setUserUuid($this->askValue($input, $output, 'User Uuid', $configuration->getUserUuid()));
        $configuration->setApiToken($this->askValue($input, $output, 'Api Token', $configuration->getApiToken()));
        $configuration->setApiEndpoint($this->askValue($input, $output, 'Api Endpoint', $configuration->getApiEndpoint() ?: $this->apiEndpoint));

        $this->saveConfiguration($input, $output, $configuration);
    }

    public function getConfiguration(InputInterface $input, OutputInterface $output)
    {
        $previousConfiguration = new Configuration();
        $configuration = clone $previousConfiguration;

        $this->resolveValue($input, $output, $configuration, 'User Uuid', null);
        $this->resolveValue($input, $output, $configuration, 'Api Token', null);
        $this->resolveValue($input, $output, $configuration, 'Api Endpoint', $this->apiEndpoint);

        if (!$configuration->equals($previousConfiguration)) {
            $this->saveConfiguration($input, $output, $configuration, $previousConfiguration);
        }

        return $configuration;
    }

    public function getName()
    {
        return 'configuration';
    }

    private function resolveValue(InputInterface $input, OutputInterface $output, Configuration $configuration, $varName, $default = null)
    {
        $configurationProperty = str_replace(' ', '', $varName);

        $value = $this->getValue($input, $varName);

        if (!$value) {
            $value = $configuration->{'get'.$configurationProperty}();
        }
        if (!$value) {
            $value = $default;
        }
        if (!$value && $input->isInteractive()) {
            $value = $this->askValue($input, $output, $varName);
        }
        if (!$value) {
            throw new \InvalidArgumentException(sprintf('You should provide your %s.', $varName));
        }
        $configuration->{'set'.$configurationProperty}($value);
    }

    private function getValue(InputInterface $input, $varName)
    {
        $envVarName = sprintf('INSIGHT_%s', str_replace(' ', '_', strtoupper($varName)));

        if ($value = getenv($envVarName)) {
            return $value;
        }

        $cliVarName = sprintf('--%s', str_replace(' ', '-', strtolower($varName)));

        return $input->getParameterOption($cliVarName);
    }

    private function askValue(InputInterface $input, OutputInterface $output, $varname, $default = null)
    {
        if ($default) {
            $question = sprintf('What is your %s? [%s] ', $varname, $default);
        } else {
            $question = sprintf('What is your %s? ', $varname);
        }

        $validator = function ($v) use ($varname) {
            if (!$v) {
                throw new \InvalidArgumentException(sprintf('Your must provide a %s!', $varname));
            }

            return $v;
        };

        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog->askAndValidate($output, $question, $validator, ($input->isInteractive() ? false : 1), $default);
    }

    private function saveConfiguration(InputInterface $input, OutputInterface $output, Configuration $configuration)
    {
        $question = 'Do you want to save this new configuration? [Y/n] ';
        if (PHP_VERSION_ID > 50400) {
            $question = json_encode($configuration->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n\n".$question;
        }
        $dialog = $this->getHelperSet()->get('dialog');

        if ($dialog->askConfirmation($output, $question) or !$input->isInteractive()) {
            $configuration->save();
        }
    }
}
