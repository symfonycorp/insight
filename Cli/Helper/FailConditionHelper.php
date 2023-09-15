<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Helper;

use SensioLabs\Insight\Sdk\Model\Analysis;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FailConditionHelper extends Helper
{
    private $el;

    public function __construct()
    {
        $this->el = new ExpressionLanguage();
    }

    public function evaluate(Analysis $analysis, $expr)
    {
        $analysisData = [
            'grade' => $analysis->getGrade(),
            'nbViolations' => 0,
            'remediationCost' => $analysis->getRemediationCost(),
        ];

        $counts = [
            // Category
            'architecture' => 0,
            'bugrisk' => 0,
            'codestyle' => 0,
            'deadcode' => 0,
            'performance' => 0,
            'readability' => 0,
            'security' => 0,

            // Severity
            'critical' => 0,
            'major' => 0,
            'minor' => 0,
            'info' => 0,
        ];

        $violations = $analysis->getViolations() ?: [];

        foreach ($violations as $violation) {
            ++$counts[$violation->getCategory()];
            ++$counts[$violation->getSeverity()];
            ++$analysisData['nbViolations'];
        }

        $vars = [
            'analysis' => (object) $analysisData,
            'counts' => (object) $counts,
        ];

        if ($this->el->evaluate($expr, $vars)) {
            return 70;
        }

        return 0;
    }

    public function getName(): string
    {
        return 'fail_condition';
    }
}
