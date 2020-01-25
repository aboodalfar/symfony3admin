<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContentTemplateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $object = (isset($options['data'])?$options['data']:null);
        $Iconhelp = false;
        if ($object && ($webPath = $object->getIcon())) {
            $Iconhelp  =  '<a target="_blank" href="/'.$webPath.'" >view</a>';
        }
        $builder
            ->add('lang', 'choice', array('choices'=>['en'=>'en'],'label'=>'lang'))
            ->add('title',null,array('label'=>'title'))
            ->add('body',null,array('label'=>'body','required'=>false))
            ->add('link',null,array('label'=>'link','required'=>false))
            ->add('icon','file',array('sonata_help'=>$Iconhelp,'required'=>false,'data_class'=>null,'label'=>'icon'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\CMSBundle\Entity\ContentTemplate::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'ContentTemplate';
    }
}
