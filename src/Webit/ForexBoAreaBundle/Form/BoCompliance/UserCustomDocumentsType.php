<?php

namespace Webit\ForexBoAreaBundle\Form\BoCompliance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserCustomDocumentsType extends AbstractType
{   
    protected $customOptions;
    
    public function __construct($customOptions) {
        $this->customOptions = $customOptions;
        
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //code smell
        $docs_mime_types = array('application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword');

        $builder
                ->add('documentName','text',array('label'=>'Document Name'))
                ->add('documentPath', 'file', array('required' => false, 'data_class' => null))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webit\ForexCoreBundle\Entity\UserCustomDocuments',
        ));
    }    


    public function getName()
    {
        return 'usercustomdocumentstype';
    }

}
