<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FaqQuestion
 *
 * @ORM\Table(name="cms_faq_question")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\FaqQuestionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class FaqQuestion extends TranslatableEntity
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
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var integer
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="smallint", nullable=true)
     */
    private $weight;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var ArrayCollection $Translations
     * @ORM\OneToMany(targetEntity="FaqQuestionTranslation", mappedBy="translation_parent", cascade={"persist","remove"}, orphanRemoval=true)
     * */
    private $Translations;

    /**
     * @var FaqCategory
     * @ORM\ManyToOne(targetEntity="FaqCategory", inversedBy="Questions")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $Category;

    public function __construct()
    {
        $this->Translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->Translations;
    }

    /**
     * getting related FaqCategory
     * @return FaqCategory
     */
    public function getCategory()
    {
        return $this->Category;
    }

    public function setCategory(FaqCategory $Category)
    {
        $this->Category = $Category;
        return $this;
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


    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FaqQuestion
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
     * Set weight
     *
     * @param integer $weight
     * @return FaqQuestion
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FaqQuestion
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FaqQuestion
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getQuestionText($lang='en')
    {
        $trans = $this->getTranslations();
        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t->getQuestionText();
            }
        }
    }

    /* translatable entity methods */
    public function addTranslationItem($trans_item)
    {
        $this->Translations[] = $trans_item;
    }

    public function getTranslatableColumns()
    {
        return array(
            'questionText',
            'answerText',
        );
    }

    public function getTranslationEntityName()
    {
        return '\Webit\CMSBundle\Entity\FaqQuestionTranslation';
    }
    /* end of translatable entity methods */


    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Add Translations
     *
     * @param \Webit\CMSBundle\Entity\FaqQuestionTranslation $translations
     * @return FaqQuestion
     */
    public function addTranslation(\Webit\CMSBundle\Entity\FaqQuestionTranslation $translations)
    {
        $this->Translations[] = $translations;

        return $this;
    }

    /**
     * Remove Translations
     *
     * @param \Webit\CMSBundle\Entity\FaqQuestionTranslation $translations
     */
    public function removeTranslation(\Webit\CMSBundle\Entity\FaqQuestionTranslation $translations)
    {
        $this->Translations->removeElement($translations);
    }
    
    public function __toString() {
        return (string)$this->getQuestionText();
    }
}
