<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webit\CMSBundle\Entity\MenuItem;

/**
 * MenuItemTranslation
 *
 * @ORM\Table(name="cms_menu_item_translation")
 * @ORM\Entity
 */
class MenuItemTranslation
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
     * @ORM\Column(name="lang", type="string", length=7)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="display_label", type="string", length=255)
     */
    private $displayLabel;
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="description", type="text",nullable=true)
     */
    private $description;


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
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="Translations")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translation_parent;
    /**
     * Set parent menu item
     * @param Webit\CMSBundle\MenuItem $translation_parent
     */
    public function setTranslationParent($translation_parent)
    {
        $this->translation_parent = $translation_parent;
    }

    /**
     * Get menu item parent
     *
     * @return Webit\CMSBundle\MenuItem
     */
    public function getTranslationParent()
    {
        return $this->translation_parent;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return MenuItemTranslation
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
     * Set lang
     *
     * @param string $lang
     * @return MenuItemTranslation
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set displayLabel
     *
     * @param string $displayLabel
     * @return MenuItemTranslation
     */
    public function setDisplayLabel($displayLabel)
    {
        $this->displayLabel = $displayLabel;

        return $this;
    }

    /**
     * Get displayLabel
     *
     * @return string
     */
    public function getDisplayLabel()
    {
        return $this->displayLabel;
    }


    /**
     * Set description
     *
     * @param string $description
     *
     * @return MenuItemTranslation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
