<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\ForexCoreBundle\Entity\RealProfileEdit;

class RealProfileEditAdmin extends Admin {

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('postal_code', 'text', array('constraints' => array(new NotBlank())))
                ->add('personal_id', 'text', array('constraints' => array(new NotBlank())))
                ->add('mobile_number', 'text', array('constraints' => array(
                        new Regex(array('pattern' => '/\d{5,15}/')),
                        new NotBlank()
            )))
                ->add('city', 'text', array('constraints' => array(new NotBlank())))
                ->add('country', 'country', array('constraints' => array(new NotBlank())))
        ;
    }

    public function createQuery($context = 'list') {
        
        /* @var $query \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery */
        $query = parent::createQuery($context);

        $query->innerJoin($query->getRootAlias() . '.PortalUser', 'pu');
        $query->where('pu.active = :status');        
        $query->setParameter(':status', \Webit\ForexCoreBundle\Entity\PortalUser::STATUS_ACTIVE);
        

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper) {
        unset($this->listModes['mosaic']);
        $listMapper
                ->addIdentifier('PortalUser.username', null, array('route' => array('name' => 'show'), 'label' => 'Email'))
                ->add('StatusLabel', null, array('label' => 'status'))
                ->add('created_at', 'date')
                 ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array('label'=>'View Details'),
                )
            ))
        ;
    }

    protected function configureShowFields(\Sonata\AdminBundle\Show\ShowMapper $show) {
        //dump($this->getSubject()); die();
        /*if ($this->getSubject()->getPortalUser()->getRealProfile()->getRealType() == \Webit\ForexCoreBundle\Entity\RealProfile::TypeCorporate) {
            $show->add('first_name', null, array('label' => 'Representative Forename(s)'));
            $show->add('last_name', null, array('label' => 'Representative Last Name'));
            $show->add('mobile_number');
            $show->add('corporate_name');
            $show->add('corporate_id');
            $show->add('company_address');
            $show->add('companyPostalZip');
            $show->add('companyPin');
            $show->add('companyCity');
            $show->add('companyResidenceCountryLabel', null, array('label' => 'Company Residence Country'));
        } else {*/
        
        /* @var $object RealProfileEdit */
        $object = $this->getSubject();
        $show->add('header', null, array('label'=>' ', 'mapped'=>false));
        if(!is_null($object->getPersonalId())){
            $show->add('personal_id');
        }
        if(!is_null($object->getPostalCode())){
            $show->add('postal_code');
        }
        if(!is_null($object->getCountry())){
            $show->add('countryLabel');
        }
        if(!is_null($object->getCity())){
            $show->add('city');
        }
        if(!is_null($object->getMobileNumber())){
            $show->add('mobile_number');
        }
        if(!is_null($object->getUsername())){
            $show->add('username');
        }
        if(!is_null($object->getAlternativeEmail())){
            $show->add('alternative_email');
        }
        if(!is_null($object->getDocumentId())){
            $show->add('DocumentIdPath', null, array('template' => 'WebitForexBoAreaBundle:sonata:document/show_single_doc.html.twig','label'=>'Passport Copy'));
        }
        if(!is_null($object->getDocumentId2())){
             $show->add('DocumentId2Path', null, array('template' => 'WebitForexBoAreaBundle:sonata:document/show_single_doc.html.twig','label'=>'Copy of Civil ID/ Status/ Profile'));
        }
        if(!is_null($object->getDocumentPor())){
            $show->add('documentPorPath', null, array('template' => 'WebitForexBoAreaBundle:sonata:document/show_single_doc.html.twig','label'=>'Copy of proof of residence (lease contract - electricity bill - certificate from the governor) (In the absence of an address in the civil card)'));
        }
        if(!is_null($object->getDocumentPor2())){
            $show->add('documentPor2Path', null, array('template' => 'WebitForexBoAreaBundle:sonata:document/show_single_doc.html.twig','label'=>'Proof of Residence (Utility Bill) 2'));
        }
//        if(!is_null($object->getClientSignature())){
//            $show->add('clientSignaturePath', null, array('template' => 'WebitForexBoAreaBundle:sonata:document/show_single_doc.html.twig','label'=>"Authentication of Client's Signature (Bank - Lawyer - official Entity) "));
//        }
        $show->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('PortalUser.username', null, array('label' => 'Username'))
                ->add('PortalUser.mobile_number')
                ->add('created_at', 'doctrine_orm_date', array('field_type' => 'sonata_type_date_picker'))
        //,'doctrine_orm_datetime_range', array('widget'=>'sonata_type_date_picker')
        ;
    }

    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection) {
        $collection->remove('create');
        $collection->remove('edit');
      //  $collection->remove('delete');
        $collection->add('approve_changes', 'approve_changes/{id}', array(), array('id' => '\d+'));
        $collection->add('reject_changes', 'reject_changes/{id}', array(), array('id' => '\d+'));
    }

    public function getTemplate($name) {
        switch ($name) {
            case 'show':
                return 'WebitForexBoAreaBundle::sonata/edit_request/show.html.twig';
                break;
            default:
                $ret = parent::getTemplate($name);
                break;
        }

        return $ret;
    }

}
