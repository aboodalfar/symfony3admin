<?php

namespace Webit\UserLogBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class LoginLogAdmin extends Admin
{    
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'time'
    );


    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list//->add('user_id')
             ->add('FosUser', null, array('label'=>'Performed By'))             
             ->add('ipAddress')                          
             ->add('time')
                ;        
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter ->add('FosUser.username', null, array('label'=>'Username'))                
                ->add('time', 'doctrine_orm_date_range', array('label' => 'Performed At', 'field_type' => 'sonata_type_date_range_picker'))
                ;
    }

}
