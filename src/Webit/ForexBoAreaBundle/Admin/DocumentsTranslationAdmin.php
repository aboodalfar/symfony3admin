<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Webit\ForexCoreBundle\Entity\DocumentsTranslation;

class DocumentsTranslationAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );
    
    protected function configureListFields(ListMapper $list) {
        $list->add('DocumentTypeLabel')
             ->add('ReferenceId')   
             ->add('FirstName')   
             ->add('LastName')   
             ->add('address')   
                ;
    }
}