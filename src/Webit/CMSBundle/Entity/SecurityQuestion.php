<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Webit\CMSBundle\Entity\TranslatableEntity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * SecurityQuestion
 *
 * @ORM\Table(name="cms_security_question")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class SecurityQuestion extends TranslatableEntity
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
     * @ORM\OneToMany(targetEntity="\Webit\ForexCoreBundle\Entity\PortalUser", mappedBy="security_question")
     */
    protected $PortalUsers;
    
    /**
     * @var ArrayCollection $Translations
     * @ORM\OneToMany(targetEntity="Webit\CMSBundle\Entity\SecurityQuestionTranslation", mappedBy="translation_parent", cascade={"persist","remove"}, orphanRemoval=true)
     * */
    private $Translations;

    
    public function __construct()
    {
        $this->Translations = new ArrayCollection();
        $this->PortalUsers = new ArrayCollection();
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
    
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    
    /**
     * add portaluser
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portalUser
     * @return \Webit\CMSBundle\Entity\SecurityQuestion
     */
    public function addPortalUser(\Webit\ForexCoreBundle\Entity\PortalUser $portalUser)
    {
        $this->PortalUsers[] = $portalUser;

        return $this;
    }

    /**
     *
     * @return ArrayCollection
     */
    public function getPortalUsers()
    {
        return $this->PortalUsers;
    }
    
    public function getTranslations()
    {
        return $this->Translations;
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
        );
    }

    public function getTranslationEntityName()
    {
        return '\Webit\CMSBundle\Entity\SecurityQuestionTranslation';
    }
    /* end of translatable entity methods */
    
    /**
     * Add Translations
     *
     * @param \Webit\CMSBundle\Entity\SecurityQuestionTranslation $translations
     * @return FaqQuestion
     */
    public function addTranslation(\Webit\CMSBundle\Entity\SecurityQuestionTranslation $translations)
    {
        $this->Translations[] = $translations;

        return $this;
    }

    /**
     * Remove Translations
     *
     * @param \Webit\CMSBundle\Entity\SecurityQuestionTranslation $translations
     */
    public function removeTranslation(\Webit\CMSBundle\Entity\SecurityQuestionTranslation $translations)
    {
        $this->Translations->removeElement($translations);
    }
    
    public function getQuestionText($lang = 'en'){
        $trans_vals = $this->getTranslationValues();
        foreach($trans_vals as $trans){
            if($trans['lang'] == $lang){
                return $trans['questionText'];
            }
        }
        
        return 'N.A';
    }
    
    public function __toString() {
        return $this->getQuestionText();
    }
}
