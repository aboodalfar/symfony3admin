<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PortalUserAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('username', 'email')
                ->add('first_name')
                ->add('last_name')
                ->add('country', 'country',['label'=>'Country Of Residence'])
                ->add('mobile_number')
                ->add('communicationLanguage')
                ->add('security_question', EntityType::class, array(
                    'class' => \Webit\CMSBundle\Entity\SecurityQuestion::class,
                    'choice_label' => 'questionText',
                    'empty_data' => null,
                    'placeholder' => 'Choose a question',
                    'required' => false
                ))
                ->add('security_question_answer')
        ;
    }

}
