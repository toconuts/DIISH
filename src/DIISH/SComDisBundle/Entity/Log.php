<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Log.
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 * 
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="DIISH\SComDisBundle\Entity\LogRepository")
 */
class Log
{
    const LOG_LEVEL_INFO = 1;
    const LOG_LEVEL_WARN = 2;
    const LOG_LEVEL_ERROR = 3;
    
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
     * @ORM\Column(name="message", type="string", length=256, nullable=false)
     * @Assert\Length(max = 256)
     */
    private $message;
    
    /**
     * @var string $userName
     * 
     * @ORM\Column(name="username", type="string", length=100, nullable=false)
     * @Assert\Length(max = 100)
     */
    private $username;
    
    /**
     * @ORM\Column(name="level", type="integer", nullable=false)
     * 
     * @var type 
     */
    private $level;
    
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
     * 
     * @param string $message
     * @param string $username
     * @param int $level
     */
    public function __construct($message, $username, $level)
    {
        $this->message = $message;
        $this->username = $username;
        $this->level = $level;
    }

    /**
     * Set id
     * 
     * @param int $value
     */
    public function setId($value)
    {
        $this->id = $value;
    }
    
    /**
     * Get id
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
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * set log level
     * @param int $value
     */
    public function setLevel($value)
    {
        $this->level = $value;
    }
    
    /**
     * Get log level
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
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