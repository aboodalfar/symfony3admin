<?php

namespace Webit\MailtemplateBundle\DataFixtures\ORM\Email;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Webit\MailtemplateBundle\Entity\MailTemplate;
use Webit\MailtemplateBundle\Entity\MailTemplateTranslation;

abstract class AbstractMailFixture extends AbstractFixture {

   /**
    * inserting group of emails data to the database
    * @param ObjectManager $manager
    * @param array $emails_data
    */
   public function insertBatchEmailsData(ObjectManager $manager, array $emails_data){

        foreach ($emails_data as $mail_key => $mail_info) {
            $mail = $manager->getRepository('\Webit\MailtemplateBundle\Entity\MailTemplate')
                    ->findOneBy(array('name' => $mail_key));

            if (!$mail) { //in order to prevent adding multiple mail templates with the same name
                $this->insertNewEmail($manager, $mail_key, $mail_info);
            }
        }    
   }


   /** 
    * inserting new email template into database
    * @param ObjectManager $manager
    * @param string $mail_key name of mail template to be send
    * @param array  $mail_info email details to be stored..
    */
   protected function insertNewEmail(ObjectManager $manager, $mail_key, array $mail_info){
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