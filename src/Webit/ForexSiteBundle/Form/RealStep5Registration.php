<?php

namespace Webit\ForexSiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RealStep5Registration extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                
                ->add('documentId',FileType::class, array(
                    'data_class' => null,
                    'required' => false,'label' =>'passport',
                    'attr' => array('autofocus'=>'autofocus','class' =>'form-control','tabindex'=> '1')))
                
                ->add('documentId2',FileType::class, array(
                    'data_class' => null,
                    'required' => false,'label' =>'driver\'s_license',
                    'attr' => array('class' =>'form-control','tabindex'=> '2')))
                
                
                ->add('documentPor',FileType::class, array(
                    'data_class' => null,
                    'required' => false,'label' =>'national_id',
                    'attr' => array('class' =>'form-control','tabindex'=> '3')));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => \Webit\ForexCoreBundle\Entity\RealProfile::class,
            'constraints' => array(
               new Callback([
                    'callback' => [$this, 'customValidate'],
                ]),
            )
        ));
    }
    
    public function customValidate($object,ExecutionContextInterface $context) {
        $count = 0;
        if(!is_null($object->getDocumentId())){
            $count++;
        }
        if(!is_null($object->getDocumentId2())){
            $count++;
        }
        if(!is_null($object->getDocumentPor())){
            $count++;
        }
        if($count == 0){
            $context->addViolation('To get your account opened we require ONE of the following ID documents');
        }
 
    }
    
    public function getBlockPrefix()
    {
        return 'step5';
    }
}
