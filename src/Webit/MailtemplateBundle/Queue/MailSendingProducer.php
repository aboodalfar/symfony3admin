<?php

namespace Webit\MailtemplateBundle\Queue;

use Webit\MailtemplateBundle\Helper\MailMessage;
use Webit\MailtemplateBundle\Helper\SenderInterface;
use Swarrot\Broker\Message;

class MailSendingProducer implements SenderInterface{
    
    
    protected $container;
    
    public function __construct($container) {
        $this->container = $container;
    }
    
    public function sendMessage(MailMessage $mailMessage){        
        $message = new Message(serialize($mailMessage));
        $this->container
                ->get('swarrot.publisher')
                ->publish('email_sending_publisher', $message);
    }
}