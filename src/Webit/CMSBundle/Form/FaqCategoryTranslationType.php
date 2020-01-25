<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqCategoryTranslationType extends AbstractType
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
            ->add('title')
            ->add('lang', 'choice', array('choices'=>$this->options['lang']))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null //'Webit\CMSBundle\Entity\FaqCategoryTranslation'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_faqcategorytranslation';
    }
}
