<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;


class CategoryAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

   protected function configureDatagridFilters(DatagridMapper $datagridMapper)
   {
        $datagridMapper
                ->add('title')
        ;
    }
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('id')
                ->addIdentifier('title')
                ->add('slug')
        ;

    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('title','text',array('required' => true))
                ->add('slug', 'text', array('required'=>false))
            ;
    }
}
