<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webit\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DownloadRepository extends EntityRepository
{
    
    /**
     * 
     * @param string $lang
     * @param integer $cat_id
     * @return type
     */
    
    public function getAllDownload($lang, $cat_id)
    {
                $qb = $this->createQueryBuilder('p')
                //->select('DISTINCT u.title,p.filePath,t.title as cat_title')        
                ->innerJoin('p.Translations', 'u')
                ->innerJoin('p.Category', 'c') 
                ->innerJoin('c.Translations', 't') 
                ->where('u.lang = :lang')
                ->andWhere('c.id = :cat_id')
                ->andWhere('t.lang = :lang')        
                ->setParameter(':lang', $lang)
                ->setParameter(':cat_id', $cat_id)
                ->groupBy('p.id')        
                ->getQuery();
        return $qb->getResult();
        
    }
    
}