<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk;

use SensioLabs\Insight\Sdk\Model\Analysis;

class MergerAnalysis
{

    public function merge(Analysis $statusAnalysis, Analysis $reportAnalysis)
    {
        $this->mergeField($statusAnalysis, $reportAnalysis, 'status');
        $this->mergeField($statusAnalysis, $reportAnalysis, 'beginAt');
        $this->mergeField($statusAnalysis, $reportAnalysis, 'endAt');
    }

    /**
     * @param Analysis $statusAnalysis
     * @param Analysis $reportAnalysis
     * @param $field
     */
    private function mergeField(Analysis $statusAnalysis, Analysis $reportAnalysis, $field)
    {
        $statusProperty       = new \ReflectionProperty(get_class($statusAnalysis), $field);
        $reportStatusProperty = new \ReflectionProperty(get_class($statusAnalysis), $field);

        $reportStatusProperty->setAccessible(true);
        $statusProperty->setAccessible(true);
        $reportStatusProperty->setValue($reportAnalysis, $statusProperty->getValue($statusAnalysis));
        $reportStatusProperty->setAccessible(false);
        $statusProperty->setAccessible(false);
    }
}
