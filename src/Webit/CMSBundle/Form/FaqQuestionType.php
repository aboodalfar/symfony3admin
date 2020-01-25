<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqQuestionType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isActive')
            ->add('weight')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webit\CMSBundle\Entity\FaqQuestion::class
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_faqquestion';
    }
}
