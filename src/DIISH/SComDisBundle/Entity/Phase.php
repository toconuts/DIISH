<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Phase.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @ORM\Table(name="phase")
 * @ORM\Entity(repositoryClass="DIISH\SComDisBundle\Entity\PhaseRepository")
 */
class Phase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;
    
    /**
     * @var float $threshold
     * 
     * @ORM\Column(name="threshold", type="float") 
     * @Assert\NotBlank
     */
    private $threshold;
    
    /**
     * @var string $color
     * 
     * @ORM\Column(name="color", type="string", length=50)
     * @Assert\Length(max = 10)
     * @Assert\NotBlank
     */
    private $color;
    
    public function __construct()
    {
        
    }

    /**
     * Set id
     * 
     * @param int $value
     */
    public function setId($value) {
        $this->id = $value;
    }
    
    /**
     * Get id
     * 
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set threshold
     * 
     * @param float $value
     */
    public function setThreshold($value) {
        $this->threshold = $value;
    }
    
    /**
     * Get threshold
     * 
     * @return float
     */
    public function getThreshold() {
        return $this->threshold;
    }
    
    /**
     * Set color
     * 
     * @param string $value
     */
    public function setColor($value) {
        $this->color = $value;
    }
    
    /**
     * Get color
     * 
     * @return string
     */
    public function getColor() {
        return $this->color;
    }
}