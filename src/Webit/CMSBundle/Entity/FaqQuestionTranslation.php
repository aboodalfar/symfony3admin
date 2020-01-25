<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FaqQuestionTranslation
 *
 * @ORM\Table(name="cms_faq_question_translation")
 * @ORM\Entity
 */
class FaqQuestionTranslation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="question_text", type="string", length=500)
     */
    private $questionText;

    /**
     * @var string
     *
     * @ORM\Column(name="answer_text", type="text")
     */
    private $answerText;


    /**
     * @ORM\ManyToOne(targetEntity="FaqQuestion", inversedBy="Translations")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translation_parent;

    /**
     * Set parent faq question
     * @param Webit\CMSBundle\FaqQuestion $translation_parent
     */
    public function setTranslationParent($translation_parent)
    {
        $this->translation_parent = $translation_parent;
    }

    /**
     * Get translation parent
     *
     * @return Webit\CMSBundle\FaqQuestion
     */
    public function getTranslationParent()
    {
        return $this->translation_parent;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return FaqQuestionTranslation
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set questionText
     *
     * @param string $questionText
     * @return FaqQuestionTranslation
     */
    public function setQuestionText($questionText)
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * Get questionText
     *
     * @return string
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * Set asnwerText
     *
     * @param string $asnwerText
     * @return FaqQuestionTranslation
     */
    public function setAnswerText($answerText)
    {
        $this->answerText = $answerText;

        return $this;
    }

    /**
     * Get answerText
     *
     * @return string
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }
}
