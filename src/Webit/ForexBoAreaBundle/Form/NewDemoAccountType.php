<?php

namespace Webit\ForexBoAreaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexCoreBundle\Entity\DemoProfile;

class NewDemoAccountType extends AbstractType {
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $list = DemoProfile::getTradingAccountTypeList();
        
        $builder

                 ->add('TradingCurrency',EntityType::class, array('label' => 'form-currency-lbl',
                    'class' => 'Webit\ForexCoreBundle\Entity\TradingCurrency',
                    'constraints' => array(new Assert\NotBlank()),
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $em)  {
                return $em->createQueryBuilder('t')
                        ->andWhere('t.isActive = :isActive')
                        ->setParameter(':isActive', true);
            },
                    'required' => true,
                    'empty_value' => 'form-currency-lbl',
                    'constraints' => array(new Assert\NotBlank())
                ))
                    ->add('TradingAccountType','choice',array('data'=>1,'label'=>'form-accountType-lbl','empty_value'=>'','choices'=>$list, 'required'=>true, 'constraints'=>array( new Assert\NotBlank() )))
                     ->add('leverage', 'choice', array('data'=>1,'label'=>'form-leverage-lbl','empty_value'=>'','choices'=>RealProfile::$leverage_list, 'required'=>true, 'constraints'=>array( new Assert\NotBlank() )))
                
                ->add('deposit','text', array('label'=>'form-deposit-lbl','required' => true,'constraints' => array(new Assert\NotBlank(),new Assert\Type(['type'=>'numeric']),  new Assert\Range(array('min'=>100,'max'=>10000000)))))
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'webit_tradersarea_newdemoaccount';
    }

}

