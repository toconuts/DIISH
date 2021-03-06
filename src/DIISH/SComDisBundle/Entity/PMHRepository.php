<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PMHRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PMHRepository extends EntityRepository
{
    /**
     * Save pmh
     * 
     * @param \DIISH\SComDisBundle\Entity\PMH $mh
     * @throws \InvalidArgumentException 
     */
    public function savePMH(PMH $pmh, $update = false)
    {           
        if ($this->isExist($pmh, $update)) {
            throw new \InvalidArgumentException('Error: Duplicated PMH ID.');
        }
        
        
        
        $manager = $this->getEntityManager();
        $manager->persist($pmh);
        $manager->flush();
    }
    
    /**
     * Update pmh
     * @param \DIISH\SComDisBundle\Entity\PMH $pmh
     * @param type $update
     */
    public function updatePMH(PMH $pmh, $update = true)
    {
        $this->savePMH($pmh, $update);
    }
    
    /**
     * Check whether pmh already exist or not.
     * 
     * @param \DIISH\SComDisBundle\Entity\PMH $pmh
     * @return boolean 
     */
    public function isExist(PMH $pmh, $update = false)
    {
        $others = $this->findAll();
        foreach ($others as $other) {
            // check ID
            if ($other->getId() == $pmh->getId()) {
                if (!$update)
                    return true;
            }
            
            // check Client ID
            if ($other->getClinic()->getId() == $pmh->getClinic()->getId()) {
                if ($other->getId() != $pmh->getId())
                    return true;
            }            
        }
        return false;
    }

    /**
     * Delete pmh.
     * 
     * @param int $id
     * @throws \InvalidArgumentException 
     */
    public function deletePMH($id)
    {
        $pmh = $this->find($id);
        if (!$pmh) {
            throw new \InvalidArgumentException('Error: PMH is not found.');
        }
        
        $manager = $this->getEntityManager();
        $manager->remove($pmh);
        $manager->flush();
    }
}