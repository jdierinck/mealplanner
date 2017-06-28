<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * AfdelingOrderedRepository
 */
class AfdelingOrderedRepository extends EntityRepository
{
    public function findIngredientenByAfdeling($boodschappenlijst, $user)
    {       
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ao','a','ibl')
            ->from('AppBundle:AfdelingOrdered','ao')           
            ->where('ao.user = :user')
            ->orderBy('ao.positie')             
            ->setParameter('user', $user)
            ->join('ao.afdeling', 'a')
            ->join('a.ingrbl', 'ibl')
            ->where('ibl.boodschappenlijst = :boodschappenlijst')
            ->setParameter('boodschappenlijst', $boodschappenlijst)
            ->getQuery()
            ->getResult();
    }

}
