<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PMH.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @ORM\Table(name="pmh")
 * @ORM\Entity(repositoryClass="DIISH\SComDisBundle\Entity\PMHRepository")
 */
class PMH
{
    const PMHID = 8000;
    const NAME = "PMH";
    const CODE = "0";
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var Clinic $clinic
     * 
     * @ORM\ManyToOne(targetEntity="Clinic")
     * @ORM\JoinColumn(name="clinic_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank
     */
    private $clinic;
    
    public function __construct()
    {
        
    }

    /**
     * Set id
     * @param int $value
     */
    public function setId($value) {
        $this->id = $value;
    }
    
    /**
     * Get id
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set clinic
     * @param Clinic $value
     */
    public function setClinic($value) {
        $this->clinic = $value;
    }
    
    /**
     * Get clinic
     * @return Clinic
     */
    public function getClinic() {
        return $this->clinic;
    }
}
