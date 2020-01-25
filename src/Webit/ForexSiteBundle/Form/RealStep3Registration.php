<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Webit\ForexSiteBundle\Validator\Constraints as AcmeAssert;

class RealStep3Registration extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $userStep = $options['userStep'];
        
        $builder
            ->add('security_question',EntityType::class, array(
               // 'label'=>'secret_question',
                'required' => true,
                'class' => 'WebitCMSBundle:SecurityQuestion',
                'constraints'=> array(
                    new Assert\NotNull()),
                'attr'=>array('autofocus'=>'autofocus','tabindex'=> '1')
            ))
            ->add('security_question_answer',TextType::class, array(
                'data'=>$options['answer_q'],
               // 'label' => 'answer',
                'required' => true,
                'constraints' => array(new Assert\NotNull(),new AcmeAssert\ContainsTextOnly),
                'attr'=>array('tabindex'=> '2','placeholder'=>'answer','class' => 'form-control')
                )
            )
              ->add('terms1', CheckboxType::class, [
                  'label'=>'I have read and understood the Financial Services Guide.',
                    'data'=>($userStep >= 3 ? true :false),
                    'attr'=>array('tabindex'=> '3'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => new Assert\IsTrue(),
                ])
                  ->add('terms2', CheckboxType::class, [
                      'label'=>'I have read and understood the Product Disclosure Statement.',
                    'data'=>($userStep >= 3 ? true :false),
                    'attr'=>array('tabindex'=> '4'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => new Assert\IsTrue(),
                ])
                 ->add('terms3', CheckboxType::class, [
                     'label'=>'I understand and agree to the Baxia FX Terms and Conditions.',
                    'data'=>($userStep >= 3 ? true :false),
                    'attr'=>array('tabindex'=> '5'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => new Assert\IsTrue(),
                ])
                 ->add('terms4', CheckboxType::class, [
                     'label'=>'I understand the nature and risks of margined products.',
                    'data'=>($userStep >= 3 ? true :false),
                    'attr'=>array('tabindex'=> '6'),
                    'required' => true,
                    'mapped' => false,
                    'constraints' => new Assert\IsTrue(),
                ])
            ;
    }

     public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\ForexCoreBundle\Entity\PortalUser::class,
        ))
        ->setRequired(['userStep','answer_q']);
    }

//
    public function getBlockPrefix()
    {
        return 'step3';
    }
}
