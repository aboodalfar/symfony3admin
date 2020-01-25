<?php

namespace Webit\ForexSiteBundle\DataFixtures\ORM\Emails;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Webit\MailtemplateBundle\Entity\MailTemplate;
use Webit\MailtemplateBundle\Entity\MailTemplateTranslation;

class LoadMailData extends AbstractFixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $data = array(
            'demo_activation_email' => array(
                'note' => 'sent upon user registration demo and contains activation url',
                'translations' => array(
                    'en' => array(
                        'subject' => 'Confirm your information',
                        'mail_body' => '<p style="font-weight: bold; margin:0; color: #141414; font-size: 17px;text-transform: capitalize;">Welcome,<span>%full_name%</span> </p>
                           <p style="font-size: 15px; color: #141414; font-weight: bold; margin:0; padding-top: 17px; line-height: 20px;">Thank you for registering, please follow the below link to activate your account:</p>
                           <p><a href="%activation_link%" style="display:inline-block;height: 36px;width: 260px;padding: 8px 0;font-size:16px;font-weight:bold;text-transform:uppercase;color: #fff;line-height: 39px;text-decoration:none;background-color: #ffa800;text-align:center;" target="_blank">Activate Account</a></p>
                           <p><b style=" font-size:  14px;">or Click to this link :<b><br><br><a href="%activation_link%" style="color: #ffa800;line-height:1;text-decoration:  underline;" target="_blank">%activation_link%</a></p>
                         
                        '
                    ),
                )
            ),
            'activation_email' => array(
                'note' => 'sent to new real user to activate/confirm his info',
                'override' => true,
                'translations' => array(
                    'en' => array(
                        'subject' => 'Email verification',
                        'mail_body' => <<<OEF
Dear %full_name%, <br/>
<br/> 
Thank you for registration.  <br/> <br/> 
In order to complete your registration, you must confirm your e­mail address. Please follow on the link below:  <br/> 
<a href="%activation_link%">%activation_link%</a> <br/>  
In order to complete your application. Thank you.<br/> 

OEF
                    ),
                ),
            ),
             'newuser_registered_to_bo' => array(
                'note' => 'Sent to backoffice upon new registration submitted',
                'override' => true,
                'translations' => array(
                    'en' => array(
                        'subject' => 'New real registration form submitted',
                        'mail_body' => <<<OEF
                      please check attached file
OEF
,
                        
                    )
                )
            ),
             'forwared_to_compliace_email' => array(
                'note' => 'internal mail, sent to compliance upon application forwarded to him',
                'override' => true,
                'translations' => array(
                    'en' => array(
                        'subject' => 'New Application Forwarded from Backoffice',
                        'mail_body' => <<<OEF
                      <p>Dear Sir,</p>

<p>New application has been forwarded to you with the following &nbsp;details:</p>

<p>E-mail: %username%</p>

<p>Full Name: %full_name%</p>
OEF
,
                        
                    )
                )
            ),
            'demoRegistrationsSuccess' => array(
                'note' => 'sent upon user registration demo success',
                'translations' => array(
                    'en' => array(
                        'subject' => 'Thank you for registering with Baxia',
                        'mail_body' => 'Hello %full_name%,<br/><br/>
                        Thank you for registering with Baxia,<br/><br/>
                        please find link to download trading platform below:<br/><br/>
                        %links%<br/><br/>
                        Regards,'
                    ),
                )
            ),
             'realRegistrationsSuccess' => array(
                'note' => 'sent upon user registration demo success',
                'translations' => array(
                    'en' => array(
                        'subject' => 'Thank you for registering with Baxia',
                        'mail_body' => 'Hello %full_name%,<br/><br/>
                        Thank you for registering with Baxia,<br/><br/>
                        please find link to download trading platform below:<br/><br/>
                        %links%<br/><br/>
                        Regards,'
                    ),
                )
            ),
        );



        foreach ($data as $mail_key => $mail_info) {
            $mail = $manager->getRepository('\Webit\MailtemplateBundle\Entity\MailTemplate')->findOneBy(array('name' => $mail_key));

            if (!$mail) { //in order to prevent adding multiple mail templates with the same name
                $mail = new MailTemplate();
                $mail->setName($mail_key);
                $mail->setNote($mail_info['note']);

                $manager->persist($mail);
                $manager->flush();

                foreach ($mail_info['translations'] as $lang => $trans) {
                    $mail_translate = new MailTemplateTranslation();
                    $mail_translate->setLang($lang);
                    $mail_translate->setSubject($trans['subject']);
                    $mail_translate->setMailBody($trans['mail_body']);
                    $mail_translate->setTranslationParent($mail);

                    $manager->persist($mail_translate);
                    $manager->flush();
                }
            }
        }
    }

}
