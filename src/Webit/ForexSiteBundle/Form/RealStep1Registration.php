<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;

class RealStep1Registration extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $userStep = $options['userStep'];
        $builder->add('first_name',TextType::class ,array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
                    'required' => true, 
                    //'label' => 'first_name',
                    'attr' => array('autofocus'=>'autofocus','tabindex'=> '1','class' => 'form-control', 'placeholder' => 'first_name')))
                
                ->add('last_name', TextType::class, array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
                    'required' => true, 
                    //'label' => 'last_name', 
                    'attr'
                    => array('tabindex'=> '2','class' => 'form-control', 'placeholder' => 'last_name')))
                ->add('username', EmailType::class, array('constraints' => array(new Assert\NotBlank(),new Assert\Email()),
                    'required' => true, 
                   // 'label' => 'email',
                    'attr' => array('class' => 'form-control',
                        'placeholder' => 'email','tabindex'=> '3')))
                
                ->add('country',CountryType::class, array('constraints' => array(new Assert\NotBlank()), 
                    'required' => true, 
                    //'label'=>'country',
                    'placeholder' => 'select_country',
                    'attr'
                    =>array('class'=>'form-control','tabindex'=> '4')))
               
                
                ->add('mobile_number', TextType::class, array('constraints' => array(
  
                        new Assert\Regex(array('pattern' => '/\d{5,15}/')),
                        new Assert\NotBlank()
                    ), 'required' => true,
                    //'label' => 'mobile_number',
                    'attr' => array('class' => 'form-control numberBlock2', 'placeholder' => 'mobile_number','tabindex'=> '5')))
		->add('phone_code','hidden', array(
                       'mapped'=>false, 'attr' => array('class'=>'form-control numberBlock1','placeholder'=>'phone_code')))
                ->add('terms', CheckboxType::class, [
                    'data'=>($userStep >= 1 ? true :false),
                    'attr'=>array('tabindex'=> '6'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => new Assert\IsTrue(),
                ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PortalUser::class,
        ))
        ->setRequired('userStep');
    }

//
    public function getBlockPrefix()
    {
        return 'step1';
    }
}
