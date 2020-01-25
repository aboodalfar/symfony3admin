<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Webit\ForexCoreBundle\Entity\DemoProfile;
use Webit\ForexCoreBundle\Entity\RealProfile;

class DemoAccountsAdmin extends Admin {

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('username', null, array('label' => 'Username'))
                ->add('mobile_number')
                ->add('country')
                ->add('full_name', 'doctrine_orm_callback', array(
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value || $value['value'] == '') {
                        return;
                    }
                    $queryBuilder->andWhere('CONCAT(' . $alias . '.first_name, CONCAT(\' \', ' . $alias . '.last_name)) LIKE :term')->setParameter(':term', '%' . $value['value'] . '%');
                    return true;
                },
                'field_type' => 'text'
            ))

        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        unset($this->listModes['mosaic']);
        $listMapper
                ->add('username', null, array('label' => 'Username'))
                ->add('mobile_number', null, array('label' => 'Mobile Number'))
                ->add('created_at', null, array('label' => 'Registered At'))
                ->add('active', null, array('label' => 'Active'))
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                    )
                ))
        ;
    }

    public function configureFormFields(\Sonata\AdminBundle\Form\FormMapper $form) {
        $form->add('username', null, array('label' => 'Email'))
                ->add('first_name', null, array('label' => 'First Name'))
                ->add('last_name', null, array('label' => 'last Name'))
                ->add('mobile_number', null, array('label' => 'Mobile Number'))
                ->add('country', 'country', array('label' => 'Country', 'placeholder' => 'Please select a Country..'))

        ;
    }

    protected function configureShowFields(\Sonata\AdminBundle\Show\ShowMapper $show) {
        $show
                ->with('Main Information')
                ->add('fullName', null, array('label' => 'Full Name'))
                ->add('username', null, array('label' => 'Email'))              
                ->add('mobile_number', null, array('label' => 'Mobile Number'))
                ->add('CountryLabel', null, array('label' => 'Country'))
                ->add('active', null, array('label' => 'Active'))
                ->add('created_at', null, array('label' => 'Registered At'))
                ->end();

    }

    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection) {

       // $collection->add('chartsStats', 'charts-and-stats', array());
//        $collection->add('open_demo_trading', 'open_demo_trading/{id}', array(), array('id' => '\d+'));
//        $collection->add('demo_resend_trading', 'demo_resend_trading/{id}', array(), array('id' => '\d+'));
//        $collection->add('demo_add_note', 'add_note/{id}', array(), array('id' => '\d+'));
    }
    
    public function getExportFields() {
        return array('username','fullName','mobile_number','country'=>'CountryLabel','created_at');
    }

}
