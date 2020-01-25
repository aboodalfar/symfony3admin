<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;


class MenuAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

   protected function configureDatagridFilters(DatagridMapper $datagridMapper)
   {
        $datagridMapper
                ->add('name')
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('id')
                ->addIdentifier('name')
        ;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('name','text',array('required' => true))
            ;
    }
}
