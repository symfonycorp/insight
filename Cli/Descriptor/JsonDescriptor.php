<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Cli\Descriptor;

use JMS\Serializer\Serializer;
use SymfonyCorp\Insight\Sdk\Model\Analysis;

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
