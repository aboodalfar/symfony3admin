<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webit\CMSBundle\Entity\Content;

/**
 * ContentTemplate
 *
 * @ORM\Table(name="cms_content_template")
 * @ORM\Entity
 */
class ContentTemplate
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body",type="text",nullable=true)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=5,nullable=true)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255, nullable=true)
     */
    private $icon;
    
    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="Content",inversedBy="ContentTemplate")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translationTemplateParent;
    
   

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
     *
     * @return ContentTemplate
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
     * Set title
     *
     * @param string $title
     *
     * @return ContentTemplate
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
     * Set body
     *
     * @param string $body
     *
     * @return ContentTemplate
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return ContentTemplate
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
     * Set icon
     *
     * @param string $icon
     *
     * @return ContentTemplate
     */
    public function setIcon($icon)
    {
        $upload_path = __DIR__ . '/../../../../web/uploads/content/icons/';
        if (!(file_exists($upload_path) && is_dir($upload_path))) {
            mkdir($upload_path);
            chmod($upload_path, 0777);
    }
        if ($icon) {
            $file_name = time().uniqid(rand()).'.'.
                    $icon->getClientOriginalExtension();
            $new_file = $icon->move($upload_path, $file_name);
            $this->icon = 'uploads/content/icons/'.$file_name;
        }
        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set translationTemplateParent
     *
     * @param \Webit\CMSBundle\Entity\Content $translationTemplateParent
     *
     * @return ContentTemplate
     */
    public function setTranslationTemplateParent(\Webit\CMSBundle\Entity\Content $translationTemplateParent = null)
    {
        $this->translationTemplateParent = $translationTemplateParent;

        return $this;
    }

    /**
     * Get translationTemplateParent
     *
     * @return \Webit\CMSBundle\Entity\Content
     */
    public function getTranslationTemplateParent()
    {
        return $this->translationTemplateParent;
    }
    
    public function __toString() {
        return '#'.$this->id;
    }


    /**
     * Set link
     *
     * @param string $link
     *
     * @return ContentTemplate
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
}
