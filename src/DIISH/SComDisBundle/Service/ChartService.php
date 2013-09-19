<?php

namespace DIISH\SComDisBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use DIISH\SComDisBundle\Entity\SurveillanceRepository;
use DIISH\SComDisBundle\Entity\Surveillance;
Use DIISH\SComDisBundle\Entity\SurveillanceTrendCriteria;
use DIISH\SComDisBundle\Entity\SurveillancePredictionCriteria;
use DIISH\SComDisBundle\Entity\SurveillanceCoefficientCriteria;
use DIISH\SComDisBundle\Entity\CommonUtils;
use DIISH\SComDisBundle\Entity\GoogleLineChart;
use DIISH\SComDisBundle\Entity\LineChart;


/**
 * Chart Service for Syndromic Surveillance
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class ChartService
{
    /**
     * @var RegistryInterface $managerRegistry
     */
    private $managerRegistry;
    
    /**
     * @var int $numberOfRecord
     */
    private $numberOfRecord;

    /**
     * Constructor
     * 
     * @param RegistryInterface $managerRegistry 
     */
    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->numberOfRecord = array();
    }
    
    /**
     * Create trend chart
     * 
     * @param \DIISH\SComDisBundle\Entity\SurveillanceTrendCriteria $criteria
     * @return array
     */
    public function createTrendChart(SurveillanceTrendCriteria $criteria)
    {
        set_time_limit(600);
        ini_set("memory_limit", "1G");

        $lineChart = new LineChart(0, $criteria->getYearChoices(), false, $criteria->isUseSeriesSyndromes());
        $lineChart->setTitle("Epidemic Trend");
        $this->setSyndromes($lineChart, $criteria->getSyndromes());
        $this->setSentinelSites($lineChart, $criteria->getSentinelSites());
        $this->setSeriesNames($lineChart);
        
        $data = $this->getTrendChartData($criteria);
        $lineChart->setData($data);
        
        return $lineChart;
    }
    
    /**
     * Create prediction chart
     * 
     * @param \DIISH\SComDisBundle\Entity\SurveillancePredictionCriteria $criteria
     * @return array
     */
    public function createPredictionChart(SurveillancePredictionCriteria $criteria)
    {
        set_time_limit(600);
        ini_set("memory_limit", "1G");
        
        $lineChart = new LineChart($criteria->getTargetYear(), $criteria->getYearChoices(), $criteria->isUseNoRecords(), false);
        $lineChart->setTitle("Epidemic Detection");
        $this->setSyndromes($lineChart, $criteria->getSyndromes());
        $this->setSentinelSites($lineChart, $criteria->getSentinelSites());
        $this->setSeriesNames($lineChart, false);
        
        $targetYearData = $this->getTargetYearData($criteria);
        $predictionData = $this->getPredictionData($targetYearData, $criteria);
        $lineChart->setData($predictionData, true);
        
        return $lineChart;
    }
    
    protected function setSyndromes(LineChart $lineChart, $ids)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');
        
        foreach($ids as $id) {
            $lineChart->addSyndrome($repository->find($id));
        }
    }
    
    protected function setSentinelSites(LineChart $lineChart, $ids)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $repository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        
        foreach($ids as $id) {
            $lineChart->addSentinelSite($repository->find($id));
        }
    }
    
    protected function setSeriesNames(LineChart $lineChart, $trend = true)
    {
        $seriesNames = array();
        
        if ($trend) {
            $syndromes = $lineChart->getSyndromes();
            foreach ($syndromes as $index => $syndrome) {
                if ($lineChart->isUseSeriesSyndromes()) {
                    $seriesNames[] = $syndrome->getName();
                } else {
                    if ($index == 0) {
                        $seriesNames[0] = $syndrome->getName();
                    } else {
                        $seriesNames[0] = $seriesNames[0] . ', ' . $syndrome->getName();
                    }
                }
            }
        } else { // for prediction
            $targetYear = $lineChart->getYear();
            $seriesNames = array("Number in $targetYear", "Total of calc. year", "Sample Size", "Average", "Std Deviation", "Margin of Error","Threshold");
        }
        
        $lineChart->setSeriesNames($seriesNames);
    }
    
    protected function getTrendChartData(SurveillanceTrendCriteria $criteria, $initialValue = 0)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $surveillances = $manager->getRepository('DIISHSComDisBundle:Surveillance')
                ->findAllByYearAndSentinelSite(
                        $criteria->getYearChoices(),
                        $criteria->getSentinelSites()
                )
        ;
        
        $syndromeChoices = $criteria->getSyndromes();
        $numberOfSeries = $criteria->isUseSeriesSyndromes() ? count($syndromeChoices) : 1;
        if ($criteria->isUseSeriesSyndromes()) {
            for ($i = 0; $i < count($syndromeChoices); $i++) {
                $this->numberOfRecord[$syndromeChoices[$i]] = 0;
            }
        } else {
            $this->numberOfRecord[0] = 0;
        }

        $series = array_fill(0, $numberOfSeries, $initialValue);
        
        $week52 = array_fill(1, 52, $series);
        $week53 = array_fill(1, 53, $series);
        $yearChoices =  $criteria->getYearChoices();
        sort($yearChoices);
        
        $trends = array();
        
        for ($i = 0; $i < count($yearChoices); $i++) {
            if (CommonUtils::is53EPIWeekInYear($yearChoices[$i])) {
                $trends[$yearChoices[$i]] = $week53;
            } else {
                $trends[$yearChoices[$i]] = $week52;
            }
        }
        
        foreach ($surveillances as $surveillance) {
            $total = 0;
            for ($i = 0; $i < count($syndromeChoices); $i++) {
                $surveillanceItem = $surveillance->getSurveillanceItemBySyndrome($syndromeChoices[$i]);
                if ($criteria->isUseSeriesSyndromes()) {
                    if (-1 === $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][$i])
                        $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][$i] = 0;
                    $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][$i] +=  $surveillanceItem->getTotal();
                    $this->numberOfRecord[$syndromeChoices[$i]];
                } else {
                    $total += $surveillanceItem->getTotal();
                }
            }
            if ($criteria->isUseSeriesSyndromes() === false) {
                if (-1 === $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][0])
                    $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][0] = 0;
                $trends[$surveillance->getYear()][$surveillance->getWeekOfYear()][0] += $total;
                $this->numberOfRecord[0]++;
            }
        }
        
        return $trends;
    }

    protected function getTargetYearData(SurveillancePredictionCriteria $criteria)
    {        
        $calcYears = $criteria->getYearChoices();
        
        $targetYear = $criteria->getTargetYear();
        $criteria->setYearChoices(array("$targetYear"));
        
        $data = $this->getTrendChartData($criteria);
        
        $criteria->setYearChoices($calcYears);
        
        return $data;
    }
    
    protected function getPredictionData(array $targetYearData, SurveillancePredictionCriteria $criteria)
    {
        // CalcData = [year][week][series = 0]
        $calcData = $this->getTrendChartData($criteria, -1);
        
        $series = array_fill(0, 7/*Number, total number of calc, SampleSize, AVG, STD, MoE, Threshold*/, 0);
        
        $weeks = array();
        $prediction = array();

        if (CommonUtils::is53EPIWeekInYear($criteria->getTargetYear())){
            $weeks = array_fill(1, 53, $series);
        } else {
            $weeks = array_fill(1, 52, $series);
        }

        $prediction[$criteria->getTargetYear()] = $weeks;
                
        $yearChoices =  $criteria->getYearChoices();
        sort($yearChoices);

        $useNoRecords = $criteria->isUseNoRecords();
        $confidenceCoefficient = $criteria->getConfidenceCoefficient();

        foreach ($prediction[$criteria->getTargetYear()] as $week => $weekSet) {
            
            $values = array();
            foreach ($yearChoices as $year) {
                if ($calcData[$year][$week][0] !== -1) {
                    $values[] = $calcData[$year][$week][0];
                } else if ($useNoRecords === true) {
                    $values[] = 0;
                }
            }
            
            // Number
            $prediction[$criteria->getTargetYear()][$week][0] = $targetYearData[$criteria->getTargetYear()][$week][0];
            
            // Total number of calc. year
            $prediction[$criteria->getTargetYear()][$week][1] = array_sum($values);
            
            // Sample Size
            if (count($values) < 2) { // Because of sample = true = (n - 1) when calcurate STDDEV. 
                $message = "Can not calculate average because there are not enough records. Week: ${week}. Please try to check \"no records as 0 case\" option.";
                throw new \InvalidArgumentException($message);
            }
            $prediction[$criteria->getTargetYear()][$week][2] = count($values);
            
            // Avg
            $prediction[$criteria->getTargetYear()][$week][3] = round((float)($prediction[$criteria->getTargetYear()][$week][1] / $prediction[$criteria->getTargetYear()][$week][2]), 2);
            
            // Standard Deviation
            $prediction[$criteria->getTargetYear()][$week][4] = round(CommonUtils::staticsStandardDeviation($values, true), 2);
            
            // Margin of Error
            $prediction[$criteria->getTargetYear()][$week][5] = round(($prediction[$criteria->getTargetYear()][$week][4] * $confidenceCoefficient) / sqrt(count($values)), 2);
            
            // Threshold
            $prediction[$criteria->getTargetYear()][$week][6] = $prediction[$criteria->getTargetYear()][$week][3] + $prediction[$criteria->getTargetYear()][$week][5];            
        }
        
        return $prediction;
    }
}
