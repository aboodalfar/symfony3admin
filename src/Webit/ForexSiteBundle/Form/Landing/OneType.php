<?php

namespace Webit\ForexSiteBundle\Form\Landing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;

class OneType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('fname',EmailType::class,array( 'label'=>false,'attr'=>['placeholder'=>'first_name','class'=>'form-control'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())))
            ->add('lname',PasswordType::class,array( 'label'=>false,'attr'=>['placeholder'=>'last_name','class'=>'form-control'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())))
                
            ->add('email',EmailType::class,array( 'label'=>false,'attr'=>['placeholder'=>'email','class'=>'form-control'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())))
                
            ->add('phone',TextType::class,array( 'label'=>false,'attr'=>['placeholder'=>'mobile_number','class'=>'form-control'],"required" => true, 'constraints'=> array(
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
