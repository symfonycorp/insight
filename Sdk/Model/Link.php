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

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
