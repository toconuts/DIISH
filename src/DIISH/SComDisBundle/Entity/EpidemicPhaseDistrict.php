<?php

namespace DIISH\SComDisBundle\Entity;

/**
 * EpidemicPhaseDistrict
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class EpidemicPhaseDistrict
{
    /**
     * @var integer $totalOfTargetCases 
     */
    private $totalOfTargetCases;
    
    /**
     * @var integer $totalOfCalcCases
     */
    private $totalOfCalcCases;

    /**
     * @var integer $numberOfCases
     */
    private $numberOfCalcCases;

    /**
     * @var float $average
     */
    private $average;
    
    /**
     * @var integer $sd
     */
    private $sd;
    
    /**
     * @var float $coefficient 
     */
    private $coefficient;
    
    /**
     * @var integer $phase
     */
    private $phase;
    
    /**
     * @var Clinic $clinics 
     */
    private $clinics;
    
    
    /*
     * @var District $district
     */
    private $district;


    /**
     * @var EpidemicPhase $epidemicPhase
     */
    private $epidemicPhase;

    public function __construct(EpidemicPhase $epidemicPhase, District $district)
    {
        if ($epidemicPhase === NULL || $district === NULL)
            throw new \InvalidArgumentException();

        $this->epidemicPhase = $epidemicPhase;
        $this->district = $district;
        
        $this->totalOfTargetCases = 0;
        $this->totalOfCalcCases = 0;
        $this->numberOfCalcCases = 0;
        $this->average = 0.0;
        $this->sd = NULL;
        $this->coefficient = NULL;
        $this->phase = NULL;

    }
    
    public function getDistrict()
    {
        return $this->district;
    }

    public function getId()
    {
        return $this->district->getId();
    }
    
    public function getName()
    {
        return $this->district->getName();
    }
    
    public function getPopulation()
    {
        return $this->district->getPopulation();
    }
    
    public function getRatio()
    {
        return $this->district->getRatio();
    }
    
    public function getTotalOfTargetCases()
    {
        return $this->totalOfTargetCases;
    }
        
    public function getTotalOfCalcCases()
    {
        return $this->totalOfCalcCases;
    }
    
    public function getNumberOfCalcCases()
    {
        return $this->numberOfCalcCases;
    }
    
    public function getAverage()
    {
        return $this->average;
    }
    
    public function getSD()
    {
        return $this->sd;
    }
    
    public function getCoefficient()
    {
        return $this->coefficient;
    }
    
    public function getPhase()
    {
        return $this->phase;
    }
    
    /**
     * Add clinics
     *
     * @param EpidemicPhaseClinic $clinic
     */
    public function addClinic(EpidemicPhaseClinic $clinic)
    {
        $this->clinics[$clinic->getId()] = $clinic;
    }
    
    /**
     * Set clinics
     *
     * @param array 
     */
    public function setClinics($clinics) {
        $this->clinics = $clinics;
    }

    /**
     * Get clinics
     *
     * @return array
     */
    public function getClinics()
    {
        return $this->clinics;
    }
    
    public function update()
    {
        $casesOfTarget = array();
        $casesOfCalc = array();

        $this->updateClinics($casesOfTarget, $casesOfCalc);
        $this->updateValues($casesOfTarget, $casesOfCalc);
    }
    
    public function updateValues(array $casesOfTarget, array $casesOfCalc, $islandwide = false)
    {
        $this->totalOfTargetCases = array_sum($casesOfTarget);
        if (!$islandwide && $this->epidemicPhase->isUseNoRecord() == false && count($casesOfTarget) == 0)
            $this->totalOfTargetCases = NULL;

        $this->totalOfCalcCases = array_sum($casesOfCalc);
        $this->numberOfCalcCases = count($casesOfCalc);
        
        // Calc average
        if ($this->numberOfCalcCases > 0) {
            $this->average = round((float)$this->totalOfCalcCases / 
                (float)$this->numberOfCalcCases, 3);
        } else {
            $this->average = NULL;
        }
        
        // Calc Standard Deviation
        if ($this->numberOfCalcCases > 1) {
            $this->sd = round(CommonUtils::staticsStandardDeviation($casesOfCalc, true), 3);
        } else { // Because sample true is (n - 1). Protect dividing with zero. 
            $message = "Can not calculate SD.";
            throw new \InvalidArgumentException($message);
        }
        
        // Calc Coefficient
        $this->updateCoefficient();
        
        // Judge the phase
        $this->phase = $this->epidemicPhase->judgePhase($this->coefficient);
    }
    
    protected function updateCoefficient()
    {
        if ($this->totalOfTargetCases === NULL || $this->average === NULL || $this->sd === NULL) {
            $this->coefficient = NULL;
        } else if ($this->sd == 0) {
            $this->coefficient = 0;
        } else {
            $this->coefficient = round(((float)$this->totalOfTargetCases - $this->average) / $this->sd, 3);
        }
    }

    public function updateCoefficientByIslandwideValue($islandAvg, $islandSd)
    {
        $this->average = round($islandAvg * $this->district->getRatio(), 3);
        $this->sd = $islandSd;
        $this->updateCoefficient();
    }

    public function updateClinics(array &$casesOfTarget, array &$casesOfCalc)
    {
        foreach ($this->clinics as $clinic) {
            try {
                $clinic->update();
            } catch (\InvalidArgumentException $e) {
                if (!$this->epidemicPhase->isUseLandwideSD()) {
                    $name = $clinic->getName();
                    $this->epidemicPhase->addMessage($e->getMessage() . " Clinic: $name");
                }
            }
            $clinic->getCases($casesOfTarget, $casesOfCalc);
        }
    }
    
    public function removeClinic($id)
    {
        unset($this->clinics[$id]);
    }
}
