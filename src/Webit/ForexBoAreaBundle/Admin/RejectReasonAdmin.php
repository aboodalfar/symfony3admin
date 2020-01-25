<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\ForexBoAreaBundle\Form\BoCompliance\RejectReasonTranslationType as TranslationType;

use Sonata\AdminBundle\Form\FormMapper;

class RejectReasonAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $lang = $this->getConfigurationPool()->getContainer()->getParameter('langs');

    	$formMapper->add('isActive')
        ->end()
        ->with('Translations', array('collapsed' => false))
                ->add('translation_values', 'collection', array('type' => new TranslationType(array('lang' => $lang)),
                    'allow_add' => true,
                    'prototype' => true,
                    'by_reference' => true,
                ))
                ->end()
    	;
    }

    protected function configureListFields(ListMapper $ListMapper){
        unset($this->listModes['mosaic']);
    	$ListMapper->add('id')
                   ->add('content')
    			   ->add('isActive')
                   ->add('_action', 'actions', array(
                             'actions' => array(
                                'edit' => array(),
                                'delete' => array()                 
                             )))
    			   ;
    }

}
