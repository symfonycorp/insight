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

class Configuration
{
    /**
     * @Type("array<string>")
     * @XmlList(entry="branch")
     * @SerializedName("ignored-branches")
     */
    private $ignoredBranches = array();

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
    private $globalExcludedDirs = array();

    /**
     * @Type("array<string>")
     * @XmlList(entry="pattern")
     * @SerializedName("excluded-patterns")
     */
    private $excludedPatterns = array();

    /**
     * @Type("patterns")
     */
    private $patterns = array();

    /**
     * @Type("parameters")
     */
    private $parameters = array();

    /**
     * @Type("rules")
     */
    private $rules = array();

    /**
     * @return array
     */
    public function getIgnoredBranches()
    {
        return $this->ignoredBranches;
    }

    /**
     * @return string|null
     */
    public function getPreComposerScript()
    {
        return $this->preComposerScript;
    }

    /**
     * @return string|null
     */
    public function getPostComposerScript()
    {
        return $this->postComposerScript;
    }

    /**
     * @return string|null
     */
    public function getPhpIni()
    {
        return $this->phpIni;
    }

    /**
     * @return array
     */
    public function getGlobalExcludedDirs()
    {
        return $this->globalExcludedDirs;
    }

    /**
     * @return array
     */
    public function getExcludedPatterns()
    {
        return $this->excludedPatterns;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
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
