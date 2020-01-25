<?php

namespace Webit\UserLogBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webit\UserLogBundle\Entity\UserLog;


class ChangeLogListener
{
    public static $logged = false; //to indicate that this transaction has been logged or not

    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function preUpdate(\Doctrine\ORM\Event\PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        $loggable_models = $this->container->getParameter('loggable_models');

        foreach( $loggable_models as $model_info ){
            if($entity instanceof $model_info['name']){ //if its one of the loggable models
                  $changed_vals = $args->getEntityChangeSet();
                  if(!self::$logged && count($changed_vals)){
                        self::$logged = true; //set as logged once this method is entered...
                        $this->logChange($entity, $entityManager, $changed_vals, $model_info['owner_method']);
                 }
            }
        }


    }

    protected function logChange($entity, $entityManager, $changed_vals, $owner_method = 'getUser')
    {
        $owner_user = null;
        if(!empty($owner_method)){
            $owner_user = $entity->$owner_method();
        }else{
            $owner_user = $entity;
        }

        $log_text = 'The following values where updated:<br/>';

        foreach($changed_vals as $k => $val){
            $old_val = $val[0];
            $new_val = $val[1];
            if($old_val instanceof \DateTime){
                $old_val = date('Y-m-d H:i:s',$old_val->getTimestamp());
            }

            if($new_val instanceof \DateTime){
                $new_val = date('Y-m-d H:i:s',$new_val->getTimestamp());
            }
            if($old_val != $new_val){
                $method_name = 'get'.ucfirst($k).'Label'; //handle numeric values to be logged in more readable format..
                if(method_exists($entity, $method_name)){
                    $old_val = $entity->$method_name($old_val);
                    $new_val = $entity->$method_name($new_val);

                    if($old_val == $new_val) continue; //if label is the same
                }
                $log_text .= $k.':   From: <b>'.$old_val.'</b>   To:<b>'.$new_val.'</b><br/>';
            }
        }

        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent("Change on user data", array('type' => 'log', 'user' => $owner_user, 'body' => $log_text )) );
    }


}
