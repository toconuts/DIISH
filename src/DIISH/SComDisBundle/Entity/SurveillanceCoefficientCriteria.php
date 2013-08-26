<?php

namespace DIISH\SComDisBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurveillanceCoefficientCriteria.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class SurveillanceCoefficientCriteria
{
    /**
     * @var id $targetYear 
     */
    private $targetYear;
    
    /**
     * @var datetime $weekend
     */
    private $weekend;
    
    /**
     * @var integer $weekOfYear
     */
    private $weekOfYear;
    
    /**
     * @var integer $year
     */
    private $year;
    
    /**
     * @var array $yearChoices 
     */
    private $yearChoices;
    
    /**
     * @var array $syndromes
     */
    private $syndromes;

    /**
     * @var bool $useNoRecords 
     */
    private $useNoRecords;
    
    /**
     * @var bool $useIslandwideSD 
     */
    private $useLandwideSD;
    
    /**
     * @var bool $showIslandwide
     */
    private $showIslandwide;
    
    /**
     * @var bool $modeSpecificWeek;
     */
    private $modeSpecificWeek;

    /**
     * Constructor
     * 
     * @param array $syndromes
     */
    public function __construct(array $syndromes)
    {
        $this->setYears();
        $this->targetYear = 0;
        $this->weekend = new \DateTime('last Saturday');
        $this->weekend->setTime(0, 0, 0);
        $this->weekOfYear = strftime('%V', time());
        $this->year = strftime('%G', time());
        $this->syndromes = $syndromes;
        $this->useNoRecords = true;
        $this->useLandwideSD = false;
        $this->showIslandwide = false;
        $this->modeSpecificWeek = false;
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
     * @return string $targetYear
     */
    public function getTargetYear()
    {
        return $this->targetYear;
    }
    
    /**
     * Set weekend
     *
     * @param datetime $weekend
     */
    public function setWeekend($weekend)
    {
        $this->weekend = $weekend;
    }

    /**
     * Get weekend
     *
     * @return datetime 
     */
    public function getWeekend()
    {
        return $this->weekend;
    }
    
    /**
     * Set weekOfYear
     *
     * @param integer $weekOfYear
     */
    public function setWeekOfYear($weekOfYear)
    {
        $this->weekOfYear = $weekOfYear;
    }

    /**
     * Get weekOfYear
     *
     * @return integer 
     */
    public function getWeekOfYear()
    {
        return $this->weekOfYear;
    }

    /**
     * Set year
     *
     * @param integer $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set years
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
     * 
     * @param array $syndromes
     */
    public function setSyndromes(array $syndromes) {
        $this->syndromes = $syndromes;
    }
    
    /**
     * Get syndromes
     * 
     * @return srray
     */
    public function getSyndromes() {
        return $this->syndromes;
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
     * Check whether useNoRecords is true or false
     * 
     * @return bool
     */
    public function isUseNoRecords()
    {
        return $this->useNoRecords;
    }
    
    /**
     * Set useLandwideSD
     * 
     * @param bool $value
     */
    public function setUseLandwideSD($value)
    {
        $this->useLandwideSD = $value;
    }
    
    /**
     * Check whether useLandwideSD is true or false
     * @return bool
     */
    public function isUseLandwideSD()
    {
        return $this->useLandwideSD;
    }
    
    /**
     * Set showIslandewide
     * 
     * @param bool $value
     */
    public function setShowIslandwide($value)
    {
        $this->showIslandwide = $value;
    }
    
    /**
     * Check showIslandewide is true or false
     * @return bool
     */
    public function isShowIslandwide()
    {
        return $this->showIslandwide;
    }
    
    /**
     * Set modeSpecificWeek
     * 
     * @param true $value
     */
    public function setModeSpecificWeek($value)
    {
        $this->modeSpecificWeek = $value;
    }
    
    /**
     * Check modeSpecificWeek is true or false
     * 
     * @return bool
     */
    public function isModeSpecificWeek()
    {
        return $this->modeSpecificWeek;
    }
}