<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContentTranslationType extends AbstractType
{
//    protected $options;
//
//    public function __construct($options)
//    {
//        $this->options = $options;
//    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
             ->add('body',CKEditorType::class,array('config'=>array('filebrowserImageBrowseUrl'=>'/bundles/webitforexsite/js/ckfinder/ckfinder.html',)))   
            ->add('brief','textarea',array('required'=>false,'label'=>'Brief ( show description in homepage)'))
            ->add('upperContent','textarea',array('required'=>false,
                'label'=>'Upper Content ( used for partnership template)'))    
            ->add('lang', 'choice', array('choices'=>array('en'=>'en')))
            ->add('image','file',array('required'=>false,'data_class'=>null))
            ->add('metaTitle', 'text', array('required'=>false))
            ->add('metaKeywords', 'textarea', array('required'=>false))
            ->add('metaDescription', 'textarea', array('required'=>false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null// 'Webit\CMSBundle\Entity\ContentTranslation'
        ));
    }

    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_contenttranslationtype';
    }
}
