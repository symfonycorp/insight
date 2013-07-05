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

class Link
{
    /**
     * @XmlAttribute
     * @Type("string")
     */
    private $href;

    /**
     * @XmlAttribute
     * @Type("string")
     */
    private $rel;

    /**
     * @XmlAttribute
     * @Type("string")
     */
    private $type;

    public function getHref()
    {
        return $this->href;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function getType()
    {
        return $this->type;
    }
}
