<?php

namespace Webit\MailtemplateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Webit\MailtemplateBundle\Helper\MailHelper;

class DefaultController extends Controller
{
    /**
     * controller to test sending email functionality with template applied
    **/
    public function testAction()
    {
        $name = $this->getRequest()->get('name');
        if(empty($name)){
            $name = 'test';
        }
        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
        $message = $mailtemplate_helper->configureMail('test');
        if($message){
            $mailtemplate_helper->send($message);
        }else{
            throw new \Exception('error');
        }
        return new \Symfony\Component\HttpFoundation\Response("Done...");
    }
}
