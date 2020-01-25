<?php

namespace Webit\MailtemplateBundle\Helper;
use Webit\MailtemplateBundle\Helper\MailMessage;

interface SenderInterface{
    
    public function sendMessage(MailMessage $mailMessage);
}
