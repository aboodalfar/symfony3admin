<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;


class FaqCategoryAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'weight'
    );

   protected function configureDatagridFilters(DatagridMapper $datagridMapper)
   {
        $datagridMapper
                ->add('createdAt')
                //TODO: add title to it
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->addIdentifier('title')
                ->add('isActive')
                ->add('createdAt')
        ;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $weight_range = range(-10,10);
        $weight = array_combine($weight_range, $weight_range);
        $lang = ['en'=>'en','ar'=>'ar'];;

        $formMapper
                ->add('isActive','checkbox', array('required'=>false))
                ->add('weight', 'choice', array('choices'=>$weight, 'required'=>false))
                ->add('translation_values','collection', array(
                                'type' =>  new \Webit\CMSBundle\Form\FaqCategoryTranslationType(array('lang'=>$lang)),
                                'allow_add' => true,
                                'prototype' => true,
                                'by_reference' => true,
                ))
            ;

    }
}
