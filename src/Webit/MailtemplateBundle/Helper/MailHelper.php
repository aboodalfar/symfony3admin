<?php

namespace Webit\MailtemplateBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Webit\MailtemplateBundle\Exception\TemplateNotFoundException;
use Webit\MailtemplateBundle\Helper\SenderInterface;

class MailHelper implements ContainerAwareInterface
{
    protected $container;
    protected $templating;
    protected $doctrine;
    protected $config;  
    
    /**
     * mail sender service, either direct or from certain queue
     * @var SenderInterface
     */
    protected $mailSenderService;

    /**
     * construct helper object, with injecting doctrine and twig engine to the class
     *
     * @param TimedTwigEngine $templating
     * @param Doctrine $doctrine
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->templating = $container->get('templating');
        $this->doctrine = $container->get('doctrine');
        $this->config = $container->getParameter('webit.mailtemplate');
   }

    /**
     * set service handler
     * @param SenderInterface $mailSenderService
     */
    public function setSenderHandler(SenderInterface $mailSenderService){
        $this->mailSenderService = $mailSenderService;
    }   
   
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
           
    /**
     * configure the email to be sent from database driven text with custom mail layout
     *
     * @param string $mail_key
     * @param array $replace_params
     * @param string $to
     * @param string $locale
     * @throws TemplateNotFoundException
     * @return \Swift_Message
     */
    public function configureMail($mail_key, $replace_params = array(), $to = null, $locale = 'en')
    {
        $email_trans = $this->doctrine->getRepository('Webit\MailtemplateBundle\Entity\MailTemplate')->getTransByKey($mail_key, $locale);
        if(!$email_trans){
            throw new TemplateNotFoundException("Email template named '".$mail_key."' is not stored in database");
        }

        $subject = strtr($email_trans->getSubject(), $replace_params);
        $mail_body = strtr($email_trans->getMailBody(), $replace_params);

        $mail_txt = $this->getMailLayoutText($locale, $subject, $mail_body);

        if (is_null($to)) {
            $to = $this->config['default_receiver'];
        }
        
        
        $message = (new MailMessage())
                ->createMessage($subject,$mail_txt,'text/html')
                ->setMailKey($mail_key)                
                ->setTo($to)           
                ->setFrom([$this->config['sender_email'] => $this->config['sender_name']])
                ->setDataParams($replace_params)
        ;
        if (isset($this->config['bcc'])) {//auto BBC
            foreach($this->config['bcc'] as $bcc_email){
                $message->addBcc($bcc_email);
            }
        }

        $reply_to = $this->config['reply_email'];
        if ($email_trans->getTranslationParent()->getReplyTo()) {
            $reply_to = $email_trans->getTranslationParent()->getReplyTo();
        }
        $message->setReplyTo($reply_to);


        return $message;
    }
    
    /**
     * sending configured mail message
     * @param \Webit\MailtemplateBundle\Helper\MailMessage $message
     */
    public function send(MailMessage $message){      
        $this->mailSenderService->sendMessage($message);
        $this->logMessage($message);
    }
    
    /**
     * sending configured mail message immediately (through default mailer without queue)
     * @param \Webit\MailtemplateBundle\Helper\MailMessage $message
     */
    public function sendImmediate(MailMessage $message){ 
        
        /*@var $mailer \Swift_Mailer */    
        $mailer = $this->container->get('mailer');        
        $ret = $mailer->send($message->getSwiftMessage());                
        
        $this->logMessage($message);
        
    }
    
    /**
     * logging message sending activity
     * @param MailMessage $message
     */
    protected function logMessage(MailMessage $message){
        if($this->config['enable_log']){            
            /*@var $logger \Psr\Log\LoggerInterface */
            $logger = $this->container->get('webit.mailtemplate.logger');
            $logger->info('email "'.$message->getMailKey().'" to '.
                       json_encode($message->getTo()),$message->getDataParams() );            
        }        
    }

    /**
     * get mail layout html text according to the passed language
     * @param String $locale
     * @return String
     */
    protected function getMailLayoutText($locale, $subject, $mail_body)
    {
        $mail_layout = $this->config['mail_layout'];
        return $this->templating->render($mail_layout, 
                array('subject' => $subject, 'body' => $mail_body,'locale'=>$locale));
    }

}
