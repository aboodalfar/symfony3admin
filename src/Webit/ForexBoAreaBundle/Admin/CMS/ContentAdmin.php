<?php

namespace Webit\ForexBoAreaBundle\Admin\CMS;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\CMSBundle\Form\ContentTemplateType;
//use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContentAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $lang = ['en'=>'en','ar'=>'ar'];
        $datagridMapper
                ->add('Translations.title', null, array('label'=>'title'))
                ->add('ContentCategory')
                ->add('createdAt','doctrine_orm_date',array('field_type'=>'sonata_type_date_picker','label' => 'Created At'))
                ->add('Translations.lang', 'doctrine_orm_choice', array('label'=>'language'), 'choice' , array('choices' => $lang))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->addIdentifier('Title')
                //->add('ContentCategory')
                ->add('createdAt')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $weight = range(-10, 10);
        $weight = array_combine($weight, $weight);
        $lang = ['en'=>'en','ar'=>'ar'];;

        $formMapper
                ->add('is_published', 'checkbox', array('required' => false))
                ->add('slug', 'text',array('attr' => array(
                          'readonly' => true,
                 )))
                ->add('ContentCategory',null)
                ->add('SideMenuItem','entity',array(
                    'help'=>'important to show sub menus in inner pages',
                    'required'=>false,
                    'placeholder'=>'select',
                    'class' => 'WebitCMSBundle:MenuItem',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em) {
                        /* @var $qb \Doctrine\DBAL\Query\QueryBuilder */
                        $qb = $em->createQueryBuilder('s')
                                ->andWhere('s.parentMenuItemId is null')                                
                                ;
                        return $qb;
                    }
                ))
                ->add('template',ChoiceType::class,array(
                    'choices'=>array(
                      'default'=>'default',
                      'box template'=>'box_template',
                      'icon template'=>'icon_template',
                      'icon template2'=>'icon_template2',
                      'partnership template' =>'partnership_template' 
                        ),'help'=>'style of page'))    
                ->end()
                ->with('Translations', array('collapsed' => false))
                ->add('translation_values','collection', 
                        array('entry_type' => \Webit\CMSBundle\Form\ContentTranslationType::class,
                    'allow_add' => true,
                    'prototype' => true,
                    'by_reference' => true,
                ))
                ->end()
               
                        
               ->with('ContentsTemplate', array(
                    'collapsed' => false, 'class' => 'col-md-12',
                    'box_class' => 'box box-solid'))
                ->add('ContentsTemplate', CollectionType::class, array(
                    'type_options'=>['delete' => true],
                    'label' => false,
                    //'type' => ContentTemplateType::class,
                    'by_reference' => false
                        ), ['edit' => 'inline',
                    'inline' => 'table'])
                ->end()         ;
    }

    public function getExportFields() {   
        return array('id','Translations[0].title', 'Translations[0].body');
    }    
    
    
}
