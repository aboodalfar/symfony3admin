<?php

namespace Webit\CMSBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ContentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContentRepository extends EntityRepository {

    /**
     * getting content query by specified category
     * @param string $category_title
     * @param string $lang
     * @return \Doctrine\ORM\Query
     */
    public function getContentQueryPyCategory($category_title, $lang = 'en',$slug=null) {
        $q_builder = $this->createQueryBuilder('p')
                ->innerJoin('p.Translations', 'trans')
                ->leftJoin('p.ContentCategory', 'cat')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->orWhere('trans.lang = :defaultLang')
                ->andWhere('cat.title = :title')
                ->addOrderBy('p.createdAt', 'asc')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ->setParameter(':defaultLang', 'en')
                ->setParameter(':title', $category_title);
        
        if(!is_null($slug)){
            $q_builder->orWhere('p.slug = :slug')->setParameter(':slug',$slug);
        }
        return $q_builder->getQuery()->execute();
    }

    /**
     * getting latest items for specific category
     * @param integer $category_id
     * @param string $lang
     * @param integer $limit
     * @param boolean $is_featured
     * @return array
     */
    public function getLatestByCategoryId($category_id, $lang, $limit = 6) {
        $qb = $this->createQueryBuilder('p')
                ->select('p', 'trans', 'cat')
                ->innerJoin('p.Translations', 'trans')
                ->innerJoin('p.ContentCategory', 'cat')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->andWhere('cat.id = :cat_id')
                ->addOrderBy('p.createdAt', 'desc')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ->setParameter(':cat_id', $category_id)
                ->setMaxResults($limit);


        return $qb->getQuery()->getArrayResult();
    }

    /**
     * getting content query by specified category
     * @param string $category_title
     * @param string $lang
     * @return \Doctrine\ORM\Query
     */
    public function getContentByCategory($category_title, $lang = 'en') {
        $q_builder = $this->createQueryBuilder('p')
                ->select('p', 'trans', 'cat')
                ->innerJoin('p.Translations', 'trans')
                ->innerJoin('p.ContentCategory', 'cat')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->andWhere('cat.title = :title')
                ->addOrderBy('p.createdAt', 'desc')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ->setParameter(':title', $category_title)
        ;

        return $q_builder->getQuery();
    }

    /**
     * getting search query for the content
     * @param string $search_phrase
     * @param string $lang
     * @return \Doctrine\ORM\Query
     */
    public function getContentSearchQuery($search_phrase, $lang = 'en') {
        $q_builder = $this->createQueryBuilder('p')
                ->innerJoin('p.Translations', 'trans')
                ->where('trans.lang = :lang')
                ->setParameter('lang', $lang)
        ;

        $searches = explode(' ', $search_phrase);
        foreach ($searches as $sv) {
            $cqb[] = $q_builder->expr()->like("trans.title", "'%$sv%'");
            $cqb[] = $q_builder->expr()->like("trans.body", "'%$sv%'");
        }

        $q_builder->andWhere(call_user_func_array(array($q_builder->expr(), "orx"), $cqb));

        return $q_builder->getQuery();
    }

    public function Search($search_term, $lang = 'en') {
        $qb = $this->createQueryBuilder('p')
                ->leftJoin('p.Translations', 'pt')
                ->where('pt.lang=:lang')
                ->andWhere('p.isPublished=:active')
                ->setParameter('lang', $lang)
                ->setParameter('active', 1);
        $searches = explode(' ', $search_term);

        foreach ($searches as $sk => $sv) {
            $cqb[] = $qb->expr()->like("pt.title", "'%" . str_replace("'", "", $sv) . "%'");
            $cqb[] = $qb->expr()->like("pt.body", "'%" . str_replace("'", "", $sv) . "%'");
        }

        $qb->andWhere(call_user_func_array(array($qb->expr(), "orx"), $cqb));


        return $qb->getQuery()->getResult();
    }

    /**
     * getting content query by specified category
     * @param string $category_title
     * @param string $lang
     * @return \Doctrine\ORM\Query
     */
    public function getContentByTitle($title, $lang = 'en') {
        $q_builder = $this->createQueryBuilder('p')
                ->innerJoin('p.Translations', 'trans')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->andWhere('trans.title = :title')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ->setParameter(':title', $title)
                ->setMaxResults(1);
        ;

        return $q_builder->getQuery()->getOneOrNullResult();
    }

    /**
     * getting content query by specified id
     * @param string $category_title
     * @param string $lang
     * @return \Doctrine\ORM\Query
     */
    public function findPage($id, $lang = 'en') {
        $q_builder = $this->createQueryBuilder('p')
              //  ->innerJoin('p.Translations', 'trans')
               // ->leftJoin('p.ContentsTemplate','contTemplate')
                ->where('p.isPublished = :is_published')
                ->andWhere('p.id = :id')
               // ->andWhere('trans.lang = :lang')
               // ->andWhere('contTemplate.lang = :lang')
                ->setParameter(':is_published', true)
                //->setParameter(':lang', $lang)
                ->setParameter(':id', $id);
                //->addOrderBy('contTemplate.id', 'asc');
        return $q_builder->getQuery()->getResult();
    }

    public function findPageByCreated($lang = 'en', $created_at = '') {
        $q_builder = $this->createQueryBuilder('p')
                ->select('p', 'trans')
                ->innerJoin('p.Translations', 'trans')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ->setMaxResults(1);
        if ($created_at) {
            $q_builder->andWhere('p.createdAt like :created_at')
                    ->setParameter('created_at', '%'.$created_at.'%');
            
          }

        return $q_builder->getQuery()->getResult();
    }
    
    public function getContentBySlug($slug, $lang = 'en') {
        $q_builder = $this->createQueryBuilder('p')
                ->innerJoin('p.Translations', 'trans')
               // ->select('p.slug,p.id,trans.brief,p.sideMenuItemId,trans.title,trans.body,trans.image,trans.metaTitle,trans.metaKeywords,trans.metaDescription')
                ->where('p.isPublished = :is_published')
                ->andWhere('trans.lang = :lang')
                ->setParameter(':is_published', true)
                ->setParameter(':lang', $lang)
                ;
        if(is_array($slug)){
            $q_builder->andWhere('p.slug in (:slugs)')->setParameter(':slugs', $slug);
        }else{
            $q_builder->andWhere('p.slug = :slug')->setParameter(':slug', $slug);
             return $q_builder
                ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        }
        
        return $q_builder
                ->getQuery()->getResult();
    }

    
        public function getContentBySlug2($slug, $lang = '') {
        $q_builder = $this->createQueryBuilder('p')
                ->innerJoin('p.Translations', 'trans')
                ->where('p.isPublished = :is_published')
                ->andWhere('p.slug = :slug')
                ->setParameter(':is_published', true)
                ->setParameter(':slug', $slug);
        if(!empty($lang)){
            $q_builder->andWhere('trans.lang = :lang')->setParameter(':lang', $lang);
        }

        return $q_builder->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }
}