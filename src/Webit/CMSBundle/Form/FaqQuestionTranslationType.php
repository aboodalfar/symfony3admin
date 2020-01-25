<?php

namespace Webit\CMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class FaqQuestionTranslationType extends AbstractType
{
//    protected $options;
//
//    public function __construct($options)
//    {
//        $this->options = $options;
//    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lang', 'choice', array('choices'=>['en'=>'en']))
            ->add('questionText','text', array('required'=>true, 'constraints'=> new NotNull() ))
            ->add('answerText',CKEditorType::class, array('required'=>true, 'constraints'=> new NotNull() ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null, //'Webit\CMSBundle\Entity\FaqQuestionTranslation'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'webit_cmsbundle_faqquestiontranslation';
    }
}
