<?php

namespace Webit\ForexBoAreaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Webit\ForexCoreBundle\Entity\Spread;

class SpreadAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('symbol')
            ->add('standardMinimumSpread')
            ->add('proMinimumSpread')
            ->add('order')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('symbol',null,array( 'editable' => true))
            ->add('standardMinimumSpread',null,array( 'editable' => true))
            ->add('proMinimumSpread',null,array( 'editable' => true))
            ->add('order',null,array( 'editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        
         $option =array();
         if($this->getSubject()->getId() == 0){
            $count = $this->getModelManager()
           ->getEntityManager(Spread::class)
           ->createQueryBuilder('c')
           ->select('count(c.id)')
           ->from('WebitForexCoreBundle:Spread', 'c')
           ->getQuery()->getSingleScalarResult();
             $option['data']=$count+1;
         }
         
        $formMapper
            ->add('symbol')
            ->add('standardMinimumSpread')
            ->add('proMinimumSpread')
            ->add('order',null,$option);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('symbol')
            ->add('standardMinimumSpread')
            ->add('proMinimumSpread')
            ->add('order')
        ;
    }
    
    public function toStfring($object)
    {
        return $object instanceof \Webit\ForexCoreBundle\Entity\Spread
            ? $object->getSymbol()
            : 'Spread'; // shown in the breadcrumb on the create view
    }
    
     protected function configureRoutes(\Sonata\AdminBundle\Route\RouteCollection $collection)
    {
        $collection->remove('edit');
    }
}
