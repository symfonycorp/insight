<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;

class Violations implements \Countable, \IteratorAggregate
{
    /**
     * @Type("array<SymfonyCorp\Insight\Sdk\Model\Violation>")
     * @XmlList(inline = true, entry = "violation")
     */
    private $violations = array();

    public function count()
    {
        return count($this->violations);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->violations);
    }

    /**
     * @return Violation[]
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param callable $callback
     */
    public function filter($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The callback is not callable.');
        }

        $this->violations = array_filter($this->violations, $callback);
    }
}
