<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webit\CMSBundle\Entity\Content;

/**
 * ContentTranslation
 *
 * @ORM\Table(name="cms_content_translation")
 * @ORM\Entity
 */
class ContentTranslation
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
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=5)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="video", type="string", length=255, nullable=true)
     */
    private $video;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", length=1000, nullable=true)
     */
    private $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", length=1000, nullable=true)
     */
    private $metaDescription;


    /**
     * @ORM\ManyToOne(targetEntity="Content", inversedBy="Translations")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $translation_parent;
    
    /**
     * @var string
     *
     * @ORM\Column(name="brief",type="text",nullable=true)
     */
    private $brief;
    
    /**
     * @var string
     *
     * @ORM\Column(name="upper_content",type="text",nullable=true)
     */
    private $upperContent;
    
    /**
     * Set parent menu item
     * @param Webit\CMSBundle\Content $translation_parent
     */
    public function setTranslationParent($translation_parent)
    {
        $this->translation_parent = $translation_parent;
    }

    /**
     * Get content parent
     *
     * @return Webit\CMSBundle\Content
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
     * @return ContentTranslation
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
     * @return ContentTranslation
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
     * @return ContentTranslation
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
     * @return ContentTranslation
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return ContentTranslation
     */

    public function setImage($image)
    {
        $upload_path = __DIR__ . '/../../../../web/uploads/content/';
        if (!(file_exists($upload_path) && is_dir($upload_path))) {
            mkdir($upload_path);
        }

        if ($image) {
            $file_name = md5(time(). $image->getClientOriginalName()).'.'.
                    $image->getClientOriginalExtension();
            $new_file = $image->move($upload_path, $file_name);
            $this->image = 'uploads/content/' . $file_name;
        }
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return (!empty($this->image) && !is_null($this->image)?'/'.$this->image:
                ('/bundles/webitforexsite/images/main-img.png'));
        return $this->image;
    }

    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return ContentTranslation
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        if(empty($this->metaTitle) || is_null($this->metaTitle)){
            return $this->title;
        }
        return $this->metaTitle;
    }

    /**
     * Set metaKeywords
     *
     * @param string $metaKeywords
     * @return ContentTranslation
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * Get metaKeywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     * @return ContentTranslation
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set video
     *
     * @param string $video
     * @return ContentTranslation
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set brief
     *
     * @param string $brief
     *
     * @return ContentTranslation
     */
    public function setBrief($brief)
    {
        $this->brief = $brief;

        return $this;
    }

    /**
     * Get brief
     *
     * @return string
     */
    public function getBrief()
    {
        return $this->brief;
    }
    
    /**
     * Set upperContent
     *
     * @param string $upperContent
     *
     * @return ContentTranslation
     */
    public function setUpperContent($upperContent)
    {
        $this->upperContent = $upperContent;

        return $this;
    }

    /**
     * Get upperContent
     *
     * @return string
     */
    public function getUpperContent()
    {
        return $this->upperContent;
    }
  }
