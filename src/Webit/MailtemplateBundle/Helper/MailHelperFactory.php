<?php

namespace Webit\MailtemplateBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MailHelperFactory{
    
    public static function configureMailHelper(ContainerInterface $container){
        /*@var $mailHandler MailHelper */
        $mailHandler = new MailHelper($container);
        $config = $container->getParameter('webit.mailtemplate');
        
        if($config['queue']['enabled']==true){
            //configure rabbitMQ
            $mailSenderService = $container->get('webit.mailtemplate.producer');
        }else{
            $mailSenderService = $container->get('webit.mailtemplate.default_sender');
        }
        
        $mailHandler->setSenderHandler($mailSenderService);
        
        return $mailHandler;
    }
}