<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
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
        $counts = array();

        $violations = $analysis->getViolations();
        if ($violations) {
            foreach ($violations as $violation) {
                if (!isset($counts[$violation->getCategory()])) {
                    $counts[$violation->getCategory()] = 0;
                }
                ++$counts[$violation->getCategory()];

                if (!isset($counts[$violation->getSeverity()])) {
                    $counts[$violation->getSeverity()] = 0;
                }
                ++$counts[$violation->getSeverity()];
            }
        }
        $vars = array(
           'analysis' => $analysis,
           'counts' => (object) $counts,
        );

        if ($this->el->evaluate($expr, $vars)) {
            return 70;
        }

        return 0;
    }

    public function getName()
    {
        return 'fail_condition';
    }
}
