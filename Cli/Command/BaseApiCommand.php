<?php

namespace SensioLabs\Insight\Cli\Command;

use SensioLabs\Insight\Sdk\Api;
use SensioLabs\Insight\Sdk\Exception\ApiClientException;
use SensioLabs\Insight\Sdk\Exception\ApiParserException;
use SensioLabs\Insight\Sdk\Exception\ApiServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class BaseApiCommand extends Command implements NeedConfigurationInterface
{
    /**
     * @return Api
     */
    protected function getApi()
    {
        return $this->getApplication()->getApi();
    }

    final public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            return $this->executeCommand($input, $output);
        } catch (\Throwable $e) {
            return $this->handleError($e, $output);
        }
    }

    abstract protected function executeCommand(InputInterface $input, OutputInterface $output): int;

    private function handleError(\Throwable $e, OutputInterface $output): int
    {
        if ($e instanceof ApiClientException) {
            $this->showClientError($e, $output);
        } elseif ($e instanceof ApiServerException) {
            $output->writeln('<error>Server temporarily unavailable. Please try again in a few minutes.</error>');
        } elseif ($e instanceof ApiParserException) {
            $output->writeln('<error>Unable to process server response. Please try again later.</error>');
        } elseif ($e instanceof TransportExceptionInterface) {
            $output->writeln('<error>Network connection failed. Check your internet connection.</error>');
        } else {
            $output->writeln('<error>Something went wrong. Please try again.</error>');
        }

        return Command::FAILURE;
    }

    private function showClientError(ApiClientException $e, OutputInterface $output): void
    {
        $message = $e->getMessage();
        if (false !== strpos($message, '401') || false !== strpos($message, 'Unauthorized')) {
            $output->writeln('<error>Invalid API credentials. Run "php insight.phar configure" to set up your token.</error>');
        } elseif (false !== strpos($message, '404') || false !== strpos($message, 'Not Found')) {
            $output->writeln('<error>Project not found. Check the project UUID and try again.</error>');
        } elseif (false !== strpos($message, '403') || false !== strpos($message, 'Forbidden')) {
            $output->writeln('<error>Access denied. You don\'t have permission for this project.</error>');
        } else {
            $output->writeln('<error>Request failed. Please check your input and try again.</error>');
        }
    }
}
