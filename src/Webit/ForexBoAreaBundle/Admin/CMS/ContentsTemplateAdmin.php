<?php namespace Webit\ForexBoAreaBundle\Admin\CMS;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
class ContentsTemplateAdmin extends Admin{
    
    
    protected function configureFormFields(FormMapper $formMapper) {
        $object = $this->getSubject();
        $Iconhelp = false;
        if ($object && ($webPath = $object->getIcon())) {
            $Iconhelp  =  '<a target="_blank" href="/'.$webPath.'" >view</a>';
        }
        $formMapper
                ->add('lang', 'choice', array('choices'=>['en'=>'en'],'label'=>'lang'))
                ->add('title')
                ->add('body')
                ->add('link')
                ->add('icon',FileType::class, array(
                    'sonata_help'=>$Iconhelp,
                    'required' =>false,
                    'data_class' => null));
    }

    
    }
