<?php

namespace Webit\UserLogBundle\EventListener;

use Symfony\Component\EventDispatcher\GenericEvent;

class BatchActivityLogListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function recordLog(GenericEvent $event)
    {        
        if (isset($event['type']) && $event['type'] == 'batch_log') {
            /*@var $batchLogRepo \Webit\UserLogBundle\Entity\BatchActivityLogRepository */
            $batchLogRepo = $this->container->get('doctrine')->getRepository('WebitUserLogBundle:BatchActivityLog');
            $batchLogRepo->createLog($this->getModifierUser(),
                    $event['module'],
                    $event['operation_type'],
                    $event['records_count'],
                    $event['ip']);
            
        }
    }

    public function getModifierUser()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(is_object($user)){
            return $user;
        }else{
            return 0; //no user detected..
        }
    }

}
