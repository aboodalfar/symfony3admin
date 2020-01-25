<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class MenuItemTranslationType extends AbstractType
{
//    private $options;
//    public function __construct(array $options)
//    {
//        $this->options = $options;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        


        $builder

            ->add('lang','choice',array('choices' =>array('en'=>'en'), "required" => true)) //TODO: dynamic langs
            ->add('display_label', 'text', array('required' => true))
            ->add('description','textarea',array('required'=>false,'sonata_help'=>'dsadsa',
                'label'=>'description (description menu)'))   

        ;
    }

    public function getBlockPrefix()
    {
        return 'webit_cms_menu_item';
    }
}
