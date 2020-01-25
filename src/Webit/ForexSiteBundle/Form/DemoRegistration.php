<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Validator\Constraints as Assert;
use Webit\ForexCoreBundle\Entity\DemoProfile;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;

class DemoRegistration extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name',TextType::class, array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
            'required' => true, 'label'=>'first_name','attr' => array('class'=>'form-control')))

                ->add('last_name',TextType::class, array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
                    'required' => true, 'label'=>'last_name', 'attr' => array('class'=>'form-control')))
                
                
                ->add('username', EmailType::class, array('constraints' => array(new Assert\NotBlank()),
                    'required' => true, 'label'=>'email', 'attr' => array('class'=>'form-control')))
                
                
                ->add('country',CountryType::class, array(
                    'placeholder'=>'select_country',
                    'constraints' => array(new Assert\NotBlank()), 
                    'required' => true, 'label'=>'country','attr'
                    =>array('class'=>'form-control')))

                ->add('phone_code','hidden', array(
                       'mapped'=>false, 'attr' => array('class'=>'form-control numberBlock1')))
                
                ->add('mobile_number',TextType::class ,array('constraints' => array(
                        new Assert\Regex(array('pattern' => '/\d{5,15}/')),
                        new Assert\NotBlank()
                    ), 'label'=>'mobile_number', 'attr' => array('class'=>'form-control numberBlock2')))
                
               
           

        ;
    }

     public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => DemoProfile::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'DemoType';
    }

}
