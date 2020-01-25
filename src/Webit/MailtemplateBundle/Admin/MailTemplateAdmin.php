<?php

namespace Webit\MailtemplateBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
//use Webit\MailtemplateBundle\Entity\MailTemplateTranslation;
use Webit\MailtemplateBundle\Form\MailTemplateTranslationType;

class MailTemplateAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

   protected function configureDatagridFilters(DatagridMapper $datagridMapper)
   {
        $datagridMapper
                ->add('name')
                ->add('note')
                ->add('translation.subject', null, array('label' => 'subject'))
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('id')
                ->addIdentifier('name')
                ->addIdentifier('subjectTrans',null, array('label'=>'Subject'))
                ->add('note')
                //->add('created_at','date')
        ;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $lang = 'en';//$this->getConfigurationPool()->getContainer()->getParameter('lang');
        $is_new = ($this->isCurrentRoute('create') ? true :false);

        $formMapper
                ->add('name','text',array('required' => true))
                ->setHelps(array('name' => $this->trans('name is a unique identifier used in programming')))
                ->add('reply_to', 'email', array('required'=>false))
                ->add('note', 'text', array('required'=>false))
                ->add('translation_values', 'collection', array(
                    'entry_type' =>  MailTemplateTranslationType::class,
                    'allow_add' => true,
                    'prototype' => true,
                    'by_reference' => true,
                ))
            ;

    }
    
    public function getExportFields() {   
        return array('id','translation[0].subject', 'translation[0].mailBody');
    }
}
