<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * FaqCategory
 *
 * @ORM\Table(name="cms_faq_category")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\FaqCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class FaqCategory extends TranslatableEntity
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
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="string", length=255, nullable=true)
     */
    private $weight;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var ArrayCollection $Translations
     * @ORM\OneToMany(targetEntity="FaqCategoryTranslation", mappedBy="translation_parent", cascade={"persist","remove"})
     * */
    private $Translations;

    /**
     * @var ArrayCollection $Questions
     * @ORM\OneToMany(targetEntity="FaqQuestion", mappedBy="Category")
     */
    private $Questions;

    public function __construct()
    {
        $this->Translations = new ArrayCollection();
        $this->Questions = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->Translations;
    }

    public function getQuestions()
    {
        return $this->Questions;
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FaqCategory
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
     * @param string $weight
     * @return FaqCategory
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FaqCategory
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
        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }
    }

    /** translatable entity methods **/
    public function addTranslationItem($trans_item)
    {
        $this->Translations[] = $trans_item;
    }

    public function getTranslatableColumns()
    {
        return array(
            'title'
        );
    }

    public function getTranslationEntityName()
    {
        return '\Webit\CMSBundle\Entity\FaqCategoryTranslation';
    }
    /** end translatable entity methods **/


    public function getTitle($lang='en')
    {
        $trans = $this->getTranslations();

        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t->getTitle();
            }
        }
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * Add Translations
     *
     * @param \Webit\CMSBundle\Entity\FaqCategoryTranslation $translations
     * @return FaqCategory
     */
    public function addTranslation(\Webit\CMSBundle\Entity\FaqCategoryTranslation $translations)
    {
        $this->Translations[] = $translations;

        return $this;
    }

    /**
     * Remove Translations
     *
     * @param \Webit\CMSBundle\Entity\FaqCategoryTranslation $translations
     */
    public function removeTranslation(\Webit\CMSBundle\Entity\FaqCategoryTranslation $translations)
    {
        $this->Translations->removeElement($translations);
    }

    /**
     * Add Questions
     *
     * @param \Webit\CMSBundle\Entity\FaqQuestion $questions
     * @return FaqCategory
     */
    public function addQuestion(\Webit\CMSBundle\Entity\FaqQuestion $questions)
    {
        $this->Questions[] = $questions;

        return $this;
    }

    /**
     * Remove Questions
     *
     * @param \Webit\CMSBundle\Entity\FaqQuestion $questions
     */
    public function removeQuestion(\Webit\CMSBundle\Entity\FaqQuestion $questions)
    {
        $this->Questions->removeElement($questions);
    }
}
