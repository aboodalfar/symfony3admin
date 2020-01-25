<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webit\CMSBundle\Entity\SecurityQuestion;

/**
 * MenuItemTranslation
 *
 * @ORM\Table(name="cms_security_question_translation")
 * @ORM\Entity
 */
class SecurityQuestionTranslation{
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
     * @ORM\Column(name="lang", type="string", length=7)
     */
    private $lang;
    
    /**
     * @ORM\ManyToOne(targetEntity="Webit\CMSBundle\Entity\SecurityQuestion", inversedBy="Translations")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translation_parent;
    
    /**
     * @var string
     *
     * @ORM\Column(name="question_text", type="string", length=500)
     */
    private $questionText;
    
    /**
     * Set parent SecurityQuestion
     * @param Webit\CMSBundle\Entity\SecurityQuestion $translation_parent
     */
    public function setTranslationParent($translation_parent)
    {
        $this->translation_parent = $translation_parent;
    }

    /**
     * Get translation parent
     *
     * @return Webit\CMSBundle\Entity\SecurityQuestion
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
     * @return SecurityQuestionTranslation
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
     * @return SecurityQuestionTranslation
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