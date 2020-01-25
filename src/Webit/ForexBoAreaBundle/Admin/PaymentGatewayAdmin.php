<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\Validator\Constraints as Assert;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Webit\ForexCoreBundle\Entity\PaymentGateway;

/**
 * Description of BranchesAdmin
 *
 * @author amjad
 */
class PaymentGatewayAdmin extends Admin {

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper) {

        $weight = range(-10, 10);
        $weight = array_combine($weight, $weight);

        $formMapper->add('codeSymbol')
                ->add('name')
               // ->add('payment_method', 'choice', array('choices' => PaymentGateway::$payment_method_list))
                
//                ->add('weight', 'choice', array('choices' => $weight, 'required' => false))
                
                ->add('icon','file', array('required' => false, 'data_class' => null))
                ->add('isActive');
    }

    protected function configureListFields(ListMapper $listMapper) {
        unset($this->listModes['mosaic']);

        $listMapper
                ->addIdentifier('codeSymbol')
                ->add('name')
                ->add('payment_method')
                ->add('isActive')
                ->add('created_at', 'date');
    }

 

}
