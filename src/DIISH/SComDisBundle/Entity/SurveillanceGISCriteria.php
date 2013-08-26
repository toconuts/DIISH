<?php

namespace DIISH\SComDisBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurveillanceGISCriteria.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class SurveillanceGISCriteria extends SurveillancePredictionCriteria
{
    /**
     * @var bool $useNoRecords 
     */
    private $useThresholdInEach;

    /**
     * Constructor
     * 
     * @param array $syndromes
     */
    public function __construct(array $syndromes)
    {
        parent::__construct($syndromes, array());
        $this->useThresholdInEach = false;
    }
    
    /**
     * Set useThresholdInEach
     * 
     * @param bool $value
     */
    public function setUseThresholdInEach($value)
    {
        $this->useThresholdInEach = $value;
    }
    
    /**
     * Check useThresholdInEach is true or false
     * 
     * @return bool
     */
    public function isUseThresholdInEach()
    {
        return $this->useThresholdInEach;
    }
}