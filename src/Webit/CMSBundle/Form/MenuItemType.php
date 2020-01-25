<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('menuId')
            ->add('parentMenuItemId')
            ->add('route')
            ->add('contentId')
            ->add('isActive')
            ->add('isTargetBlank')
            ->add('weight')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\CMSBundle\Entity\MenuItem::class
        ));
    }

    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_menuitemtype';
    }
}
