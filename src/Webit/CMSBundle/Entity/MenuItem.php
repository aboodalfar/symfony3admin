<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webit\CMSBundle\Entity\Content;

/**
 * MenuItem
 *
 * @ORM\Table(name="cms_menu_item")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\MenuItemRepository")
 */
class MenuItem extends TranslatableEntity
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
     * @ORM\Column(name="menu_id", type="integer")
     */
    private $menuId;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_menu_item_id", type="integer",nullable=true)
     */
    private $parentMenuItemId;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     */
    private $route;

    /**
     * @var integer
     *
     * @ORM\Column(name="content_id", type="integer",nullable=true)
     */
    private $contentId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_target_blank", type="boolean")
     */
    private $isTargetBlank;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="smallint",nullable=true)
     */
    private $weight;

    /**
     * @var ArrayCollection $Translations
     * @ORM\OneToMany(targetEntity="MenuItemTranslation", mappedBy="translation_parent", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $Translations;

    /**
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="ChildrenItems")
     * @ORM\JoinColumn(name="parent_menu_item_id", referencedColumnName="id")
     */
    private $parentMenuItem;    

    /**
     * @var ArrayCollection $ChildrenItems
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parentMenuItem", cascade={"persist","remove"}, orphanRemoval=true)
     **/
    private $ChildrenItems;

    public function __construct()
    {
        $this->Translations = new ArrayCollection();
        $this->ChildrenItems = new ArrayCollection();
    }

    /**
     * Add translation
     *
     * @param Webit\CMSBundle\Entity\MenuItemTranslation $translation
     */
    public function addTranslationItem($translation)
    {
        $translation->setParentId($this->id);
        $this->Translations[] = $translation;
    }

    public function getTranslations()
    {
        return $this->Translations;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="menu_items")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     */
    private $menu;

    /**
     * setting the parent menu
     * @param \Webit\CMSBundle\Entity\Menu $menu
     * @return \Webit\CMSBundle\Entity\MenuItem
     */
    public function setMenu(Menu $menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     *
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    public function setParentMenuItem(MenuItem $parentMenuItem = null)
    {
        $this->parentMenuItem = $parentMenuItem;
    }

    public function getParentMenuItem()
    {
        return $this->parentMenuItem;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Content")
     * @ORM\JoinColumn(name="content_id", referencedColumnName="id")
     */
    private $content;

    public function setContent(Content $content = null)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
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
     * Set menuId
     *
     * @param integer $menuId
     * @return MenuItem
     */
    public function setMenuId($menuId)
    {
        $this->menuId = $menuId;

        return $this;
    }

    /**
     * Get menuId
     *
     * @return integer
     */
    public function getMenuId()
    {
        return $this->menuId;
    }

    /**
     * Set parentMenuItemId
     *
     * @param integer $parentMenuItemId
     * @return MenuItem
     */
    public function setParentMenuItemId($parentMenuItemId)
    {
        $this->parentMenuItemId = $parentMenuItemId;

        return $this;
    }

    /**
     * Get parentMenuItemId
     *
     * @return integer
     */
    public function getParentMenuItemId()
    {
        return $this->parentMenuItemId;
    }

    /**
     * Set route
     *
     * @param string $route
     * @return MenuItem
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set contentId
     *
     * @param integer $contentId
     * @return MenuItem
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Get contentId
     *
     * @return integer
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return MenuItem
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isTargetBlank
     *
     * @param boolean $isTargetBlank
     * @return MenuItem
     */
    public function setIsTargetBlank($isTargetBlank)
    {
        $this->isTargetBlank = $isTargetBlank;

        return $this;
    }

    /**
     * Get isTargetBlank
     *
     * @return boolean
     */
    public function getIsTargetBlank()
    {
        return $this->isTargetBlank;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return MenuItem
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    public function __toString()
    {
        return $this->getLabel() . ' - ' . $this->getMenu();
    }

    public function getLabel($lang = 'en')
    {
        $trans = $this->getTranslationValues();


        if ($trans) {
            return $trans[$lang]['display_label'];
        }
        return 'N.A';
    }

    public function getLabelAR($lang = 'ar')
    {
        $trans = $this->getTranslationValues();


        if ($trans) {
            if (isset($trans[$lang])) {
                return $trans[$lang]['display_label'];
            } else {
                $elem = current($trans);
                return $elem['display_label'];
            }
        }
        return 'N.A';
    }

    public function getTranslationEntityName()
    {
        return 'Webit\CMSBundle\Entity\MenuItemTranslation';
    }

    public function getTranslatableColumns()
    {
        return array(
            'display_label','description'
        );
    }

    /**
     * Add Translations
     *
     * @param \Webit\CMSBundle\Entity\MenuItemTranslation $translations
     * @return MenuItem
     */
    public function addTranslation(\Webit\CMSBundle\Entity\MenuItemTranslation $translations)
    {
        $this->Translations[] = $translations;

        return $this;
    }

    /**
     * Remove Translations
     *
     * @param \Webit\CMSBundle\Entity\MenuItemTranslation $translations
     */
    public function removeTranslation(\Webit\CMSBundle\Entity\MenuItemTranslation $translations)
    {
        $this->Translations->removeElement($translations);
    }


    /**
     * Set link
     *
     * @param string $link
     * @return MenuItem
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * adding children menu item 
     * @param \Webit\CMSBundle\Entity\MenuItem $item
     * @return \Webit\CMSBundle\Entity\MenuItem
     */
    public function addChildrenItem(MenuItem $item){
        $this->ChildrenItems[] = $item;
        return $this;
    }
    
    /**
     * getting children items
     * @return ArrayCollection
     */
    public function getChildrenItems(){
        return $this->ChildrenItems;
    }
}
