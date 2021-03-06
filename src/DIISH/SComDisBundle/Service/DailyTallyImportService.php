<?php

namespace DIISH\SComDisBundle\Service;
          
use Symfony\Bridge\Doctrine\RegistryInterface;

use DIISH\CommonBundle\Entity\Document;

use DIISH\SComDisBundle\Entity\SurveillanceRepository;
use DIISH\SComDisBundle\Entity\Surveillance;
use DIISH\SComDisBundle\Entity\CommonUtils;

/**
 * Daily Tally Import Service for Syndromic Surveillance
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class DailyTallyImportService
{
    /**
     * @var RegistryInterface $managerRegistry
     */
    private $managerRegistry;
    
    /**
     * @var array $logInfo
     */
    private $logInfo;
    
    /**
     * @var array $logWarn
     */
    private $logWarn;
    
    /**
     * @var string $errorMessage
     */
    private $errorMessage;
    
    /**
     * @var int $importedRecordsNumber
     */
    private $importedRecordsNumber;
    
    /**
     * @var \DIISH\SComDisBundle\Entity\SurveillanceRepository $surveillanceRepository
     */
    private $surveillanceRepository;
    
    /**
     * @var \DIISH\SComDisBundle\Entity\SentinelSiteRepository $sentinelSiteRepository
     */
    private $sentinelSiteRepository;
    
    /**
     * @var \DIISH\SComDisBundle\Entity\ClinicRepository $clinicRepository
     */
    private $clinicRepository;
    
    /**
     * @var \DIISH\SComDisBundle\Entity\Syndrome4SurveillanceRepository $syndromeRepository
     */
    private $syndromeRepository;
    
    /**
     * @var array $CNV_TBL_SENTINEL_CODE
     */
    public static $CNV_TBL_SENTINEL_CODE = array(
        1 => 1,     // MARIGOT
        2 => 2,     // GRAND BAY
        3 => 3,     // ST JOSEPH
        4 => 4,     // LA PLAINE
        5 => 5,     // CASTLE BRUCE
        6 => 6,     // POTSMOUTH
        7 => 7,     // ROSEAU
        8 => 8,     // PMH
                    // 9 is ROSS UNIVERSITY but only exists new system
    );
    
    /**
     * @var array $CNV_TBL_CLINIC_CODE
     */
    public static $CNV_TBL_CLINIC_CODE = array(
        /* MARIGOT */
        'M1' => 1001,
        'M2' => 1002,
        'M3' => 1003,
        'M4' => 1004,
        'M5' => 1005,
        
        /* GRAND BAY */
        'G1' => 2001,
        'G2' => 2002,
        'G3' => 2003,
        'G4' => 2004,
        'G5' => 2005,
        
        /* ST JOSEPH */
        'J1' => 3001,
        'J2' => 3002,
        'J3' => 3003,
        'J4' => 3004,
        'J5' => 3005,
        
        /* LA PLAINE */
        'L1' => 4001,
        'L2' => 4002,
        'L3' => 4003,
        'L4' => 4004,
        'L5' => 4005,
        
        /* CASTLE BRUCE */
        'C1' => 5001,
        'C2' => 5002,
        'C3' => 5003,
        'C4' => 5004,
        'C5' => 5005,
        
        /* PORTSMOUTH */
        'P1' => 6001,
        'P2' => 6002,
        'P3' => 6003,
        'P4' => 6004,
        'P5' => 6005,
        'P6' => 6006,
        'P7' => 6007,
        'P8' => 6008,
        
        /* ROSEAU */
        /* ROSEAU CENTRAL */
        'RC'  => 7101,
        
        /* ROSEAU NORTH */
        'RN1' => 7201,
        'RN2' => 7202,
        'RN3' => 7203,
        'RN4' => 7204,
        'RN5' => 7205,
        // 7206 new system also have Campbell
        // 7207 new system also have Warner
        
        /* ROSEAU SOUTH */
        'RS1' => 7301,
        'RS2' => 7302,
        'RS3' => 7303,
        'RS4' => 7304,
        
        /* ROSEAU VALLEY */
        'RV1' => 7401,
        'RV2' => 7402,
        'RV3' => 7403,
        'RV4' => 7404,
        'RV5' => 7405,
        'RV6' => 7406,
        'RV7' => 7407,
        
        /* PMH */
        '2' => 8002,
        '3' => 8003,
        '4' => 8004,
        '5' => 8005,
        
        /* ROSS UNIVERSITY */
        'P9' => 9001,    // also need to change sentinel code 6 -> 9
    );
    
    /**
     * @var int $ROSUNIVERSITY_ID
     */
    public static $ROSSUNIVERSITY_ID = 9001;
    
    /**
     * @var array $CNV_TBL_SYNDROME_OFFSET
     */
    public static $CNV_TBL_SYNDROME_OFFSET = array(
         1 =>  5,   // Gastroenteritis < 5
         2 => 13,   // Gastroenteritis >= 5
         3 => 21,   // Undifferentiated Fever < 5
         4 => 29,   // Undifferentiated Fever >= 5
         5 => 37,   // Ferver & Neurological Symptoms
         6 => 45,   // Ferver & Hemorrhagic Symptoms
         7 => 53,   // ARI < 5
         8 => 61,   // ARI >= 5
         9 => 77,   // Skin Rash
        10 => 69,   // Conjunctivitis
        11 => 85,   // Genital Discharge
        12 => 93,   // Genital Ulcer
    );
    
    /**
     * Constructor.
     * 
     * @param RegistryInterface $managerRegistry 
     */
    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logInfo = array();
        $this->logWarn = array();
        $this->errorMessage = '';
        $this->importedRecordsNumber = 0;
    }
    
    /**
     * @return array 
     */
    public function getLogInfo()
    {
        return $this->logInfo;
    }
    
    /**
     * @return array
     */
    public function getLogWarn()
    {
        return $this->logWarn;
    }
    
    /**
     * @return array
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    /**
     * @return int
     */
    public function getImportedRecordsNumber()
    {
        return $this->importedRecordsNumber;
    }
    
    /**
     * Import
     * 
     * @param Document $document
     * @param boolean $isLegacy
     * @return boolean
     */
    public function import(Document $document, $isLegacy = true)
    {
        set_time_limit(600);
        ini_set("memory_limit", "1G");
        
        $manager = $this->managerRegistry->getManager('common');
        
        $this->clear();
        $this->logInfo("Start Importing");
        
        $result = false;
        
        if ($isLegacy) {
            $result = $this->importFromLegacySys($document);    
        } else {
            $result = $this->importFromNewSys($document);
        }
        
        // Update status of the importing
        $document->setStatus($result ? "Success" : "Failure");
        $manager->persist($document);
        $manager->flush();
        
        $this->logInfo("End Importing. Sutatus: ". ($result ? 'Success' : 'Failure'));
        
        return $result;
    }
    
    protected function importFromLegacySys(Document $document)
    {
        $manager = $this->managerRegistry->getManager('scomdis');
        $this->surveillanceRepository = $manager->getRepository('DIISHSComDisBundle:Surveillance');
        $this->sentinelSiteRepository = $manager->getRepository('DIISHSComDisBundle:SentinelSite');
        $this->clinicRepository = $manager->getRepository('DIISHSComDisBundle:Clinic');
        $this->syndromeRepository = $manager->getRepository('DIISHSComDisBundle:Syndrome4Surveillance');     
        $syndromes = $this->syndromeRepository->findAll();
        
        try {
            $manager->getConnection()->beginTransaction();
        
            $filepath = $document->getAbsolutePath();
            $handle = fopen($filepath, "r");
            while ($csv = fgetcsv($handle)) {
                $res = mb_convert_variables("UTF-8", "SJIS-win", $csv);
                if (!$res) {
                    $message = "Importing Error Occured. Can not convert Multi Byte. ID:".$csv[0];
                    throw new \InvalidArgumentException($message);
                }
                
                $surveillance = new Surveillance($syndromes);
                $res = $this->convert($surveillance, $csv);
                if ($res) {
                    try {
                        $this->surveillanceRepository->saveSurveillance($surveillance);
                    } catch (\InvalidArgumentException $e) {
                        $message = $e->getMessage()." - ID: ".$csv[0]." Sentinel: ".$csv[1]." Clinic: ".$csv[2];
                        throw new \InvalidArgumentException($message);
                    }
                    $this->importedRecordsNumber++;
                    $this->logInfo("Import Record - ID: ".$csv[0]." Sentinel: ".$csv[1]." Clinic: ".$csv[2]);
                } else {
                    // Not error, only ignore this record.
                    $this->logWarn("Ignore Record - ID: ".$csv[0]." Sentinel: ".$csv[1]." Clinic: ".$csv[2]);
                }
            }
            
            $manager->getConnection()->commit();
            
        } catch (\Exception $e) {
            $manager->getConnection()->rollback();
            $this->errorMessage = $e->getMessage();
            return false;
        }
        return true;
    }
    
    protected function convert(Surveillance $surveillance, $csv)
    {
        // Check the record
        $bool1 = empty($csv[1]);
        $bool1 = $csv[1] === "0" ? true : false;
        $bool1 = empty($csv[2]);
        $bool1 = $csv[2] === "0" ? true : false;
        
        if ((empty($csv[1]) || $csv[1] === "0") || (empty($csv[2]) || $csv[2] === "0" ))
            return false;
        
        $weekend = new \DateTime($csv[3]);
        $weekend->setTime(0, 0, 0);
        if (2004 > CommonUtils::getEPIYear($weekend))
            return false;

        // Clinic
        $clinicId = self::$CNV_TBL_CLINIC_CODE[$csv[2]];
        $clinic = $this->clinicRepository->find($clinicId);
        $surveillance->setClinic($clinic);
        
        // Sentinel Site
        $sentinelSiteId = self::$CNV_TBL_SENTINEL_CODE[$csv[1]];
        if ($clinicId === self::$ROSSUNIVERSITY_ID)
            $sentinelSiteId = 9;
        $sentinelSite = $this->sentinelSiteRepository->find($sentinelSiteId);
        $surveillance->setSentinelSite($sentinelSite);
        
        // weekend     
        $surveillance->setWeekend($weekend);
        
        // week_of_year & year
        $surveillance->setWeekNumber($weekend);
        
        // Reported_by
        $surveillance->setReportedBy('Imported by system');
        
        // Reported_at
        if (CommonUtils::isDate($csv[4])) {
            $surveillance->setReportedAt(new \DateTime($csv[4]));
        } else {
            $surveillance->setReportedAt(clone $weekend);
        }
        
        // Received_at and Received_by
        if (CommonUtils::isDate($csv[101])) {
            $surveillance->setReceivedAt(new \DateTime($csv[101]));
            $surveillance->setReceivedBy('Epidemiologist');
        }
        
        // Serveillance Items
        $surveillanceItems = $surveillance->getSurveillanceItems();
        foreach ($surveillanceItems as $surveillanceItem) {
            $id = $surveillanceItem->getSyndrome()->getId();
            if (0 < $id && $id < 13) {
                $offset = self::$CNV_TBL_SYNDROME_OFFSET[$id];
                $surveillanceItem->setSunday(   $csv[$offset + 0]);
                $surveillanceItem->setMonday(   $csv[$offset + 1]);
                $surveillanceItem->setTuesday(  $csv[$offset + 2]);
                $surveillanceItem->setWednesday($csv[$offset + 3]);
                $surveillanceItem->setThursday( $csv[$offset + 4]);
                $surveillanceItem->setFriday(   $csv[$offset + 5]);
                $surveillanceItem->setSaturday( $csv[$offset + 6]);
                $surveillanceItem->setReferrals($csv[$offset + 7]);
            }
        }
        
        return true;
    }    
    
    protected function importFromNewSys(Document $document)
    {
        // This function is only place holder that might be used on enhancement.
        return $document;
    }
    
    protected function clear()
    {
        $this->logInfo = array();
        $this->logWarn = array();
        $this->errorMessage = '';
        $this->importedRecordsNumber = 0;
    }
    
    protected function logInfo($message)
    {
        $this->logInfo[] = $message;
    }
    
    protected function logWarn($message)
    {
        $this->logWarn[] = $message;
    }
}
