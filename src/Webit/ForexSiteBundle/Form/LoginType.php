<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;

class LoginType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username',EmailType::class,array( 'label'=>false,'attr'=>['placeholder'=>'email','class'=>'form-control'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())))
            ->add('password',PasswordType::class,array( 'label'=>false,'attr'=>['placeholder'=>'password','class'=>'form-control'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'login';
    }
}
