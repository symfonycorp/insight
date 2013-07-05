<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Descriptor;

use JMS\Serializer\Serializer;
use SensioLabs\Insight\Sdk\Model\Analysis;

class JsonDescriptor extends AbstractDescriptor
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function describeAnalysis(Analysis $analysis, array $options = array())
    {
        $output = $options['output'];
        $output->writeln($this->serializer->serialize($analysis, 'json'));
    }
}
