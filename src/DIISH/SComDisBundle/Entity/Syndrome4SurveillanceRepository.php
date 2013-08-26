<?php

namespace DIISH\SComDisBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Syndrome4SurveillanceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Syndrome4SurveillanceRepository extends EntityRepository
{
    /**
     * Save syndrome$surveillance
     * 
     * @param \DIISH\SComDisBundle\Entity\Syndrome4Surveillance $syndrome
     * @param bool $update
     * @throws \InvalidArgumentException 
     */
    public function saveSyndrome(Syndrome4Surveillance $syndrome, $update = false)
    {           
        if ($this->isExist($syndrome, $update)) {
            throw new \InvalidArgumentException('Error: Duplicated Syndrome ID.');
        }
        
        $manager = $this->getEntityManager();
        $manager->persist($syndrome);
        $manager->flush();
    }

    /**
     * Update syndrome4surveillance
     * 
     * @param \DIISH\SComDisBundle\Entity\Syndrome4Surveillance $syndrome
     * @param bool $update
     */
    public function updateSyndrome(Syndrome4Surveillance $syndrome, $update = true)
    {
        $this->saveSyndrome($syndrome, $update);
    }
    
    /**
     * Check whether syndrome already exist or not
     * 
     * @param Syndrome $syndrome
     * @return boolean 
     */
    public function isExist(Syndrome4Surveillance $syndrome, $update = false)
    {
        $other = $this->findOneBy(array(
            'id'    => $syndrome->getId(),
        ));
        
        if ($other) {
            if ($other->getId() === $syndrome->getId()) {
                if ($update)
                    return false;
                else
                    return true;
            } else {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check whether display id is available or not
     * 
     * @param type $id 
     */
    public function isAvailableDispID(Syndrome4Surveillance $syndrome)
    {
        $others = $this->findAll();
        foreach ($others as $other) {
            if ($other->getId() != $syndrome->getId()) {
                if ($other->getDisplayId() == $syndrome->getDisplayId()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Delete syndrome
     * 
     * @param int $id
     * @throws \InvalidArgumentException 
     */
    public function deleteSyndrome($id)
    {
        $syndrome = $this->find($id);
        if (!$syndrome) {
            throw new \InvalidArgumentException('Error: Syndrome is not found.');
        }
        
        $manager = $this->getEntityManager();
        $manager->remove($syndrome);
        $manager->flush();
    }
}