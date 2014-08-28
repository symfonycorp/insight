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
     * @SerializedName("ignored-branches")
     */
    private $ignoredBranches;

    /**
     * @Type("string")
     * @SerializedName("pre-composer-script")
     */
    private $preComposerScript;

    /**
     * @Type("string")
     * @SerializedName("post-composer-script")
     */
    private $postComposerScript;

    /**
     * @Type("string")
     * @SerializedName("php-ini")
     */
    private $phpIni;

    /**
     * @Type("array<string>")
     * @XmlList(entry="dir")
     * @SerializedName("global-excluded-dirs")
     */
    private $globalExcludedDirs;

    /**
     * @Type("array<string>")
     * @XmlList(entry="pattern")
     * @SerializedName("excluded-patterns")
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

    public function getGlobalExcludedDirs()
    {
        return $this->globalExcludedDirs;
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

    public function toInsightConfigArray()
    {
        return array(
            'ignore_branches' => $this->ignoredBranches,
            'pre_composer_script' => $this->preComposerScript,
            'post_composer_script' => $this->postComposerScript,
            'php_ini' => $this->phpIni,
            'global_exclude_dirs' => $this->globalExcludedDirs,
            'exclude_patterns' => $this->excludedPatterns,
            'patterns' => $this->patterns,
            'rules' => $this->rules,
            'parameters' => $this->parameters,
        );
    }
}
