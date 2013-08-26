<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Clinic.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @ORM\Table(name="clinic")
 * @ORM\Entity(repositoryClass="DIISH\SComDisBundle\Entity\ClinicRepository")
 */
class Clinic
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string $name
     * 
     * @ORM\Column(name="name", type="string", length=100, unique=true)
     * @Assert\Length(max = 100)
     */
    private $name;
    
    /**
     * @var type
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     * @Assert\Length(max = 10)
     */
    private $code;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clinics = new ArrayCollection();
    }
    
    /**
     * @var SentilenSite $sentinelSite
     * 
     * @ORM\ManyToOne(targetEntity="SentinelSite", inversedBy="clinics")
     * @ORM\JoinColumn(name="sintinel_site_id", referencedColumnName="id", nullable=false)
     */
    private $sentinelSite;
    
    /**
     * @var SentilenSite $district
     * 
     * @ORM\ManyToOne(targetEntity="District", inversedBy="clinics")
     * @ORM\JoinColumn(name="destrict_id", referencedColumnName="id", nullable=false)
     */
    private $district;

    /**
     * __toString()
     * 
     * @return string 
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set sentinelSite
     *
     * @param SentinelSite $sentinelSite
     */
    public function setSentinelSite(SentinelSite $sentinelSite)
    {
        $this->sentinelSite = $sentinelSite;
    }

    /**
     * Get sentinelSite
     *
     * @return SentinelSite 
     */
    public function getSentinelSite()
    {
        return $this->sentinelSite;
    }
    
    /**
     * Set district
     *
     * @param District $district
     */
    public function setDistrict(District $district)
    {
        $this->district = $district;
    }

    /**
     * Get district
     *
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }
}