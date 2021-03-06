<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * OutbreakRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OutbreakRepository extends EntityRepository
{
    /**
     * Save outbreak
     * 
     * @param Outbreak $outbreak
     * @throws \InvalidArgumentException 
     */
    public function saveOutbreak(Outbreak $outbreak)
    {   
        $this->isExist_EX($outbreak);
        
        $manager = $this->getEntityManager();
        $manager->persist($outbreak);
        
        foreach ($outbreak->outbreakItems as $item ) {
            $item->setOutbreak($outbreak);
            $manager->persist($item);
        }

        $manager->flush();
    }
    
    /**
     * Check whether the outbreak already exists or not
     * 
     * @param Outbreak $outbreak
     * @return boolean 
     */
    public function isExist(Outbreak $outbreak)
    {
        $other = $this->findOneBy(array(
            'weekOfYear'    => $outbreak->getWeekOfYear(),
            'year'          => $outbreak->getYear(),
            'sentinelSite'  => $outbreak->getSentinelSite()->getId(),
            'clinic'        => $outbreak->getClinic()->getId(),
            'syndrome'      => $outbreak->getSyndrome()->getId(),
        ));
        
        if ($other) {
            if ($other->getId() === $outbreak->getId())
                return false;
            else
                return true;
        }
        
        return false;
    }
    
    /**
     * Check whether the outbreak already exists or not
     * 
     * @param Outbreak $outbreak
     * @throws \InvalidArgumentException
     */
    public function isExist_EX(Outbreak $outbreak)
    {
        if ($this->isExist($outbreak)) {
            $message = 'Error: This outbreak is already exist. '.
                            $outbreak->getUniqueTitle();
            throw new \InvalidArgumentException($message);
        }
    }
    
    /**
     * Delete outbreak
     * 
     * @param Outbreak $outbreak
     * @throws \InvalidArgumentException 
     */
    public function deleteOutbreak($id)
    {
        $outbreak = $this->find($id);
        if (!$outbreak) {
            throw new \InvalidArgumentException('Error: The outbreak is not found.');
        }
        
        $manager = $this->getEntityManager();
        
        foreach ($outbreak->outbreakItems as $item ) {
            $manager->remove($item);
        }
        
        $manager->remove($outbreak);
        $manager->flush();
    }
}