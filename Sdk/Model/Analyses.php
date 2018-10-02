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

class Analyses
{
    /**
     * @Type("array<SymfonyCorp\Insight\Sdk\Model\Link>")
     * @XmlList(inline = true, entry = "link")
     */
    private $links = array();

    /**
     * @Type("array<SymfonyCorp\Insight\Sdk\Model\Analysis>")
     * @XmlList(inline = true, entry = "analysis")
     */
    private $analyses = array();

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
