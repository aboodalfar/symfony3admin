<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * @ORM\Table(name="cms_content")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\ContentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Content extends TranslatableEntity
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
     * @var boolean
     *
     * @ORM\Column(name="is_published", type="boolean")
     */
    private $isPublished;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;


    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="side_menu_item_id", type="integer", nullable=true)
     */
    private $sideMenuItemId;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="contents")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $ContentCategory;
    
    
     /**
     * @var string
     *
     * @ORM\Column(name="template",type="string",length=255,nullable=true)
     */
    private $template;
    
   


    /**
     * getting the content category of the page
     * @return ContentCategory
     */
    public function getContentCategory()
    {
        return $this->ContentCategory;
    }

    /**
     * set ContentCategory of the page
     * @param \Webit\CMSBundle\Entity\Category $contentCategory
     * @return \Webit\CMSBundle\Entity\Content
     */
    public function setContentCategory(Category $contentCategory)
    {
        $this->ContentCategory = $contentCategory;

        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Menu")
     * @ORM\JoinColumn(name="side_menu_id", referencedColumnName="id")
     */
    private $SideMenu;
    
    /**
     * @ORM\ManyToOne(targetEntity="MenuItem")
     * @ORM\JoinColumn(name="side_menu_item_id", referencedColumnName="id")
     */
    private $SideMenuItem;

    /**
     * @var ArrayCollection $Translations
     * @ORM\OneToMany(targetEntity="ContentTranslation", mappedBy="translation_parent", cascade={"persist","remove"}, orphanRemoval=true)
     * */
    private $Translations;
    
    
    
    /**
     * @var ArrayCollection $ContentsTemplate
     * @ORM\OneToMany(targetEntity="ContentTemplate", mappedBy="translationTemplateParent", cascade={"persist","remove"}, orphanRemoval=true)
     * */
    private $ContentsTemplate;

    public function __construct()
    {
        $this->Translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ContentsTemplate = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * get side menu related to this page
     * @return Menu
     */
    public function getSideMenu()
    {
        return $this->SideMenu;
    }

    /**
     * get side menu
     * @param \Webit\CMSBundle\Entity\Menu $sideMenu
     */
    public function setSideMenu(Menu $sideMenu = null)
    {
        $this->SideMenu = $sideMenu;
    }

    public function getTranslations()
    {
        return $this->Translations;
    }

    /**
     *
     * @param \Webit\CMSBundle\Entity\ContentTranslation $translation
     */
    public function addTranslationItem($translation)
    {
        $this->Translations[] = $translation;
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
     * Set isPublished
     *
     * @param boolean $isPublished
     * @return Content
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Content
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     * @return Content
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($created_at)
    {
        $this->createdAt = $created_at;
    }

    public function getTranslatableColumns()
    {
        return array(
            'title',
            'body',
            'video',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'brief',
            'image',
            'upperContent'
        );
    }

    public function getTranslationEntityName()
    {
        return 'Webit\CMSBundle\Entity\ContentTranslation';
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->slug)) {
            $this->slug = $this->generateSlug();
        }
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function generateSlug()
    {
        $translations = $this->getTranslations();
        $trans = @$translations[0];
        if ($trans) {
            //var_dump($trans); die();
            $text = $trans->getTitle();
            $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
            $text = trim($text, '-');
            //$text = strtolower($text);
            //$text = preg_replace('~[^-\w]+~', '', $text);
            if (empty($text)) {
                return 'n-a';
            }
            return strtolower($text);
        }
    }

    public function getTitle($lang = 'en')
    {
        $trans = $this->getTranslationValues();

        if ($trans) {
            $current_trans = current($trans);
            return isset($trans[$lang]['title'])?$trans[$lang]['title']:  $current_trans['title'];
        }
        return 'N.A';
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getBody($lang = 'en')
    {
        $trans = $this->getTranslationValues();

        if ($trans) {
            return isset($trans[$lang]['body'])?$trans[$lang]['body']:'';
        }
        return 'N.A';
    }

    /**
     * Add Translations
     *
     * @param \Webit\CMSBundle\Entity\ContentTranslation $translations
     * @return Content
     */
    public function addTranslation(\Webit\CMSBundle\Entity\ContentTranslation $translations)
    {
        $this->Translations[] = $translations;

        return $this;
    }

    /**
     * Remove Translations
     *
     * @param \Webit\CMSBundle\Entity\ContentTranslation $translations
     */
    public function removeTranslation(\Webit\CMSBundle\Entity\ContentTranslation $translations)
    {
        $this->Translations->removeElement($translations);
    }






    /**
     * Set sideMenuItem
     *
     * @param \Webit\CMSBundle\Entity\MenuItem $sideMenuItem
     *
     * @return Content
     */
    public function setSideMenuItem(\Webit\CMSBundle\Entity\MenuItem $sideMenuItem = null)
    {
        $this->SideMenuItem = $sideMenuItem;

        return $this;
    }

    /**
     * Get sideMenuItem
     *
     * @return \Webit\CMSBundle\Entity\MenuItem
     */
    public function getSideMenuItem()
    {
        return $this->SideMenuItem;
    }

    /**
     * Set sideMenuItemId
     *
     * @param integer $sideMenuItemId
     *
     * @return Content
     */
    public function setSideMenuItemId($sideMenuItemId)
    {
        $this->sideMenuItemId = $sideMenuItemId;

        return $this;
    }

    /**
     * Get sideMenuItemId
     *
     * @return integer
     */
    public function getSideMenuItemId()
    {
        return $this->sideMenuItemId;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return Content
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Add contentsTemplate
     *
     * @param \Webit\CMSBundle\Entity\ContentTemplate $contentsTemplate
     *
     * @return Content
     */
    public function addContentsTemplate(\Webit\CMSBundle\Entity\ContentTemplate $contentsTemplate)
    {
        $contentsTemplate->setTranslationTemplateParent($this);//custom
        $this->ContentsTemplate[] = $contentsTemplate;

        return $this;
    }


    /**
     * Remove contentsTemplate
     *
     * @param \Webit\CMSBundle\Entity\ContentTemplate $contentsTemplate
     */
    public function removeContentsTemplate(\Webit\CMSBundle\Entity\ContentTemplate $contentsTemplate)
    {
        $this->ContentsTemplate->removeElement($contentsTemplate);
    }

    /**
     * Get contentsTemplate
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContentsTemplate()
    {
        return $this->ContentsTemplate;
    }
}
