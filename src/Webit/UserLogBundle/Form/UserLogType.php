<?php

namespace Webit\UserLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('subject')
            ->add('body','textarea')
            //->add('createdAt')
            //->add('LoggedUser')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webit\UserLogBundle\Entity\UserLog'
        ));
    }

    public function getName()
    {
        return 'webit_bundle_userlogbundle_userlogtype';
    }
}
