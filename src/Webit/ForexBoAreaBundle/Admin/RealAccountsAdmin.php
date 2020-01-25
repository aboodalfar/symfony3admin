<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\RealProfile;

class RealAccountsAdmin extends Admin
{
    public $expectedVersionStamp;
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(\Sonata\AdminBundle\Form\FormMapper $formMapper)
    {
     
        $is_new = ($this->isCurrentRoute('create') ? true :false);
        $docs_mime_types = array('application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword');

        $formMapper->with('Contact Information')
                ->add('PortalUser', 'sonata_type_admin', array('label' => false), array('edit' => 'inline'))
                ->add('date_of_birth', 'date', array('years' => range(date("Y", strtotime("- 70 year")), date("Y", strtotime("+ 2 year"))), 'widget' => 'choice', 'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day'), 'format' => 'yyyy-MM-dd', "required" => true))
                
                ->add('full_address')
                ->add('city')
                ->add('zipCode')
                ->add('state')
                ->add('password')
                ->add('leverage', 'choice', array('choices'=>RealProfile::$leverage_list))
                ->add('currency', 'choice', array('choices'=>RealProfile::$currency_list))
                ->add('accountType', 'choice', array('choices'=>RealProfile::$currency_list))
                ->add('howKnow', 'choice', array('choices'=>RealProfile::$how_know_list))

                ->add('documentId', 'file', array('required' => false,'data_class' => null,'label'=>'passport' ))
                ->add('documentId2', 'file', array('required' => false, 'data_class' => null,'label'=>'driver\'s license' ))
                ->add('documentPor', 'file', array('required' => false, 'data_class' => null,'label'=>'national id' ))

                ->end()                
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('PortalUser.username', null, array('label' => 'Email'))
                ->add('PortalUser.mobile_number',null, array('label'=>'Mobile number'))
                //->add('boStatus', null, array('field_type' => 'choice'), null, array('choices' => RealProfile::$docStatus))
                ->add('PortalUser.created_at', 'doctrine_orm_date', array('field_type' => 'sonata_type_date_picker', 'label' => 'Registration Date'))
                ->add('PortalUser.active', null, array('label' => 'Active?'))
                ->add('PortalUser.TradingAccounts.login', null, array('label' => 'Account Number'))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('PortalUser.username', null, array('route' => array('name' => 'show'), 'label' => 'Email'))
                ->add('PortalUser.mobile_number', null, array('label' => 'Mobile Number'))
                //->add('boStatusLabel', null, array('label'=>'BO Status'))
                //->add('PortalUser.realTradingAccounts', null, array('label' => 'Account Number'))
                //->add('compStatusLabel',null, array('label'=>'Compliance Status'))
                ->add('PortalUser.active', null, array('label' => 'Active?'))
                ->add('PortalUser.createdAt', 'datetime', array('label' => 'Registration Date'))
                 ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    protected function configureShowFields(\Sonata\AdminBundle\Show\ShowMapper $show)
    {
        $show
                ->with('Main Information')
                ->add('PortalUser.username', null, array('label' => 'Username(Email)'))
                //->add('boStatusLabel', null, array('label' => 'Backoffice Status'))
                ->add('PortalUser.first_name', null, array('label' => 'First name'))
                ->add('PortalUser.last_name', null, array('label' => 'Last Name'))
                ->add('PortalUser.countryLabel', null, array('label' => 'Country'))
                ->add('city', null, array('label' => 'City'))
                ->add('PortalUser.mobile_number', null, array('label' => 'Mobile Number'))
                ->add('PortalUser.active', null, array('label' => 'Active?'))
                ->add('PortalUser.createdAt', 'date', array('label' => 'Registration Date'))
                ->add('PortalUser.security_question',null,array('label'=>'security question'))
                ->add('leverage')
                ->add('currency')
                ->add('accountType')
                ->add('howKnow')
                ->end()


                ->with("PDF")
                ->add('PortalUser.pdf_doc', null, array('template' => 'WebitForexBoAreaBundle:UserAdmin:pdf_doc.html.twig'))
                ->end()

                ->with('Documents')
                ->add('docs','document')
                ->end();
    }

//    public function preUpdate($RealUsers)
//    {
//        $RealUsers->setUserCustomDocuments($RealUsers->getUserCustomDocuments());
//    }

    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->add('forward_application', 'forward_real_application/{id}', array(), array('id' => '\d+'));
        $collection->add('approve_application', 'approve_application/{id}', array(), array('id' => '\d+'));
        $collection->add('reject_application', 'reject_application/{id}', array(), array('id' => '\d+'));
        $collection->add('trading_accounts', '../tradingaccount/{id}/edit', array(), array('id' => '\d+'));
        $collection->add('add_new_log', 'add_new_log/{id}', array(), array('id' => '\d+'));
        $collection->add('open_sub_account', 'open_sub_account/{id}', array(), array('id' => '\d+'));
        $collection->add('resend_info', 'resend/{id}', array(), array('id' => '\d+'));

    }

    public function getExportFields()
    {
        return array('Email'=>'PortalUser.username','Full Name'=>'PortalUser.fullName', 'Created At'=>'PortalUser.createdAt');
    }

    public function getBatchActions()
    {
        // retrieve the default batch actions (currently only delete)

        $actions = parent::getBatchActions();

        if (
                $this->hasRoute('edit') && $this->isGranted('EDIT') &&
                $this->hasRoute('delete') && $this->isGranted('DELETE')
        ) {
            $actions['SendExpirationEmail'] = array(
                'label' => $this->trans('Send expiration email', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true
            );
            $actions['SendNotificationEmail'] = array(
                'label' => $this->trans('Send notification email', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true
            );
        }

        return $actions;
    }    
    
 

    /**
     * {@inheritdoc}
     */      
//    public function update($object) {
//        try{
//            parent::update($object);
//        }catch(\Doctrine\ORM\OptimisticLockException $e) {
//            $this->getConfigurationPool()->getContainer()->get('session')
//                    ->getFlashBag()->add('sonata_flash_error', 'someone modified the object in between');
//        }
//    }
}
