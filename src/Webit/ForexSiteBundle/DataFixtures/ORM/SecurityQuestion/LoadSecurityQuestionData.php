<?php

namespace Webit\ForexSiteBundle\DataFixtures\ORM\SecurityQuestion;

use Doctrine\Common\Persistence\ObjectManager;
use Webit\CMSBundle\DataFixtures\ORM\SecurityQuestion\AbstractSecurityQuestionFixture;

class LoadSecurityQuestionData extends AbstractSecurityQuestionFixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $data = array(
            'What is your mother\'s maiden name?' =>
            array(
                'translations' => array(
                    'en' => array(
                        'questionText' => 'What is your mother\'s maiden name?',
                    )
                )
            ),
            'What is your first pet\'s name?' => array(
                'translations' => array(
                    'en' => array(
                        'questionText' => 'What is your first pet\'s name?',
                    )
                )
            ),
            'What street did you grow up on?' => array(
                'translations' => array(
                    'en' => array(
                        'questionText' => 'What street did you grow up on?',
                    )
                )
            ),
            'What is your favorite color?' => array(
                'translations' => array(
                    'en' => array(
                        'questionText' => 'What is your favorite color?',
                    )
                )
            ),
            'Who is your favirite musician, artist or actor?' => array(
                'translations' => array(
                    'en' => array(
                        'questionText' => 'Who is your favirite musician, artist or actor?',
                    )
                )
            )
            
//Add additional fixtures here
        );



        $this->insertBatchSecurityQuestionsData($manager, $data);
    }

}

//
//