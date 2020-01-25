<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPublished')
            ->add('slug')
            ->add('categoryId')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\CMSBundle\Entity\Content::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_contenttype';
    }
}
