<?php

namespace Webit\CMSBundle\DataFixtures\ORM\SecurityQuestion;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Webit\CMSBundle\Entity\SecurityQuestion;
use Webit\CMSBundle\Entity\SecurityQuestionTranslation;

abstract class AbstractSecurityQuestionFixture extends AbstractFixture {

    /**
     * inserting group of emails data to the database
     * @param ObjectManager $manager
     * @param array $emails_data
     */
    public function insertBatchSecurityQuestionsData(ObjectManager $manager, array $security_questions_data) {

        foreach ($security_questions_data as $key => $security_question_info) {
            $SecurityQuestion = $manager->getRepository('\Webit\CMSBundle\Entity\SecurityQuestionTranslation')
                    ->findOneBy(array('questionText' => $key));

            if (!$SecurityQuestion) {
                $this->insertNewSecurityQuestion($manager, $security_question_info);
            }
        }
    }

    /**
     * inserting new  security question  template into database
     * @param ObjectManager $manager
     * @param array  $security_question_info security question  details to be stored..
     */
    protected function insertNewSecurityQuestion(ObjectManager $manager, array $security_question_info) {
        $SecurityQuestion = new SecurityQuestion();

        $manager->persist($SecurityQuestion);
        $manager->flush();

        foreach ($security_question_info['translations'] as $lang => $trans) {
            $security_question_translate = new SecurityQuestionTranslation();
            $security_question_translate->setLang($lang);
            $security_question_translate->setQuestionText($trans['questionText']);
            $security_question_translate->setTranslationParent($SecurityQuestion);

            $manager->persist($security_question_translate);
            $manager->flush();
        }
    }

}
