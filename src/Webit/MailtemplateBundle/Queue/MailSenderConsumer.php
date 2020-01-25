<?php

namespace Webit\MailtemplateBundle\Queue;

use Webit\MailtemplateBundle\Helper\MailHelper;
use Psr\Log\LoggerInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Broker\Message;

class MailSenderConsumer implements ProcessorInterface{

   /**    
    * @var MailHelper
    */
   private $mailtemplateHelper;

   /**    
    * @var LoggerInterface
    */   
   private $logger;
    
   public function __construct( LoggerInterface $logger, MailHelper $mailtemplateHelper )
   {
      $this->logger = $logger;
      $this->mailtemplateHelper = $mailtemplateHelper;      
   }    

   public function process(Message $message, array $options) {       
        /*@var $mailMessage \Webit\MailtemplateBundle\Helper\MailMessage */
        $mailMessage = unserialize($message->getBody());
     
        $this->mailtemplateHelper->sendImmediate($mailMessage);
        echo $mailMessage->getMailKey()." email Message sent to ".json_encode($mailMessage->getSwiftMessage()->getTo()).chr(10);
        $this->logger->info($mailMessage->getMailKey()." email sent to ".json_encode($mailMessage->getSwiftMessage()->getTo()));            
            
          
        
        return true;
    }

}