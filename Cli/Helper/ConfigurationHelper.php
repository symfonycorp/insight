<?php

namespace SensioLabs\Insight\Cli\Helper;

use SensioLabs\Insight\Cli\Configuration;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigurationHelper extends Helper
{
    private $apiEndpoint;

    public function __construct(string $apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function updateConfigurationManually(InputInterface $input, OutputInterface $output)
    {
        $configuration = new Configuration();

        $userUuid = $input->getOption('user-uuid') ?: $configuration->getUserUuid();
        $apiToken = $input->getOption('api-token') ?: $configuration->getApiToken();

        // Avoid saving again a legacy URL
        $defaultEndpoint = $configuration->getApiEndpoint();
        if (false !== strpos($defaultEndpoint, '.sensiolabs.com')) {
            $defaultEndpoint = null;
        }

        $apiEndpoint = $input->getOption('api-endpoint') ?: $defaultEndpoint;

        $configuration->setUserUuid($this->askValue($input, $output, 'User Uuid', $userUuid));
        $configuration->setApiToken($this->askValue($input, $output, 'Api Token', $apiToken));
        $configuration->setApiEndpoint($this->askValue($input, $output, 'Api Endpoint', $apiEndpoint ?: $this->apiEndpoint));

        if (false !== strpos($configuration->getApiEndpoint(), '.sensiolabs.com')) {
            $io = new SymfonyStyle($input, $output);
            $io->warning('You are using the legacy URL of SymfonyInsight which may stop working in the future. You should reconfigure this tool by running the "configure" command and use "https://insight.symfony.com" as endpoint.');
        }

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
            $this->saveConfiguration($input, $output, $configuration);
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
        $validator = static function ($v) use ($varname) {
            if (!$v) {
                throw new \InvalidArgumentException(sprintf('Your must provide a %s!', $varname));
            }

            return $v;
        };

        if (!$input->isInteractive()) {
            return $validator($default);
        }

        $question = new Question(
            $default ? sprintf('What is your %s? [%s] ', $varname, $default) : sprintf('What is your %s? ', $varname),
            $default
        );

        $question->setValidator($validator);

        return $this->getHelperSet()->get('question')->ask($input, $output, $question);
    }

    private function saveConfiguration(InputInterface $input, OutputInterface $output, Configuration $configuration)
    {
        if (!$input->isInteractive()) {
            $configuration->save();

            return;
        }

        $question = new ConfirmationQuestion('Do you want to save this new configuration? [Y/n] ');

        if ($this->getHelperSet()->get('question')->ask($input, $output, $question)) {
            $configuration->save();
        }
    }
}
