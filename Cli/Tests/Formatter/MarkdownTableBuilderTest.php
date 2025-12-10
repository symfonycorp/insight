<?php

namespace SensioLabs\Insight\Cli\Tests\Formatter;

use PHPUnit\Framework\TestCase;
use SensioLabs\Insight\Cli\Formatter\MarkdownTableBuilder;
use SensioLabs\Insight\Sdk\Model\Violation;
use SensioLabs\Insight\Sdk\Model\Violations;

class MarkdownTableBuilderTest extends TestCase
{
    public function testBuildMultipleViolations(): void
    {
        $violation1 = $this->createStub(Violation::class);
        $violation1->method('getTitle')->willReturn('Title1');
        $violation1->method('getCategory')->willReturn('Bug');
        $violation1->method('getSeverity')->willReturn('High');
        $violation1->method('getMessage')->willReturn("Message1 with | pipe\nand newline");
        $violation1->method('getResource')->willReturn('src/File1.php');
        $violation1->method('getLine')->willReturn(10);

        $violation2 = $this->createStub(Violation::class);
        $violation2->method('getTitle')->willReturn('Title2');
        $violation2->method('getCategory')->willReturn('Style');
        $violation2->method('getSeverity')->willReturn('Low');
        $violation2->method('getMessage')->willReturn("Message2 with | pipe\rCarriageReturn");
        $violation2->method('getResource')->willReturn('src/File2.php');
        $violation2->method('getLine')->willReturn(20);

        $violations = $this->createStub(Violations::class);
        $violations->method('getIterator')->willReturn(new \ArrayIterator([$violation1, $violation2]));

        $builder = new MarkdownTableBuilder();

        $expected = <<<MD
| Title | Category | Severity | Message | File | Line |
|-------|----------|----------|---------|------|------|
| Title1 | Bug | High | Message1 with / pipe and newline | src/File1.php | 10 |
| Title2 | Style | Low | Message2 with / pipe CarriageReturn | src/File2.php | 20 |
MD;

        $this->assertSame($expected, $builder->build($violations));
    }
}
