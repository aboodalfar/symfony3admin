<?php

namespace Webit\MailtemplateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class MailTemplateTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('lang', 'choice', array('choices' => array('en' => 'English', 'ar' => 'Arabic'), "required" => true))
                ->add('subject')
                ->add('mail_body',CKEditorType::class, array("required" => FALSE))
        ;
    }

//    public function __construct(array $options)
//    {
//        $this->options = $options;
//    }

    /*  public function setDefaultOptions(OptionsResolverInterface $resolver)
      {
      $resolver->setDefaults(array(
      'data_class' => NULL//'Acme\GcmBundle\Entity\MailTemplateTranslation'
      ));
      } */

    public function getName()
    {
        return 'acme_gcmbundle_mailtemplatetranslationtype';
    }

}
