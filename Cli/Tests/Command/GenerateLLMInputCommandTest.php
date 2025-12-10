<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Tests\Command;

use PHPUnit\Framework\TestCase;
use SensioLabs\Insight\Cli\Application;
use SensioLabs\Insight\Cli\Command\GenerateLLMInputCommand;
use SensioLabs\Insight\Sdk\Api;
use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\Project;
use SensioLabs\Insight\Sdk\Model\Violation;
use SensioLabs\Insight\Sdk\Model\Violations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateLLMInputCommandTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/insight_test_'.uniqid();
        mkdir($this->tempDir);
        chdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testExecuteWithoutSecurityViolationsFiltersThemByDefault(): void
    {
        $violations = $this->createViolationsWithCategories(['performance', 'security', 'bug']);
        $command = $this->createCommandWithMockedApi($violations);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['project-uuid' => 'test-uuid-123']);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Your LLM prompt is ready', $output);

        $generatedFile = $this->getGeneratedFile();
        $this->assertFileExists($generatedFile);

        $content = file_get_contents($generatedFile);
        $this->assertStringContainsString('Performance Violation', $content);
        $this->assertStringContainsString('Bug Violation', $content);
        $this->assertStringNotContainsString('Security Violation', $content);
    }

    public function testExecuteWithSecurityViolationsWhenConfirmed(): void
    {
        $violations = $this->createViolationsWithCategories(['performance', 'security', 'bug']);
        $command = $this->createCommandWithMockedApi($violations);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute(['project-uuid' => 'test-uuid-123', '--with-security-violations' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $generatedFile = $this->getGeneratedFile();
        $content = file_get_contents($generatedFile);

        $this->assertStringContainsString('Performance Violation', $content);
        $this->assertStringContainsString('Bug Violation', $content);
        $this->assertStringContainsString('Security Violation', $content);
    }

    public function testExecuteWithSecurityViolationsWhenDeclined(): void
    {
        $violations = $this->createViolationsWithCategories(['performance', 'security']);
        $command = $this->createCommandWithMockedApi($violations);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['no']);
        $commandTester->execute(['project-uuid' => 'test-uuid-123', '--with-security-violations' => true]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $generatedFile = $this->getGeneratedFile();
        $content = file_get_contents($generatedFile);

        $this->assertStringContainsString('Performance Violation', $content);
        $this->assertStringNotContainsString('Security Violation', $content);
    }

    public function testExecuteFiltersIgnoredViolationsByDefault(): void
    {
        $violations = $this->createViolationsWithIgnored();
        $command = $this->createCommandWithMockedApi($violations);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['project-uuid' => 'test-uuid-123',]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $generatedFile = $this->getGeneratedFile();
        $content = file_get_contents($generatedFile);

        $this->assertStringContainsString('Active Violation', $content);
        $this->assertStringNotContainsString('Ignored Violation', $content);
    }

    public function testExecuteShowsIgnoredViolationsWhenOptionSet(): void
    {
        $violations = $this->createViolationsWithIgnored();
        $command = $this->createCommandWithMockedApi($violations);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['project-uuid' => 'test-uuid-123', '--show-ignored-violations' => true,]);

        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $generatedFile = $this->getGeneratedFile();
        $content = file_get_contents($generatedFile);

        $this->assertStringContainsString('Active Violation', $content);
        $this->assertStringContainsString('Ignored Violation', $content);
    }

    private function createCommandWithMockedApi(Violations $violations): GenerateLLMInputCommand
    {
        $analysis = $this->createStub(Analysis::class);
        $analysis->method('getViolations')->willReturn($violations);

        $project = $this->createStub(Project::class);
        $project->method('getLastAnalysis')->willReturn($analysis);

        $api = $this->createStub(Api::class);
        $api->method('getProject')->willReturn($project);

        $application = new class($api) extends Application {
            private $mockedApi;

            public function __construct(Api $api)
            {
                $this->mockedApi = $api;
                parent::__construct();
            }

            public function getApi(): Api
            {
                return $this->mockedApi;
            }
        };

        $command = new GenerateLLMInputCommand();
        $command->setApplication($application);

        return $command;
    }

    private function createViolationsWithCategories(array $categories): Violations
    {
        $violationsArray = [];

        foreach ($categories as $category) {
            $violation = $this->createStub(Violation::class);
            $violation->method('getCategory')->willReturn($category);
            $violation->method('isIgnored')->willReturn(false);
            $violation->method('getTitle')->willReturn(ucfirst($category).' Violation');
            $violation->method('getMessage')->willReturn('This is a '.$category.' issue');
            $violation->method('getSeverity')->willReturn('major');
            $violation->method('getResource')->willReturn('src/File.php');
            $violation->method('getLine')->willReturn(42);

            $violationsArray[] = $violation;
        }

        return $this->generateViolationsMock($violationsArray);
    }

    private function createViolationsWithIgnored(): Violations
    {
        $activeViolation = $this->createStub(Violation::class);
        $activeViolation->method('getCategory')->willReturn('performance');
        $activeViolation->method('isIgnored')->willReturn(false);
        $activeViolation->method('getTitle')->willReturn('Active Violation');
        $activeViolation->method('getMessage')->willReturn('This is active');
        $activeViolation->method('getSeverity')->willReturn('major');
        $activeViolation->method('getResource')->willReturn('src/Active.php');
        $activeViolation->method('getLine')->willReturn(10);

        $ignoredViolation = $this->createStub(Violation::class);
        $ignoredViolation->method('getCategory')->willReturn('bug');
        $ignoredViolation->method('isIgnored')->willReturn(true);
        $ignoredViolation->method('getTitle')->willReturn('Ignored Violation');
        $ignoredViolation->method('getMessage')->willReturn('This is ignored');
        $ignoredViolation->method('getSeverity')->willReturn('minor');
        $ignoredViolation->method('getResource')->willReturn('src/Ignored.php');
        $ignoredViolation->method('getLine')->willReturn(20);

        $violationsArray = [$activeViolation, $ignoredViolation];

        return $this->generateViolationsMock($violationsArray);
    }

    private function generateViolationsMock(array $violationsArray): Violations
    {
        return new class($violationsArray) extends Violations {
            private $items;

            public function __construct(array $items)
            {
                $this->items = $items;
            }

            public function count(): int
            {
                return count($this->items);
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->items);
            }

            public function getViolations(): array
            {
                return $this->items;
            }

            public function filter($callback): void
            {
                if (!\is_callable($callback)) {
                    throw new \InvalidArgumentException('The callback is not callable.');
                }
                $this->items = array_values(array_filter($this->items, $callback));
            }
        };
    }

    private function getGeneratedFile(): string
    {
        $files = glob($this->tempDir.'/symfony_insight_llm_prompt_*.md');
        $this->assertNotEmpty($files, 'No generated file found');

        return $files[0];
    }
}
