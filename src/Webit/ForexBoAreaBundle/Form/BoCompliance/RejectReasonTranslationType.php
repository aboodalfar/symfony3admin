<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RejectReasonTranslationType extends AbstractType
{

    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lang', 'choice', array('choices'=>$this->options['lang']))
            ->add('content')
          //  ->add('parentId')            
          //  ->add('RejectReason')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null// 'Webit\CMSBundle\Entity\ContentTranslation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'webit_forexboareabundle_rejectreasontranslation';
    }
}
