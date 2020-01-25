<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Webit\ForexCoreBundle\Entity\DocumentsTranslation;
use Symfony\Component\Validator\Constraints as Assert;

class DocumentsTranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('documentType', 'choice', 
                    array('choices'=>DocumentsTranslation::$doc_type_arr,
                          'label' => 'Documents Type',
                          'empty_value' => 'Select...',
                          'constraints' => array(new Assert\NotBlank())
                        )
                    )            
            ->add('country', 'text', array('constraints'=> new Assert\NotBlank()))
            ->add('city')
            ->add('referenceId')
            ->add('firstName', 'text', array('constraints'=> new Assert\NotBlank()))
            ->add('lastName', 'text', array('constraints'=> new Assert\NotBlank()))
            ->add('nationality')
            ->add('address','textarea')
            ->add('dateOfIssue','sonata_type_date_picker', array(
                'constraints' => array(new Assert\Date(), new Assert\NotBlank()),
                'dp_pick_time' => false,  
                'format'=> 'y-M-d',
            ))
            ->add('dateOfExpiry','sonata_type_date_picker', array(
                'constraints' => array(new Assert\Date(), new Assert\NotBlank()),
                'dp_pick_time' => false,                
                'format'=> 'y-M-d',
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webit\ForexCoreBundle\Entity\DocumentsTranslation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'doc_trans';
    }
}
