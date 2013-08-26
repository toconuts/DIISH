<?php

namespace DIISH\SComDisBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurveillancePredictionCriteria.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class SurveillancePredictionCriteria extends SurveillanceTrendCriteria
{
    /**
     * @var string $targetYear 
     */
    private $targetYear;

    /**
     * @var bool $useNoRecords 
     */
    private $useNoRecords;

    /**
     * @var int $confidenceCoefficient
     */
    private $confidenceCoefficient;

    /**
     * Constructor
     * 
     * @param array $syndromes
     * @param array $sentinelSites
     */
    public function __construct(array $syndromes, array $sentinelSites)
    {
        parent::__construct($syndromes, $sentinelSites);
        $this->targetYear = 0;
        $this->useNoRecords = true;
        $this->confidenceCoefficient = 1.645;
    }
    
    /**
     * Set targetYear
     * 
     * @param string $value
     */
    public function setTargetYear($value)
    {
        $this->targetYear = $value;
    }
    
    /**
     * Get targetYear
     * 
     * @return string
     */
    public function getTargetYear()
    {
        return $this->targetYear;
    }

    /**
     * Set useNoRecords
     * 
     * @param bool $value
     */
    public function setUseNoRecords($value)
    {
        $this->useNoRecords = $value;
    }
    
    /**
     * Check Whether useNoRecords is true or false
     * 
     * @return type
     */
    public function isUseNoRecords()
    {
        return $this->useNoRecords;
    }
    
    /**
     * Set conficenceCoefficient
     * 
     * @param float $value
     */
    public function setConfidenceCoefficient($value)
    {
        $this->confidenceCoefficient = $value;
    }
    
    /**
     * Get confidenceCoefficient
     * 
     * @return float
     */
    public function getConfidenceCoefficient()
    {
        return $this->confidenceCoefficient;
    }
}