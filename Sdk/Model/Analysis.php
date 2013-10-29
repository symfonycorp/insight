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

class Analysis
{
    const STATUS_ORDERED  = 'ordered';
    const STATUS_RUNNING  = 'running';
    const STATUS_MEASURED = 'measured';
    const STATUS_ANALYZED = 'analyzed';
    const STATUS_FINISHED = 'finished';

    /**
     * @Type("array<SensioLabs\Insight\Sdk\Model\Link>")
     * @XmlList(inline = true, entry = "link")
     */
    private $links;

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
    private $grades;

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

    /** @Type("SensioLabs\Insight\Sdk\Model\Violations") */
    private $violations;

    public function getLinks()
    {
        return $this->links;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getGrade()
    {
        return $this->grade;
    }

    public function getNextGrade()
    {
        return $this->nextGrade;
    }

    public function getGrades()
    {
        return $this->grades;
    }

    public function getRemediationCost()
    {
        return $this->remediationCost;
    }

    public function getRemediationCostForNextGrade()
    {
        return $this->remediationCostForNextGrade;
    }

    public function getNbViolations()
    {
        return $this->nbViolations;
    }

    public function getBeginAt()
    {
        return $this->beginAt;
    }

    public function getEndAt()
    {
        return $this->endAt;
    }

    public function getDuration()
    {
        return new \DateInterval('PT'.($this->duration ? $this->duration : '0').'S');
    }

    public function getFailureMessage()
    {
        return $this->failureMessage;
    }

    public function getFailureCode()
    {
        return $this->failureCode;
    }

    public function isFailed()
    {
        return $this->failed;
    }

    public function isFinished()
    {
        return static::STATUS_FINISHED == $this->status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    public function isAltered()
    {
        return $this->isAltered;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
