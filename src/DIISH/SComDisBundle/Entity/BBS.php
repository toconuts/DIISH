<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BBS.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @ORM\Table(name="bbs")
 * @ORM\Entity(repositoryClass="DIISH\SComDisBundle\Entity\BBSRepository")
 */
class BBS
{    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $message
     * 
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    private $message;
    
    /**
     * @var string $userName
     * 
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     * @Assert\Length(max = 100)
     */
    private $username;
    
    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;
    
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Set Id
     * 
     * @param int $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }
    
    /**
     * Get Id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set message
     * 
     * @param string $value
     */
    public function setMessage($value)
    {
        $this->message = $value;
    }
    
    /**
     * Get message
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set username
     * 
     * @param string $value
     */
    public function setUsername($value)
    {
        $this->username = $value;
    }
    
    /**
     * Get username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

}