<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Insight\Cli\Descriptor;

use Symfony\Insight\Sdk\Model\Analysis;
use Symfony\Insight\Sdk\Model\Violation;

abstract class AbstractDescriptor
{
    public function describe($object, array $options = array())
    {
        if ($object instanceof Analysis) {
            if (!$options['show_ignored_violations'] && $object->getViolations()) {
                $object->getViolations()->filter(function (Violation $v) {
                    return !$v->isIgnored();
                });
            }

            return $this->describeAnalysis($object, $options);
        }

        throw new \InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_class($object)));
    }

    abstract protected function describeAnalysis(Analysis $argument, array $options = array());
}
