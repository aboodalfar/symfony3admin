<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Webit\ForexCoreBundle\Entity\Partnership;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;


class PartnershipType extends AbstractType
{
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName',TextType::class,array( 'attr'=>['placeholder'=>'first name'],"required" => true, 'constraints'=> array(
                new Assert\NotNull(),new AcmeAssert\ContainsTextOnly)))
            ->add('lastName',TextType::class,array( 'attr'=>['placeholder'=>'last name'],"required" => true, 'constraints'=> array(
                new Assert\NotNull(),new AcmeAssert\ContainsTextOnly)))
            ->add('email',EmailType::class,array('attr'=>['placeholder'=>'email'],"required" => true, 'constraints'=> array(
                new Assert\NotNull())))
                
            ->add('phone_code','hidden', array(
                       'mapped'=>false, 'attr' => array('class'=>'form-control numberBlock1','placeholder'=>'phone_code')))
                
            ->add('phoneNumber',TextType::class, array('attr'=>['placeholder'=>'phone number','class'=>'form-control'],'constraints' => array(
                        new Assert\Regex(array('pattern' => '/\d{5,15}/')),
                        new Assert\NotBlank()
            )))
                
             ->add('company',TextType::class,array(
                 'attr'=>['placeholder'=>'company'],
                 "required" => false, 'constraints'=> array(
                new AcmeAssert\ContainsTextOnly)))
                
             ->add('numberClients',TextType::class,array(
                 'attr'=>['placeholder'=>'Expected number of clients'],
                 "required" => false, 'constraints'=> array(
                new Assert\NotNull())))  
             ->add('funds',TextType::class,array(
                 'attr'=>['placeholder'=>'Expected funds under management'],
                 "required" => false))  
                
              ->add('website',TextType::class,array(
                 'attr'=>['placeholder'=>'website'],
                 "required" => false))  
                
              ->add('skype',TextType::class,array(
                 'attr'=>['placeholder'=>'Skype contact'],
                 "required" => false))    
            
            
            ->add('type','hidden',['data'=>1])    
  

        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Partnership::class
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'partnership';
    }
}
