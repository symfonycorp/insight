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

use SensioLabs\Insight\Cli\Formatter\MarkdownTableBuilder;
use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\Violation;
use SensioLabs\Insight\Sdk\Model\Violations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateLLMInputCommand extends Command implements NeedConfigurationInterface
{
    private const OUTPUT_FILE_NAME = 'symfony_insight_llm_prompt_%s.md';

    protected function configure(): void
    {
        $this
            ->setName('generate-llm-input')
            ->addArgument('project-uuid', InputArgument::REQUIRED)
            ->addOption('with-security-violations', null, InputOption::VALUE_NONE, 'Include security violations')
            ->addOption('show-ignored-violations', null, InputOption::VALUE_NONE, 'Show ignored violations')
            ->setDescription('Generate a LLM-ready prompt based on the last analysis of a project')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projectUuid = $input->getArgument('project-uuid');

        try {
            $api = $this->getApplication()->getApi();

            /** @var Analysis $analysis */
            $analysis = $api->getProject($projectUuid)->getLastAnalysis();

            if (!$analysis) {
                $io->writeln('<error>There are no analyses</error>');

                return Command::FAILURE;
            }

            $violations = $analysis->getViolations();

            if (!$violations || 0 === $violations->count()) {
                $io->writeln('<info>No violations found in the last analysis.</info>');

                return Command::SUCCESS;
            }

            if (!$input->getOption('show-ignored-violations')) {
                $violations->filter(function (Violation $violation) {
                    return !$violation->isIgnored();
                });
            }

            $includeSecurity = false;

            if ($input->getOption('with-security-violations')) {
                $io->warning([
                    'Using the --with-security-violations option will expose the security vulnerabilities of your application in the prompt.',
                    'Make sure you do not share it with any public or untrusted agent.',
                ]);

                $includeSecurity = $io->confirm('Do you really want to include these in the prompt?', false);
            }

            if (!$includeSecurity) {
                $violations->filter(function (Violation $violation) {
                    return 'security' !== $violation->getCategory();
                });
            }

            if (0 === $violations->count()) {
                $io->writeln('<info>No violations found after applying filters.</info>');

                return Command::SUCCESS;
            }

            $promptContent = $this->buildPromptContent($violations);
            $outputPath = $this->writePromptFile($promptContent);

            $io->writeln(sprintf('<info>Your LLM prompt is ready 🚀 at <href=%1$s/>%1$s</info>', $outputPath));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    private function buildPromptContent(Violations $violations): string
    {
        $promptTemplatePath = __DIR__.'/../Resources/prompt.md';

        if (!file_exists($promptTemplatePath)) {
            throw new \RuntimeException(sprintf('Prompt template not found at: %s', $promptTemplatePath));
        }

        $instructions = file_get_contents($promptTemplatePath);

        if (false === $instructions) {
            throw new \RuntimeException('Failed to read prompt template');
        }

        return str_replace('{{analysis_result}}', MarkdownTableBuilder::build($violations), $instructions);
    }

    private function writePromptFile(string $content): string
    {
        $outputPath = sprintf(self::OUTPUT_FILE_NAME, (new \DateTimeImmutable('now'))->format('YmdHis'));

        if (false === file_put_contents($outputPath, $content)) {
            throw new \RuntimeException(sprintf('Failed to write file: %s', $outputPath));
        }

        $realPath = realpath($outputPath);

        if (false === $realPath) {
            throw new \RuntimeException(sprintf('Failed to resolve real path for: %s', $outputPath));
        }

        return $realPath;
    }
}
