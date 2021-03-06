<?php

namespace DIISH\SComDisBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use DIISH\SComDisBundle\Entity\SurveillanceRepository;
use DIISH\SComDisBundle\Entity\Surveillance;

/**
 * Weekly Report Service for Syndromic Surveillance
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class DailyTallyReportService
{
    /**
     * @var RegistryInterface $managerRegistry
     */
    private $managerRegistry;
    
    /**
     * @var DIISH\Bundle\SComDisBundle\Entity\Surveillance 
     */
    private $surveillance;

    /**
     * Constructor.
     * 
     * @param RegistryInterface $managerRegistry 
     */
    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    /**
     * Output Weekly Report.
     * 
     * @param string $filename 
     */
    public function outReport($filename, $surveillance)
    {
        $reader = \PHPExcel_IOFactory::createReader("Excel5");
        $book = $reader->load($filename);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $book->setActiveSheetIndex(0);
        $sheet = $book->getActiveSheet();

        $this->setCells($sheet, $surveillance);
          
        $writer = \PHPExcel_IOFactory::createWriter($book, "Excel5");
        $writer->save('php://output');
    }
    
    /**
     * Set cells on the excel sheet.
     * 
     * @param PHPExcel_Worksheet $sheet
     * @param \DIISH\SComDisBundle\Entity\Surveillance $surveillance
     */
    protected function setCells($sheet, $surveillance) {
        
        // Clinic
        $sheet->setCellValue('D11', $surveillance->getClinic()->getName());
        
        // Sentinel Site
        $sheet->setCellValue('K11', $surveillance->getSentinelSite()->getName());
        
        // Week of year
        $sheet->setCellValue('C12', $surveillance->getWeekOfYear());
        
        // Weekend
        $sheet->setCellValue('H12', $surveillance->getWeekend()->format('d/m/y'));
        
        // Reported At
        $sheet->setCellValue('L12', $surveillance->getReportedAt()->format('d/m/y'));
        
        $row = 15;
        foreach ($surveillance->getSurveillanceItems() as $item ) {
            
            // Syndrome
            $sheet->setCellValue('B'.$row, $item->getSyndrome()->getName());
            
            // Sun.
            $sheet->setCellValue('G'.$row, (string)$item->getSunday());
            
            // Mon.
            $sheet->setCellValue('H'.$row, (string)$item->getMonday());
            
            // Tue.
            $sheet->setCellValue('I'.$row, (string)$item->getTuesday());
            
            // Wed.
            $sheet->setCellValue('J'.$row, (string)$item->getWednesday());
            
            // Thu.
            $sheet->setCellValue('K'.$row, (string)$item->getThursday());
            
            // Fri.
            $sheet->setCellValue('L'.$row, (string)$item->getFriday());
            
            // Sat.
            $sheet->setCellValue('M'.$row, (string)$item->getSaturday());
            
            // Total
            $sheet->setCellValue('N'.$row, (string)$item->getTotal());
            
            $row++;
        }
        
        // Received At and By
        $receivedAt = $surveillance->getReceivedAt();
        if ($receivedAt) {
            $sheet->setCellValue('K33', $receivedAt->format('d/m/y'));
            $sheet->setCellValue('K34', $surveillance->getReceivedBy());
        } else {
            $sheet->setCellValue('K33', "Unreceived");
        }        
    }
}
