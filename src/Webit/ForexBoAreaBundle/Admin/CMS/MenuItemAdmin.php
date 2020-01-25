<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\CMSBundle\Form\MenuItemTranslationType;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelType;


class MenuItemAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('isActive')
                ->add('menu')
                //->add('content')
                ->add('Translations.displayLabel', null, array('label'=>'Label'))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('id')
                ->addIdentifier('label')
                ->add('parentMenuItem',null,array('label'=>'Menu'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $weight_range = range(-10, 10);
        $weight = array_combine($weight_range, $weight_range);
        $langs = $this->getConfigurationPool()->getContainer()->getParameter('langs');

        $formMapper
                ->add('is_active', 'checkbox', array('required' => false))
                ->add('menu',null,array('help'=>'(Parent Level 1)','placeholder'=>'select'))
                ->add('link')
                ->add('route')
                ->add('content',ModelType::class,array('required'=>false,'placeholder'=>'select','help'=>'(Link To Page)','btn_add'=>false))
                ->add('parentMenuItem',null,array('placeholder'=>'select','help'=>'(Parent Level 2)'))
                ->add('isTargetBlank', 'checkbox', array('required' => false))
                ->add('weight', 'text', array('help'=>'used for order menu(include all level)', 'required' => false))
                ->end()
                ->with('Translations', array('collapsed' => false))
                ->add('translation_values', CollectionType::class, array(
                    'entry_type' => MenuItemTranslationType::class ,
                    'allow_add' => true,
                    'prototype' => true,
                    'by_reference' => true,
                ))
        ;
    }

    public function getExportFields() {
        return array('id','label');
    }        
}
