<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class SliderAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'weight'
    );



    protected function configureFormFields(FormMapper $formMapper)
    {
        $weight=array();
        for($i=-10;$i<=10;$i++) {
            $weight[$i]=$i;
        }
        $lang = ['en'=>'en','ar'=>'ar'];
        $is_new = ($this->isCurrentRoute('create') ? true :false);
        $formMapper
                ->add('lang', 'choice', array('label' => 'Lang', 'choices' => $lang, 'required' => true))
                ->add('active', 'checkbox', array('required' => false))
                ->add('image', 'file', array('data_class'=>null,'required' => $is_new))
                ->add('title','textarea', array('required' => false))
                ->add('description','textarea', array('required' => false))
                ->add('description2','textarea', array('required' => false))
                ->add('weight', 'choice', array('label' => 'weight', 'choices' =>$weight, 'required' => true))
                ->add('route', 'text', array('required' => false))
                ->add('content', null, array('label' => 'content', 'required' => false))

               // ->setHelps(array('page_id' => $this->trans('slug is auto generated')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
         $lang = ['en'=>'en','ar'=>'ar'];;
        $datagridMapper
        ->add('active')
        ->add('lang', 'doctrine_orm_choice', array('label'=>'language'), 'choice' , array('choices' => $lang))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->add('imageBlock','string',
                    array('template' => 'WebitCMSBundle:SliderAdmin:image.html.twig'),
                    array('label'    => 'Thumbnail')     
                    )
                ->add('active')
                ->add('lang');
    }

    public function getExportFields() {   
        return array('id','title', 'description');
    }     
}
