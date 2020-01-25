<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RealApplicationTranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('secondName')
            ->add('thirdName')
            ->add('corporateName')
            ->add('businessSector')
            ->add('personToContact')
            ->add('city')
            ->add('streetName')
            ->add('address')
            ->add('buildingNameNumber')
            ->add('stateProvince')
            ->add('cityTown')
            ->add('occupation')
            ->add('nameOfAttorney')
            ->add('attorneyLocation')
            //->add('createdAt')
            //->add('updatedAt')
            //->add('userId')
            //->add('PortalUser')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webit\ForexCoreBundle\Entity\RealApplicationTranslation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'webit_forexboareabundle_realapplicationtranslation';
    }
}
