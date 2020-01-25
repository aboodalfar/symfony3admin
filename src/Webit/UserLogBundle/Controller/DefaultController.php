<?php

namespace Webit\UserLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Symfony\Component\EventDispatcher\GenericEvent as GenericEvent;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use Webit\UserLogBundle\Entity\UserLog;

class DefaultController extends Controller
{
    
    public function dashboardBlockAction(){
        $logs = $this->getDoctrine()->getRepository('\Webit\UserLogBundle\Entity\UserLog')
                ->getLatestLogs(6);
        return $this->render('WebitUserLogBundle::Default/dashboard_block.html.twig',
                array('logs'=>$logs));
    }
}
