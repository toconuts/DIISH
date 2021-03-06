<?php

namespace DIISH\SComDisBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;

use DIISH\SComDisBundle\Entity\SurveillanceRepository;
use DIISH\SComDisBundle\Entity\Surveillance;
use DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria;
use DIISH\SComDisBundle\Entity\EpidemicPhase;
use DIISH\SComDisBundle\Entity\EpidemicPhaseClinic;
use DIISH\SComDisBundle\Entity\District;
use DIISH\SComDisBundle\Entity\Clinic;
use DIISH\SComDisBundle\Entity\Phase;
use DIISH\SComDisBundle\Entity\PMH;
use DIISH\SComDisBundle\Entity\EpidemicPhaseDistrict;
use DIISH\SComDisBundle\Entity\CommonUtils;

/**
 * Epidemic Phase Service for Syndromic Surveillance
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class EpidemicPhaseService
{
    /**
     * @var RegistryInterface $managerRegistry
     */
    private $managerRegistry;
    
    /**
     * Constructor
     * 
     * @param RegistryInterface $managerRegistry 
     */
    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    /**
     * Create seasonal coefficient
     * 
     * @param \DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria $criteria
     * @return \DIISH\SComDisBundle\Entity\EpidemicPhase
     */
    public function createSeasonalCoefficient(SurveillanceCoefficientCriteria $criteria)
    {
        $criteria->setWeekOfYear(0);
        return $this->createEpidemicPhaseObject($criteria);
    }

    /**
     * Create EpidemicPhase
     * 
     * @param \DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria $criteria
     * @return \DIISH\SComDisBundle\Entity\EpidemicPhase
     */
    public function createEpidemicPhase(SurveillanceCoefficientCriteria $criteria)
    {
        $criteria->setModeSpecificWeek(true);
        $criteria->setTargetYear(CommonUtils::getEPIYear($criteria->getWeekend()));
        return $this->createEpidemicPhaseObject($criteria);
    }
    
    /**
     * Create EpidemicPhaseObject
     * 
     * @param \DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria $criteria
     * @return \DIISH\SComDisBundle\Entity\EpidemicPhase
     */
    public function createEpidemicPhaseObject(SurveillanceCoefficientCriteria $criteria)
    {
        set_time_limit(600);
        ini_set("memory_limit", "1G");

        $epidemicPhase = new EpidemicPhase(
                $criteria->getTargetYear(),
                $criteria->getWeekOfYear(),
                $criteria->getYearChoices(),
                $criteria->isUseNoRecords(),
                $criteria->isUseLandwideSD(),
                $criteria->isShowIslandwide(),
                $criteria->isShowOnlyIslandwide()
        );
        
        if ($criteria->isModeSpecificWeek()) {
            $epidemicPhase->setTitle('Epidemic Phase');
        } else {
            $epidemicPhase->setTitle('Seasonal Coefficient');
        }
        
        $this->setSyndromes($epidemicPhase, $criteria->getSyndromes());
        
        $this->setDistricts($epidemicPhase, $criteria);
        
        if ($criteria->isModeSpecificWeek()) {
            $this->mergePMH($epidemicPhase);
        }
        
        $this->setPhase($epidemicPhase);
        
        $epidemicPhase->analyzeCoefficient();
        
        return $epidemicPhase;
    }
    
    protected function mergePMH(EpidemicPhase $epidemicPhase)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository = $manager->getRepository('DIISHSComDisBundle:PMH');
        $pmh = $repository->findAll();
        $epidemicPhase->mergePMH($pmh);
    }
    
    protected function setPhase(EpidemicPhase $epidemicPhase)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository = $manager->getRepository('DIISHSComDisBundle:Phase');
        $phases = $repository->findAll();
        $epidemicPhase->setPhases($phases);
    }
    
    protected function setSyndromes(EpidemicPhase $epidemicPhase, $ids)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        
        foreach($ids as $id) {
            $epidemicPhase->addSyndrome($repository->find($id));
        }
    }
   
    protected function setDistricts(EpidemicPhase $epidemicPhase, SurveillanceCoefficientCriteria $criteria)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository  = $manager->getRepository('DIISHSComDisBundle:Surveillance');

        // Create epidemic phase district objects
        $districts = $this->getEpidemicPhaseDistrict($epidemicPhase, $criteria);
        $epidemicPhase->setDistricts($districts);
        
        // TargetYear
        $targetSurveillance = array();
        $targetYear[] = $criteria->getTargetYear();
        if ($criteria->isModeSpecificWeek()) {
            $targetSurveillance = $repository->findAllBySpecificWeek($targetYear, $criteria->getWeekOfYear());
        } else {
            $targetSurveillance = $repository->findAllByYear($targetYear);
        }
        $this->setTargetYearData($epidemicPhase, $targetSurveillance);
        
        // CalcYear
        $calcSurveillances = array();
        if ($criteria->isModeSpecificWeek()) {
            $calcSurveillances = $repository->findAllBySpecificWeek($criteria->getYearChoices(), $criteria->getWeekOfYear());
        } else {
            $calcSurveillances = $repository->findAllByYear($criteria->getYearChoices());
        }
        $this->setCalcYearsData($epidemicPhase, $calcSurveillances);
    }
    
    protected function getEpidemicPhaseDistrict(
            EpidemicPhase $epidemicPhase,
            SurveillanceCoefficientCriteria $criteria)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $districts = $manager->getRepository('DIISHSComDisBundle:District')->findAll();
        
        $epidemicPhaseDistricts = array();
        if ($criteria->isModeSpecificWeek()) {
            $epidemicPhaseDistricts[$criteria->getWeekOfYear()] = $this->createEpidemicPhaseDistrictInstance($epidemicPhase, $districts, $criteria->getYearChoices());
        } else {
            $endweek = CommonUtils::is53EPIWeekInYear($criteria->getTargetYear()) ? 53 : 52;
            for ($i = 1; $i <= $endweek; $i++) {
                $epidemicPhaseDistricts[$i] = $this->createEpidemicPhaseDistrictInstance($epidemicPhase, $districts, $criteria->getYearChoices());
            }
        }
        
        return $epidemicPhaseDistricts;
    }
    
    protected function createEpidemicPhaseDistrictInstance(EpidemicPhase $epidemicPhase, $districts, $years)
    {
        $epidemicPhaseDistricts = null;

        foreach ($districts as $district) {
            $epidemicPhaseDistrict = new EpidemicPhaseDistrict($epidemicPhase, $district);
            $epidemicPhaseDistrict->setClinics($this->createEpidemicPhaseClinic($epidemicPhase, $district, $years));
            $epidemicPhaseDistricts[$district->getId()] = $epidemicPhaseDistrict;
        }
        
        return $epidemicPhaseDistricts;
    }
    
    protected function createEpidemicPhaseClinic(EpidemicPhase $epidemicPhase, District $district, $years)
    {
        $clinics = $district->getClinics();
        $epidemicPhaseClinics = array();
        
        foreach ($clinics as $clinic) {
            $epidemicPhaseClinics[$clinic->getId()] = new EpidemicPhaseClinic($epidemicPhase, $clinic, $years);
        }
        
        return $epidemicPhaseClinics;
    }
    
    protected function setTargetYearData(EpidemicPhase $epidemicPhase, $surveillances)
    {
        $this->setData($epidemicPhase, $surveillances, true);
    }
    
    protected function setCalcYearsData(EpidemicPhase $epidemicPhase, $surveillances)
    {
        $this->setData($epidemicPhase, $surveillances, false);
    }
    
    protected function setData(EpidemicPhase $epidemicPhase, $surveillances, $isTarget)
    {
        foreach ($surveillances as $surveillance) {
            $surveillanceItems = $surveillance->getSurveillanceItems();
            $total = 0;
            $endweek = CommonUtils::is53EPIWeekInYear($epidemicPhase->getYear()) ? 53 : 52;;
            foreach ($surveillanceItems as $surveillanceItem) {
                if ($epidemicPhase->isTargetSyndrome($surveillanceItem->getSyndrome()->getId())) {
                    $total += $surveillanceItem->getTotal();
                }
            }
            
            $week = $surveillance->getWeekOfYear();
            $clinic = $surveillance->getClinic();
            $clinicId = $clinic->getId();
            $districtId = $clinic->getDistrict()->getId();
            if ($isTarget) {
                $epidemicPhase->setCasesOfTarget($week, $districtId, $clinicId, $total);
            } else {                
                if ($week <= $endweek) {
                    $year = $surveillance->getYear();
                    $epidemicPhase->setCasesOfCalc($week, $districtId, $clinicId, $year, $total);
                }
            }
        }
    }
}
