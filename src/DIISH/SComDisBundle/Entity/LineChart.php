<?php

namespace DIISH\SComDisBundle\Entity;


/**
 * LineChart.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 */
class LineChart
{
    /**
     * @var string $title
     */
    private $title;
    
    /**
     * @var string $targetYear
     */
    private $year;
    
    /**
     * @var array $calcYears
     */
    private $calcYears;
    
    /**
     * @var array seriesNames;
     */
    private $seriesNames;
    
    /**
     * $data[year][week][series]
       
     * @var array $data
     */
    private $data;
    
    /**
     * $lineChartData[] = array(index, series1, series2, series3,...seriesN)
     * 
     * @var array $lineChartData
     */
    private $lineChartData;
    
    /**
     * @var int $maxValue
     */
    private $maxValue;
    
    /**
     * @var boolean $useSeriesSyndromes
     */
    private $useSeriesSyndromes;
    
    /**
     * @var boolean $isUseNorecord
     */
    private $useNoRecord;
    
    /**
     * @var array $syndromes
     */
    private $syndromes;
    
    /**
     * @var array $sentinelSites;
     */
    Private $sentinelSites;
    
    /**
     * @var array $messages;
     */
    private $messages;
    
    /**
     * Constructor
     * 
     * @param string $year
     * @param array $calcYears
     * @param bool $useNorecord
     * @param bool $useSeriesSyndromes
     */
    public function __construct($year, $calcYears, $useNorecord, $useSeriesSyndromes = false)
    {
        $this->chartTitle = "Chart Title";
        $this->year = $year;
        $this->calcYears = $calcYears;
        $this->useNoRecord = $useNorecord;
        $this->useSeriesSyndromes = $useSeriesSyndromes;
        
        $this->seriesNames = array();
        
        $this->data = array();
        $this->lineChartData = array();
        
        $this->maxValue = 0;
        
        $this->sentinelSites = array();
        $this->syndromes = array();
        
        $this->messages = array();
    }
    
    /**
     * Get year
     * 
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }
    
    /**
     * Get calculation year
     * @return array
     */
    public function getCalcYears()
    {
        return $this->calcYears;
    }

    /**
     * Set series names
     * 
     * @param array $names
     */
    public function setSeriesNames(array $names) {
        $this->seriesNames = array();
        $this->seriesNames[0] = 'x';
        foreach ($names as $index => $name) {
            $this->seriesNames[$index + 1] = $name;
        }
    }
    
    /**
     * Get series names
     * 
     * @return array
     */
    public function getSeriesNames() {
        return $this->seriesNames;
    }
    
    /**
     * Set chart data
     * 
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->createLineChartData();
    }
    
    /**
     * Create data
     * 
     * data[year][week][series]
     * lineChartData[] = array(index, series1, series2, series3,...seriesN)
     */
    public function createLineChartData()
    {        
        $this->maxValue = 0;
        $this->lineChartData = array();
        
        $index = 0;
        foreach ($this->data as $numberOfYear => $year) {
            foreach ($year as $numberOfWeek => $week) {
                if ($numberOfWeek === 1) {
                    $this->lineChartData[$index][0] = "$numberOfYear-$numberOfWeek";
                } elseif ($this->isLabeledWeek ($numberOfWeek)) {
                    $this->lineChartData[$index][0] = (string)$numberOfWeek;
                } else {
                    $this->lineChartData[$index][0] = '';
                }
                foreach ($week as $series => $value) {
                    $this->lineChartData[$index][$series + 1] = $value;
                    $this->updateMaxValue($value);
                }
                $index++;
            }
        }
    }
    
    /**
     * Get line data
     * @return array
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Get line chart data
     * 
     * @return array
     */
    public function getLineChartData() {
        return $this->lineChartData;
    }
    
    /**
     * Set max value
     * 
     * @param int $value
     */
    public function setMaxValue($value) {
        $this->maxValue = $value;
    }
    
    /**
     * Get max value
     * 
     * @return int
     */
    public function getMaxValue() {
        return $this->maxValue;
    }
    
    /**
     * Set title
     * 
     * @param string $value
     */
    public function setTitle($value) {
        $this->title = $value;
    }
    
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Add syndrome
     *
     * @param Syndrome4Surveillance $syndrome
     */
    public function addSyndrome(Syndrome4Surveillance $syndrome)
    {
        $this->syndromes[] = $syndrome;
    }
    
    /**
     * Get syndrome
     * 
     * @return array
     */
    public function getSyndromes()
    {
        return $this->syndromes;
    }
    
    /**
     * Add sentinelSite
     *
     * @param SentinelSite $sentinelSite
     */
    public function addSentinelSite(SentinelSite $sentinelSite)
    {
        $this->sentinelSites[] = $sentinelSite;
    }
    
    /**
     * Set sentinelSites
     *
     * @param array $sentinelSites
     */
    public function setSentinelSites($sentinelSites)
    {
        $this->sentinelSites = $sentinelSites;
    }
    
    /**
     * Get sentinelSites
     *
     * @return array $sentinelSites
     */
    public function getSentinelSites()
    {
        return $this->sentinelSites;
    }
    
    /**
     * Add message
     * 
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }
    
    /**
     * Get messages
     * 
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * Check whether to use no record as 0 case or not
     * 
     * @return bool
     */
    public function isUseNoRecord()
    {
        return $this->useNoRecord;
    }
    
    /**
     * Check whether to use syndromes as series or not
     * 
     * @return bool
     */
    public function isUseSeriesSyndromes()
    {
        return $this->useSeriesSyndromes;
    }

    protected function isLabeledWeek($week) {
        if ($week ==  1 || 
            $week ==  5 || 
            $week ==  9 || 
            $week == 13 || 
            $week == 17 ||
            $week == 21 || 
            $week == 25 || 
            $week == 29 || 
            $week == 33 || 
            $week == 37 ||
            $week == 41 || 
            $week == 45 || 
            $week == 49) {
            return true;
        }
        return false;
    }
    
    protected function updateMaxValue($value) {
        if ($this->maxValue < $value)
            $this->maxValue = $value;
    }

}
