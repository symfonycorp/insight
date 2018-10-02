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
use JMS\Serializer\Annotation\XmlAttribute;

class Violation
{
    /** @Type("string") */
    private $title;

    /** @Type("string") */
    private $message;

    /** @Type("string") */
    private $resource;

    /** @Type("integer") */
    private $line;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $severity;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $category;

    /**
     * @Type("boolean")
     * @XmlAttribute
     */
    private $ignored;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
    }
}
