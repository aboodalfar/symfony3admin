<?php

namespace Webit\CMSBundle\Entity;
use Webit\CMSBundle\Entity\Content as Content;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Category
 *
 * @ORM\Table(name="cms_category")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Category
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
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var ArrayCollection $contents
     * @ORM\OneToMany(targetEntity="Content", mappedBy="ContentCategory", cascade={"persist","remove"})
     * */
    private $contents;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    /**
     * adding new content to be associated with this category
     * @param \Webit\CMSBundle\Entity\Content $content
     */
    public function addContent(Content $content)
    {
        $content->setCategoryId($this->id);
        $this->contents[] = $content;
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
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     * @return Category
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Category
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if(empty($this->slug)){
            $this->slug = $this->generateSlug();
        }
        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }
    }

    public function generateSlug()
    {
        $text = strtolower($this->getTitle());
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        //$text = strtolower($text);
        //$text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    public function __toString()
    {
        return (string)$this->getTitle();
    }

    /**
     * Remove contents
     *
     * @param \Webit\CMSBundle\Entity\Content $contents
     */
    public function removeContent(\Webit\CMSBundle\Entity\Content $contents)
    {
        $this->contents->removeElement($contents);
    }

    /**
     * Get contents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContents()
    {
        return $this->contents;
    }
}
