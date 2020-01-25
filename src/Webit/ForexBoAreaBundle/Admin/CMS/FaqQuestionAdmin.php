<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Webit\CMSBundle\Form\FaqQuestionTranslationType;


class FaqQuestionAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'weight'
    );

   protected function configureDatagridFilters(DatagridMapper $datagridMapper)
   {
        $datagridMapper
                ->add('createdAt');
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->addIdentifier('questionText')
                ->add('Category')
                ->add('isActive')
                ->add('weight')
                ->add('createdAt')
                ->add('updatedAt')
        ;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $weight_range = range(-10,10);
        $weight = array_combine($weight_range, $weight_range);
        $formMapper
                ->add('Category', null, array('required'=>true))
                ->add('isActive', 'checkbox', array('required'=>false))
                ->add('weight', 'choice', array('choices'=>$weight, 'required'=>false))
                ->add('translation_values',CollectionType::class, array(
                                'entry_type' =>  FaqQuestionTranslationType::class,
                                'allow_add' => true,
                                'prototype' => true,
                                'by_reference' => true,
                ))
            ;

    }
    
    public function getExportFields() {   
        return array('id','Translations[0].questionText', 'Translations[0].answerText');
    }   
}
