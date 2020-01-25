<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Locale\Locale;

/**
 * Webit\CMSBundle\Entity\Slider
 *
 * @ORM\Table(name="cms_slider")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\SliderRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Slider
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @Assert\NotNull(
     *   message="@-not-null-error-message-@"
     * )
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image;

    /**
     * @Assert\NotNull(
     *      message="@-not-null-error-message-@"
     * )
     * @ORM\Column(name="weight", type="integer", length=11)
     */
    private $weight;

    /**
     * @Assert\NotNull(
     *      message="@-not-null-error-message-@"
     * )
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     *
     * @ORM\Column(name="route", type="string", length=255,nullable=true)
     */
    private $route;

    /**
     * @Assert\NotNull(
     *      message="@-not-null-error-message-@"
     * )
     * @ORM\Column(name="lang", type="string", length=7)
     */
    private $lang;

    /**
     *
     * @ORM\Column(name="title", type="string", length=400)
     */
    private $title;

    /**
     *
     * @ORM\Column(name="description", type="string", length=400, nullable=true)
     */
    private $description;
    
    
    /**
     *
     * @ORM\Column(name="description2", type="text", nullable=true)
     */
    private $description2;

    /**
     *
     * @ORM\Column(name="page_id", type="integer", length=11,nullable=true)
     */
    private $page_id;

    /**
     * @ORM\ManyToOne(targetEntity="Content")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    protected $content;

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
     * Set image
     *
     * @param string $image
     */
    public function setImage($image)
    {
        $upload_path = __DIR__ . '/../../../../web/uploads/slider/';
        if (!(file_exists($upload_path) && is_dir($upload_path))) {
            mkdir($upload_path);
        }

        if ($image) {
            $file_name = rand(0, 1000) . $image->getClientOriginalName();
            $new_file = $image->move($upload_path, $file_name);
            $this->image = 'uploads/slider/' . $file_name;
        }
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
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

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set route
     *
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
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
     * Set lang
     *
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
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
     * Set page_id
     *
     * @param integer $pageId
     */
    public function setPageId($pageId)
    {
        $this->page_id = $pageId;
    }

    /**
     * Get page_id
     *
     * @return integer
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * Set page
     *
     * @param Webit\CMSBundle\Entity\Page $page
     */
    public function setContent(\Webit\CMSBundle\Entity\Content $Content)
    {
        $this->Content = $Content;
        $this->page_id = $Content->getId();
    }

    /**
     * Get page
     *
     * @return Webit\CMSBundle\Entity\Page
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Slider
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
     * Set description
     *
     * @param string $description
     * @return Slider
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

    public function __toString()
    {
        return (string)$this->getTitle();
    }

    /**
     * Set description2
     *
     * @param string $description2
     *
     * @return Slider
     */
    public function setDescription2($description2)
    {
        $this->description2 = $description2;

        return $this;
    }

    /**
     * Get description2
     *
     * @return string
     */
    public function getDescription2()
    {
        return $this->description2;
    }
}
