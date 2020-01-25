<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class RejectApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('predefined_reasons', 'entity', array(
                        'class' => 'WebitForexCoreBundle:RejectReason',
                        'query_builder' => function (EntityRepository $er) {
                              return $er->createQueryBuilder('r')
                                    ->andWhere('r.isActive = :active')
                                    ->setParameter(':active', true);
                                },
                         'multiple' => true,
                         'expanded' => true,
                         'label'    => 'Select a Reason'
                        ))
                ->add('reason', 'textarea', array(
                            "attr"      => array("class" => "sonata-medium"), 
                            "required"  => false,
                            "label" => "other reason (optional)"
                            ))
                ->add('notify_client', 'checkbox', array('required'=>false))
        ;
    }

    public function getName()
    {
        return 'RejectApplication';
    }

}
