<?php

namespace DIISH\SComDisBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurveillanceTrendCriteria.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class SurveillanceTrendCriteria
{
    /**
     * @var array $yearChoices 
     */
    private $yearChoices;
    
    /**
     * @var array $syndromes
     */
    private $syndromes;

    /**
     * @var array $sentinelSites
     */
    private $sentinelSites;
    
    /**
     * 
     * @var type 
     */
    private $useSeriesSyndromes;

    /**
     * Constructor
     * 
     * @param array $syndromes
     * @param array $sentinelSites
     */
    public function __construct(array $syndromes, array $sentinelSites)
    {
        $this->syndromes = $syndromes;
        $this->sentinelSites = $sentinelSites;
        $this->setYears();
        $this->useSeriesSyndromes = false;
    }
    
    /**
     * Set yearChoices
     */
    public function setYears() {
        $year = date('Y');
        for ($i = $year; $year >= CommonUtils::$BEGINING_YEAR; $year--){
            $this->yearChoices[] = $year;
        }
    }

    /**
     * Set yearChoices
     * 
     * @param array $years
     */
    public function setYearChoices(array $years) {
        $this->yearChoices = $years;
    }
    
    /**
     * Get yearChoices
     * 
     * @return array
     */
    public function getYearChoices() {
        return $this->yearChoices;
    }
    
    /**
     * Set syndromes
     * @param array $syndromes
     */
    public function setSyndromes(array $syndromes) {
        $this->syndromes = $syndromes;
    }
    
    /**
     * Get syndromes
     * @return array
     */
    public function getSyndromes() {
        return $this->syndromes;
    }
    
    /**
     * Set sentinelSites
     * 
     * @param array $sentinelSites
     */
    public function setSentinelSites(array $sentinelSites) {
        $this->sentinelSites = $sentinelSites;
    }
    
    /**
     * Get sentinelSites
     * 
     * @return array
     */
    public function getSentinelSites() {
        return $this->sentinelSites;
    }
    
    /**
     * Set useSeriesSyndromes
     * 
     * @param bool $value
     */
    public function setUseSeriesSyndromes($value)
    {
        $this->useSeriesSyndromes = $value;
    }
    
    /**
     * Check useSeriesSyndromes is true or false
     * 
     * @return bool
     */
    public function isUseSeriesSyndromes() {
        return $this->useSeriesSyndromes;
    }
}