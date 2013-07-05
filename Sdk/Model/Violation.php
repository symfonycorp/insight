<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
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
    private $criticity;

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $category;

    public function getTitle()
    {
        return $this->title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getCriticity()
    {
        return $this->criticity;
    }

    public function getCategory()
    {
        return $this->category;
    }

}
