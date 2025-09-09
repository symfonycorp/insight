<?php

namespace SensioLabs\Insight\Cli\EventListener;

use SensioLabs\Insight\Sdk\Exception\ApiClientException;
use SensioLabs\Insight\Sdk\Exception\ApiParserException;
use SensioLabs\Insight\Sdk\Exception\ApiServerException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApiErrorListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => ['onConsoleError', 256],
        ];
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $output = $event->getOutput();
        $error = $event->getError();

        if ($error instanceof ApiClientException) {
            $this->showClientError($error, $output);
        } elseif ($error instanceof ApiServerException) {
            $output->writeln('<error>Server temporarily unavailable. Please try again in a few minutes.</error>');
        } elseif ($error instanceof ApiParserException) {
            $output->writeln('<error>Unable to process server response. Please try again later.</error>');
        } elseif ($error instanceof TransportExceptionInterface) {
            $output->writeln('<error>Network connection failed. Check your internet connection.</error>');
        } else {
            $output->writeln('<error>Something went wrong. Please try again, or run with -v for more details.</error>');
        }

        if ($output->isVerbose()) {
            throw $error;
        }

        $event->setExitCode(0);
    }

    private function showClientError(ApiClientException $e, $output): void
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
