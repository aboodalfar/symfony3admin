<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Webit\ForexCoreBundle\Entity\TradingAccount;
use Webit\ForexCoreBundle\Entity\RealProfile;

class TradingAccountAdmin extends Admin {
    
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper) {
        $sonata_name = $this->getConfigurationPool()->getContainer()->get('request')->get('_sonata_name');
        $is_new = (strpos($sonata_name, 'create') !== false);        
        
        $formMapper
                //->add('CodeTradingGroup')
                ->add('login', 'integer', array('disabled' => 'disabled'))
                ->add('roPassword',null, array('disabled' => 'disabled','label'=>'Read-only Password',
                    'sonata_help'=>'This reflects first password upon account creation, not necessarly current one'))
                ->add('onlinePassword',null, array('disabled' => 'disabled','label'=>'Master password',
                    'sonata_help'=>'This reflects first password upon account creation, not necessarly current one'))
                //->add('Platform', null, array('attr'=>['disabled'=>$is_new?false:'disabled']))
                /*->add('TradingCurrency', 'sonata_type_model', array(
                    'query' => $this->getConfigurationPool()->getContainer()
                                ->get('doctrine')->getRepository('\Webit\ForexCoreBundle\Entity\TradingCurrency')
                                ->getActiveCurrenciesQuery(),
                    'btn_add' => false, 
                    'btn_delete' => false,
                    'btn_list' => false
                ))*/
                ->add('leverage', 'choice', array('empty_value' => 'Choose a leverage...', 'choices' => RealProfile::$leverage_list))
                ->add('comment')
                ->add('agent_account')
        ;
        
        if($is_new){
              $formMapper->add('account_type', 'choice', array('choices' => TradingAccount::$account_type_list));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('PortalUser.first_name', null, array('label' => 'First name'))
                ->add('PortalUser.last_name', null, array('label' => 'Last Name'))
                ->add('PortalUser.username', null, array('label' => 'Username(Email)'))
                ->add('Platform')
                ->add('TradingCurrency')
                ->add('login')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        unset($this->listModes['mosaic']);
        $listMapper
                ->addIdentifier('login', null, array('route'=>array('name'=>'show') ))
                ->add('PortalUser.first_name', null, array('label' => 'First name'))
                ->add('PortalUser.last_name', null, array('label' => 'Last Name'))
                ->add('PortalUser.username', null, array('label' => 'Username(Email)'))
                ->add('account_type_label', null, array('label'=>'Account Type'))
                ->add('Platform')
                ->add('TradingCurrency.name', null, array('label'=>'Currency'))
                ->add('createdAt')
                 ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array('label'=>'View Details'),
                )
            ))
        ;
    }
    public function getExportFields() {
        return array('login', 'Full Name' => 'PortalUser.FullName','Email'=>'PortalUser.username','Account Type'=>'account_type_label','Platform','Currency'=>'TradingCurrency.name','createdAt');
    }
    
    protected function configureShowFields(ShowMapper $show){
        $show
                ->add('login')
                ->add('PortalUser.first_name')
                ->add('PortalUser.last_name')
                ->add('PortalUser.username')
                ->add('account_type_label')
                ->add('Platform.name')
                ->add('TradingCurrency.name')
                ->add('leverage')
                ->add('createdAt')
                ;
    }

    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection) {
        $collection->remove('create');
        $collection->add('refresh_account', 'refresh_account/{id}/check', array(), array('id' => '\d+'));
        $collection->add('Portalusers', '../realusers/{id}/show', array(), array('id' => '\d+'));
    }

}
