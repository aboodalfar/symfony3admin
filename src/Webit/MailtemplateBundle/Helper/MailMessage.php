<?php

namespace Webit\MailtemplateBundle\Helper;

class MailMessage{
    
    /**
     * mail template key used in DB
     * @var string
     */
    protected $mail_key;
    
    /**
     *
     * @var \Swift_Message
     */
    protected $swift_mesage;
    
    /**
     *
     * @var array
     */
    protected $data_params;
    
    /**
     *
     * @var string
     */
    protected $to;
    
    /**
     *
     * @var array
     */
    protected $from;
    
    public function setMailKey($mail_key){
        $this->mail_key = $mail_key;
        return $this;
    }
    
    public function getMailKey(){
        return $this->mail_key;
    }
    
    public function getDataParams(){
        return $this->data_params;
    }
    
    public function setDataParams($data){
        $this->data_params = $data;
        return $this;
    }
    
    public function getSwiftMessage(){
        return $this->swift_mesage;
    }
    
    /**
     * create new swift message instance to be used in this mail message
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     * @return \Webit\MailtemplateBundle\Helper\MailMessage
     */
    public function createMessage($subject = null, $body = null, $contentType = null, $charset = null) {
        $this->swift_mesage =  \Swift_Message::newInstance($subject, $body, $contentType, $charset);
        
        return $this;
    }
    
    public function setTo($to){
        $this->swift_mesage->setTo($to);
        $this->to = $to;
        return $this;
    }
    
    public function setFrom($from){
        $this->swift_mesage->setFrom($from);
        $this->from = $from;
        return $this;
    }
    
    /**
     * forward all non-defined methods to \Swift_message instance, as type of decorating the message
     * @param string $name
     * @param array $arguments
     * @return MailMessage
     */
    public function __call($name, $arguments) {
        $ret = call_user_func_array([$this->swift_mesage,$name], $arguments);
        
        if(strpos($name,'get') ===0){
            return $ret;
        }
        
        return $this;
    }
    
    public function __sleep() {
        return ['mail_key','data_params','from','to','swift_mesage'];
    }
    
    public function __wakeup() {
        //TODO: implement wakeup here on consumer;
    }
}
