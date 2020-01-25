<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Webit\ForexCoreBundle\Entity\Partnership;
use Symfony\Component\Validator\Constraints as Assert;

class PartnershipAdmin extends Admin {

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper) {
        $docs_mime_types = array('application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword');

        $formMapper
                ->with('Main Information')
                ->add('first_name', 'text', array('constraints' => array(new Assert\NotBlank())))
                ->add('last_name', 'text', array('constraints' => array(new Assert\NotBlank())))
                ->add('email', 'email', array('constraints' => array(new Assert\NotBlank(), new Assert\Email())))
                ->add('type', 'choice', array('choices' => Partnership::$partnership_type, 'constraints' => array(new Assert\NotBlank())))
                ->add('country', 'country', array('empty_value' => 'Select Country', 'constraints' => array(new Assert\Country())))
                ->add('phoneNumber', 'text', array('constraints' => new Assert\NotBlank()))
                ->add('status', 'choice', array('choices' => Partnership::$status_arr))
                ->end();

        /* @var $object RealProfile */
        $object = $this->getSubject();
        $idfile_help = $porfile_help = null ;
        if ($object->getIDFile()) {
            $idfilepath = $object->getIDFilePath();
            $idfile_help = "<a href=/" . $idfilepath . ">Download File</a>";
        }
        
           if ($object->getpORFile()) {
            $porfilepath = $object->getpORFilePath();
            $porfile_help = "<a href=/" . $porfilepath . ">Download File</a>";
        }
        $formMapper
                ->with('Documents')
                ->add('IDFile', 'file', array('sonata_help' => $idfile_help, 'required' => false, 'data_class' => null))
                ->add('pORFile', 'file', array('sonata_help' => $porfile_help, 'required' => false, 'data_class' => null))
                ->end();
        //->add('Active','checkbox', array('required'=>false))
        ;
    }

    protected function configureListFields(ListMapper $list) {
        unset($this->listModes['mosaic']);
        $list
                ->addIdentifier('id')
                ->addIdentifier('email')
                ->addIdentifier('firstName', null, array('route' => array('name' => 'show')))
                ->add('lastName')
                ->add('typeText')
                ->add('createdAt', 'date')
                 ->add('_action', null, [
            'actions' => [
                'show' => [],
                'delete' => [],
            ]
        ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('email')
                ->add('type', null, array('field_type' => 'choice'), null, array('choices' => Partnership::$partnership_type))
                ->add('firstName')
                ->add('lastName')
                ->add('createdAt', 'doctrine_orm_date', array('field_type' => 'sonata_type_date_picker', 'label' => 'Registration Date'))
        ;
    }

    protected function configureShowFields(\Sonata\AdminBundle\Show\ShowMapper $show) {
        $show
                ->with('Main Information')
                ->add('firstName', null, array('label' => 'First name'))
                ->add('lastName', null, array('label' => 'Last Name'))
                ->add('email', null, array('label' => 'Email'))
                ->add('phoneNumber', null, array('label' => 'Mobile Number'))
                ->add('company', null, array('label' => 'company'))
                ->add('numberClients', null, array('label' => 'Expected number of clients'))
                ->add('funds', null, array('label' => 'Expected funds under management'))
                ->add('website', null, array('label' => 'website'))
                ->add('skype', null, array('label' => 'skype'))
                ->add('typeText', null, array('label' => 'Partbership Type'))
                ->add('createdAt', 'date', array('label' => 'Registration Date'))
                
                ->end()
//                ->with("PDF")
//                ->add('pdfDoc', null, array('template' => 'WebitForexBoAreaBundle::sonata/Partnership/pdf_doc.html.twig'))
//                ->end()
//                ->with('Documents')
//                ->add('IDFilePath', 'image')
//                ->add('pORFilePath', 'image')
//                ->end()
                ;
    }

    protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection) {
        $collection->add('chartsStats', 'charts-and-stats', array());
        $collection->remove('edit')->remove('create');
    }
    
    
    public function preUpdate($IBUsers) {
        /* @var $IBUsers Partnership */
        
        
        if ($IBUsers->getIDFile() instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $filename = $IBUsers->uploadOneFile($IBUsers->getIDFile());
            $IBUsers->setIDFile($filename);
        }
        if ($IBUsers->getPORFile() instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $filename = $IBUsers->uploadOneFile($IBUsers->getPORFile());
            $IBUsers->setPORFile($filename);
        }
    }

    public function prePersist($IBUsers) {
        if ($IBUsers->getIDFile() instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $filename = $IBUsers->uploadOneFile($IBUsers->getIDFile());
            $IBUsers->setIDFile($filename);
        }
        if ($IBUsers->getPORFile() instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $filename = $IBUsers->uploadOneFile($IBUsers->getPORFile());
            $IBUsers->setPORFile($filename);
        }
    

}
}
