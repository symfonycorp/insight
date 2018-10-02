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

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlList;

class Analysis
{
    const STATUS_ORDERED = 'ordered';
    const STATUS_RUNNING = 'running';
    const STATUS_MEASURED = 'measured';
    const STATUS_ANALYZED = 'analyzed';
    const STATUS_FINISHED = 'finished';

    /**
     * @Type("array<SymfonyCorp\Insight\Sdk\Model\Link>")
     * @XmlList(inline = true, entry = "link")
     */
    private $links = array();

    /** @Type("integer") */
    private $number;

    /** @Type("string") */
    private $grade;

    /**
     * @Type("string")
     * @SerializedName("next-grade")
     */
    private $nextGrade;

    /** @Type("array<string>") */
    private $grades = array();

    /**
     * @Type("float")
     * @SerializedName("remediation-cost")
     */
    private $remediationCost;

    /**
     * @Type("float")
     * @SerializedName("remediation-cost-for-next-grade")
     */
    private $remediationCostForNextGrade;

    /**
     * @Type("integer")
     * @SerializedName("nb-violations")
     */
    private $nbViolations;

    /**
     * @Type("DateTime")
     * @SerializedName("begin-at")
     */
    private $beginAt;

    /**
     * @Type("DateTime")
     * @SerializedName("end-at")
     */
    private $endAt;

    /** @Type("integer") */
    private $duration;

    /**
     * @Type("string")
     * @SerializedName("failure-message")
     */
    private $failureMessage;

    /**
     * @Type("string")
     * @SerializedName("failure-code")
     */
    private $failureCode;

    /** @Type("boolean") */
    private $failed;

    /** @Type("string") */
    private $status;

    /**
     * @Type("string")
     * @SerializedName("status-message")
     */
    private $statusMessage;

    /**
     * @Type("boolean")
     * @SerializedName("altered")
     */
    private $isAltered;

    /** @Type("SymfonyCorp\Insight\Sdk\Model\Violations") */
    private $violations;

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @return string
     */
    public function getNextGrade()
    {
        return $this->nextGrade;
    }

    /**
     * @return string[]
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * @return float
     */
    public function getRemediationCost()
    {
        return $this->remediationCost;
    }

    /**
     * @return float
     */
    public function getRemediationCostForNextGrade()
    {
        return $this->remediationCostForNextGrade;
    }

    /**
     * @return int
     */
    public function getNbViolations()
    {
        return $this->nbViolations;
    }

    /**
     * @return \DateTime
     */
    public function getBeginAt()
    {
        return $this->beginAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @return \DateInterval
     */
    public function getDuration()
    {
        return new \DateInterval('PT'.($this->duration ? $this->duration : '0').'S');
    }

    /**
     * @return string
     */
    public function getFailureMessage()
    {
        return $this->failureMessage;
    }

    /**
     * @return string
     */
    public function getFailureCode()
    {
        return $this->failureCode;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->failed;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return static::STATUS_FINISHED == $this->status;
    }

    /**
     * @return string One of the STATUS_* constants
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @return bool
     */
    public function isAltered()
    {
        return $this->isAltered;
    }

    /**
     * @return Violations|null
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
