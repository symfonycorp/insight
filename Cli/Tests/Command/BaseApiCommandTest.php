<?php

namespace SensioLabs\Insight\Cli\Tests\Command;

use PHPUnit\Framework\TestCase;
use SensioLabs\Insight\Cli\Application;
use SensioLabs\Insight\Cli\Command\BaseApiCommand;
use SensioLabs\Insight\Sdk\Exception\ApiClientException;
use SensioLabs\Insight\Sdk\Exception\ApiParserException;
use SensioLabs\Insight\Sdk\Exception\ApiServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class BaseApiCommandTest extends TestCase
{
    private $command;

    protected function setUp(): void
    {
        $this->command = new class() extends BaseApiCommand {
            private $exceptionToThrow;

            public function setExceptionToThrow(\Throwable $exception = null): void
            {
                $this->exceptionToThrow = $exception;
            }

            protected function configure(): void
            {
                $this->setName('test-command');
            }

            protected function executeCommand(InputInterface $input, OutputInterface $output): int
            {
                if ($this->exceptionToThrow) {
                    throw $this->exceptionToThrow;
                }
                $output->writeln('Success');

                return Command::SUCCESS;
            }
        };

        $this->command->setApplication(new Application());
    }

    public function testSuccessfulExecution(): void
    {
        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Success', $tester->getDisplay());
    }

    public function testApiClientException(): void
    {
        $exception = new ApiClientException('HTTP/1.1 401 Unauthorized');
        $this->command->setExceptionToThrow($exception);

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Invalid API credentials', $tester->getDisplay());
    }

    public function testApiServerException(): void
    {
        $exception = new ApiServerException('HTTP/1.1 500 Internal Server Error');
        $this->command->setExceptionToThrow($exception);

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Server temporarily unavailable', $tester->getDisplay());
    }

    public function testApiParserException(): void
    {
        $exception = new ApiParserException('Unable to parse response XML');
        $this->command->setExceptionToThrow($exception);

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Unable to process server response', $tester->getDisplay());
    }

    public function testTransportException(): void
    {
        $exception = new class('Network connection timeout') extends \Exception implements TransportExceptionInterface {
            public function getResponse()
            {
                throw new \BadMethodCallException('Not supported');
            }
        };
        $this->command->setExceptionToThrow($exception);

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Network connection failed', $tester->getDisplay());
    }

    public function testGenericException(): void
    {
        $exception = new \RuntimeException('Something unexpected happened');
        $this->command->setExceptionToThrow($exception);

        $tester = new CommandTester($this->command);
        $exitCode = $tester->execute([]);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Something went wrong', $tester->getDisplay());
    }
}
