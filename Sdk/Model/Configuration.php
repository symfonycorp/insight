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

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlKeyValuePairs;

class Configuration
{
    /**
     * @Type("array<string>")
     * @XmlList(entry="branch")
     * @SerializedName("ignore_branches")
     */
    private $ignoredBranches;

    /**
     * @Type("string")
     */
    private $preComposerScript;

    /**
     * @Type("string")
     */
    private $postComposerScript;

    /**
     * @Type("string")
     */
    private $phpIni;

    /**
     * @Type("array<string>")
     * @XmlList(entry="dir")
     */
    private $globalExcludeDirs;

    /**
     * @Type("array<string>")
     * @XmlList(entry="pattern")
     * @SerializedName("exclude_patterns")
     */
    private $excludedPatterns;

    /**
     * @Type("patterns")
     */
    private $patterns;

    /**
     * @Type("parameters")
     */
    private $parameters;

    /**
     * @Type("rules")
     */
    private $rules;

    public function getIgnoredBranches()
    {
        return $this->ignoredBranches;
    }

    public function getPreComposerScript()
    {
        return $this->preComposerScript;
    }

    public function getPostComposerScript()
    {
        return $this->postComposerScript;
    }

    public function getPhpIni()
    {
        return $this->phpIni;
    }

    public function getGlobalExcludeDirs()
    {
        return $this->globalExcludeDirs;
    }

    public function getExcludedPatterns()
    {
        return $this->excludedPatterns;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getRules()
    {
        return $this->rules;
    }
}
