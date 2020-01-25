<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FaqCategoryTranslation
 *
 * @ORM\Table(name="cms_faq_category_translation")
 * @ORM\Entity
 */
class FaqCategoryTranslation
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2)
     */
    private $lang;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer")
     */
    private $parentId;


    /**
     * @ORM\ManyToOne(targetEntity="FaqCategory", inversedBy="Translations")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translation_parent;
    /**
     * Set parent faq category
     * @param Webit\CMSBundle\FaqCategory $translation_parent
     */
    public function setTranslationParent($translation_parent)
    {
        $this->translation_parent = $translation_parent;
    }

    /**
     * Get category translation parent
     *
     * @return Webit\CMSBundle\FaqCategory
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
     * Set title
     *
     * @param string $title
     * @return FaqCategoryTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return FaqCategoryTranslation
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
