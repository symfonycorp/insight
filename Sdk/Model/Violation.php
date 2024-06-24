<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;

class Violation
{
    /** @Type("string") */
    #[Type("string")]
    private $title;

    /** @Type("string") */
    #[Type("string")]
    private $message;

    /** @Type("string") */
    #[Type("string")]
    private $resource;

    /** @Type("integer") */
    #[Type("integer")]
    private $line;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    #[Type("string")]
    #[XmlAttribute]
    private $severity;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    #[Type("string")]
    #[XmlAttribute]
    private $category;

    /**
     * @Type("boolean")
     * @XmlAttribute
     */
    #[Type("boolean")]
    #[XmlAttribute]
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
