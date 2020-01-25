<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
class RealStep2Registration extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $userStep = $options['userStep'];
        $builder->add('date_of_birth',DateType::class, array(
                    'html5'=>false,'widget' => 'single_text',
                    'constraints' => array(new Assert\NotBlank()),
                    'required' => true, 
                    //'label' => 'date_of_birth',
                    'attr' => array('autocomplete'=>'off','tabindex'=> '1','class' => 'form-control', 'placeholder' => 'date_of_birth')))
                
                ->add('full_address',TextType::class, array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
                    'required' => true, 
                    //'label' => 'address', 
                    'attr'
                    => array('tabindex'=> '2','class' => 'form-control', 'placeholder' => 'address')))
                ->add('city', EmailType::class, array('constraints' => array(new Assert\NotBlank(),new AcmeAssert\ContainsTextOnly),
                    'required' => true, 
                    //'label' => 'city',
                    'attr' => array('class' => 'form-control',
                        'placeholder' => 'city','tabindex'=> '3')))
                
                ->add('zipCode',TextType::class, array('constraints' => array(new Assert\NotBlank()), 
                    'required' => true, 
                    //'label'=>'zip_code',
                    'attr'
                    =>array('class'=>'form-control','placeholder'=>'zip_code','tabindex'=> '4')))
                
                ->add('state',TextType::class, array('constraints' => array(
                        new Assert\NotBlank()
                    ), 'required' => true,
                    //'label' => 'state',
                    'attr' => array('class' => 'form-control', 'placeholder' => 'state','tabindex'=> '5')))
                
                ->add('password',PasswordType::class, array(
                    'constraints' => array(
                        $userStep >= 2 ? new Assert\Optional() :new Assert\NotBlank()
                        
                    ), 
                    'required' => $userStep >= 2 ? false :true,
                    //'label' => 'password', 
                    'attr' => array('class' => 'form-control', 'placeholder' => 'password','tabindex'=> '6')))
                
            ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\ForexCoreBundle\Entity\RealProfile::class,
             'constraints' => array(
               new Callback([
                    'callback' => [$this, 'customValidate'],
                ]),
            )
        ))
        ->setRequired('userStep');
    }

//
    public function getBlockPrefix()
    {
        return 'step2';
    }
     public function customValidate( $object, ExecutionContextInterface $context) {
        $today = date("Y-m-d");
        if (!empty($object->getDateOfBirth()) || !is_null($object->getDateOfBirth())) {
            $dateOfBirth = $object->getDateOfBirth()->format('Y-m-d');
            $today = date("Y-m-d");
            $diff = date_diff(date_create($dateOfBirth), date_create($today));
            $age = $diff->format('%y');
            if($age < 18){
               $context->buildViolation('Age does not accept less than 18')
            ->atPath('date_of_birth')
            ->addViolation();
            }
        }

    }
}
