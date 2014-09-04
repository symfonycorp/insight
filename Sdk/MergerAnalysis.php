<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *
 * @category    PhpStorm
 * @author     aurelien
 * @copyright  2014 Efidev 
 * @version    CVS: Id:$
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