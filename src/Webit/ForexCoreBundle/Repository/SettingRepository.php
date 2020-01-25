<?php

namespace Webit\ForexCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Webit\ForexCoreBundle\Entity\Currency;

/**
 * CurrencyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SettingRepository extends EntityRepository
{
    
    public function getByKeys($keys,$output='object') {
        $qb = $this->createQueryBuilder('p','p.key');
        if($output == 'array'){
            $qb->select('p.value,p.key');
        }
        return $qb->add('where',$qb->expr()->in('p.key', ':keys'))
                ->setParameter(':keys', array_values($keys))
                ->getQuery()->execute();
    }
    
}