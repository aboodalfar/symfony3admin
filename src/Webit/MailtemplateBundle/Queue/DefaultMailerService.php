<?php

namespace Webit\MailtemplateBundle\Queue;

use Webit\MailtemplateBundle\Helper\MailMessage;
use Webit\MailtemplateBundle\Helper\SenderInterface;

class DefaultMailerService implements SenderInterface{
    
    
    protected $mailer;
    
    public function __construct($mailer) {
        $this->mailer = $mailer;
    }
    
    
    public function sendMessage(MailMessage $message){        
        
        $this->mailer->send($message->getSwiftMessage());
    }
    
}