<?php

namespace Webit\UserLogBundle\EventListener;

use Symfony\Component\EventDispatcher\GenericEvent;

class UserLogListener
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function recordLog(GenericEvent $event)
    {
        if (isset($event['type']) && $event['type'] == 'log') {
            $this->container->get('doctrine')->getRepository('WebitUserLogBundle:UserLog')
                    ->createLog($event['user'], $event->getSubject(), $this->getModifierUser(), $event['body']);
            //handle later CRM or salesforce integration ...etc.
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
