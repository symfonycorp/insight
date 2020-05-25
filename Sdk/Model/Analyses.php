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
use JMS\Serializer\Annotation\XmlList;

class Analyses
{
    /**
     * @Type("array<SensioLabs\Insight\Sdk\Model\Link>")
     * @XmlList(inline = true, entry = "link")
     */
    private $links = [];

    /**
     * @Type("array<SensioLabs\Insight\Sdk\Model\Analysis>")
     * @XmlList(inline = true, entry = "analysis")
     */
    private $analyses = [];

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return Analysis[]
     */
    public function getAnalyses()
    {
        return $this->analyses;
    }
}
