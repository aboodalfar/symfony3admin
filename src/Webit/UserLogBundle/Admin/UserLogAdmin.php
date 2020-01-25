<?php

namespace Webit\UserLogBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserLogAdmin extends Admin
{    
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );


    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list//->add('user_id')
             ->add('FosUser', null, array('label'=>'Performed By'))
             ->add('LoggedUser')
             ->add('subject')
             //->add('modifierUser')
             ->add('body',null, array('template' => 'WebitUserLogBundle:UserLog:log_body.html.twig'))
             ->add('createdAt')
                ;
        //parent::configureListFields($list);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter ->add('LoggedUser.username', null, array('label'=>'User Email'))
                ->add('subject')
                ->add('rejectReason', 'doctrine_orm_callback', array(
                    'callback' => array($this, 'getrejectReason'),
                    'field_type' => 'text',
                    'label' => 'Reject Reason'
                ))
                ->add('body',null, array('label'=>'modifier name (or description)'))
                ->add('createdAt', 'doctrine_orm_date_range', array('label' => 'Performed At', 'field_type' => 'sonata_type_date_range_picker'))       
                ->add('full_name', 'doctrine_orm_callback', array(
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value || $value['value'] == '') {
                        return;
                    }
                    $queryBuilder->leftJoin(sprintf('%s.LoggedUser', $alias), 'p');
                    
                    $queryBuilder->andWhere('CONCAT(' . 'p.first_name,CONCAT(\' \', ' .  'p.second_name),CONCAT(\' \', ' .  'p.third_name), CONCAT(\' \', ' .  'p.last_name)) LIKE :term')->setParameter(':term', '%' . $value['value'] . '%');
                    return true;
                },
                'field_type' => 'text'
            ));
    }

    /**
     * filter non field of entity
     * @param type $queryBuilder
     * @param type $alias
     * @param type $field
     * @param type $value
     * @return boolean
     */
    public function getRejectReason($queryBuilder, $alias, $field, $value) {

        if (!$value['value']) {
            return;
        }
        $queryBuilder                
                ->andWhere($alias.'.body like :reject')
                ->setParameter(':reject',  '%reject%' . $value['value'] . '%');

        return true;
    }

}
