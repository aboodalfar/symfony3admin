<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Webit\ForexCoreBundle\Entity\RealProfile;

class RealStep4Registration extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                
                ->add('leverage',ChoiceType::class, array(
                    'placeholder'=>'leverage',
                    'choices'=>RealProfile::$leverage_list,
                    'constraints'=>array(new Assert\NotBlank()),
                    'required' => true,
                    //'label' =>'leverage',
                    'attr' => array('autofocus'=>'autofocus','class' =>'form-control','tabindex'=> '1')))
                
                 ->add('currency',ChoiceType::class, array(
                    'placeholder'=>'currency',
                    'choices'=>RealProfile::$currency_list, 
                    'constraints' => array(new Assert\NotBlank()),
                    'required' => true, 
                     //'label' => 'currency',
                    'attr' => array('class' => 'form-control','tabindex'=> '2')))
                
                
                ->add('accountType',ChoiceType::class, array(
                    'placeholder'=>'account_type',
                    'choices'=>array('IB'=>'IB','Indivual'=>'Indivual'), 
                    'constraints' => array(new Assert\NotBlank()),
                    'required' => true,
                    //'label' => 'account_type',
                    'attr' => array('class' => 'form-control','tabindex'=> '3')))
                
                ->add('howKnow',ChoiceType::class, array(
                    'placeholder'=>'form_how_know',
                    'choices'=>RealProfile::$how_know_list, 
                    'constraints' => array(new Assert\NotBlank()),
                    'required' => true,
                    //'label' => 'form_how_know',
                    'attr' => array('class' => 'form-control','tabindex'=> '4')))
                
             
                
            ;
    }

  
     public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\ForexCoreBundle\Entity\RealProfile::class,
        ));
    }

//
    public function getBlockPrefix()
    {
        return 'step4';
    }
}
