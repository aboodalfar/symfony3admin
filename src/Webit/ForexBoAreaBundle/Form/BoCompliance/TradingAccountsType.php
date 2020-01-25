<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Webit\ForexCoreBundle\Entity\TradingAccount;
use Doctrine\ORM\EntityRepository;
use Webit\ForexCoreBundle\Entity\RealProfile;

use \Symfony\Component\Validator\Constraints as Assert;

class TradingAccountsType extends AbstractType
{
    protected $other_options;
    
    public function __construct($other_options = array()) {
        $this->other_options = $other_options;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trading_type_list = RealProfile::$trading_type_list;
        $builder
//                ->add('CodeTradingGroup', null, array('required'=>false,'label' => 'Trading Group', 'required' => true, 'empty_data' => 'Please select a group..'))
                ->add('manual_create', 'checkbox', array('mapped' => false, 'required' => false))
                ->add('Platform', 'entity', 
                        array('class' => 'WebitForexCoreBundle:Platform', 
                              'empty_value'=>'Select', 
                              'constraints'=>new Assert\NotBlank(),
                              'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('tc')
                                        ->andWhere('tc.isActive = true');
                               },                            
                              'data' => isset($this->other_options['subacc_request'])?$this->other_options['subacc_request']->getPlatform():null,
                              'label' => 'Platform/Account Type',         
                            ))
                ->add('TradingCurrency', 'entity', 
                        array('required'=>false,'class' => 'WebitForexCoreBundle:TradingCurrency', 
                            'empty_value'=>'Select', 
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('tc')
                                        ->andWhere('tc.isActive = true');
                            },
                            'constraints'=>new Assert\NotBlank(),
                              'data' => isset($this->other_options['subacc_request'])?$this->other_options['subacc_request']->getTradingCurrency():null
                            ))
                  ->add('trading_account_type','choice',array('data' => isset($this->other_options['subacc_request'])?$this->other_options['subacc_request']->getTradingAccountType():null,'empty_value'=>'',"mapped" => false,'choices'=>$trading_type_list, 'required'=>true, 'constraints'=>array( new Assert\NotBlank() )))                      
                ->add('login')
                ->add('ro_password', 'text', array('required' => false))
                ->add('online_password', 'text', array('required' => false))
                ->add('leverage', 'choice', array('data' => isset($this->other_options['subacc_request'])?$this->other_options['subacc_request']->getLeverage():null,'empty_data' => 'Choose a leverage...', 'choices' => RealProfile::$leverage_list))                
                ->add('comment')
                ->add('agent_account')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webit\ForexCoreBundle\Entity\TradingAccount'
        ));
    }

    public function getName()
    {
        return 'tradingaccountstype';
    }

}
