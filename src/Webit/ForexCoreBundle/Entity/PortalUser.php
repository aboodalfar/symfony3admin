<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Webit\ForexCoreBundle\Entity\PortalUser
 *
 *
 * @ORM\Table(name="forex_portal_user")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\PoratlUserRepository")
 * @ORM\HasLifecycleCallbacks() 
 * @UniqueEntity(fields="username", message="this email is already registered.")
 */
class PortalUser implements AdvancedUserInterface, \Serializable {

    const Active = 1;
    const Not_active = 0;
    const DemoAccount = 0;
    const RealAccount = 1;
 

    public static $account_types = array(
        "Demo" => self::DemoAccount,
        "Real" => self::RealAccount
    );
    public static $account_types_keys = array(
        self::DemoAccount => "Demo",
        self::RealAccount => "Real",
    );
    public static $active_types = array(
        self::Active => 'Active',
        self::Not_active => 'Not Active',
    );
  

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255)
     * @Assert\Email()
     */
    protected $username;

    /**
     * @var string $username
     *
     * @ORM\Column(name="alternative_email", type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    protected $alternative_email;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255,nullable=true)
     */
    protected $salt;



    /**
     * @var string $active
     *
     * @ORM\Column(name="active", type="boolean",nullable=true)
     */
    protected $active;


    /**
     * @var string $md5_key
     *
     * @ORM\Column(name="md5_key", type="string", length=32)
     */
    protected $md5_key;

    /**
     * @var string $first_name
     *
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    protected $first_name;

  

    /**
     * @var string $last_name
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     */
    protected $last_name;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $created_at;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime",nullable=true)
     */
    protected $updated_at;

    /**
     * @var string $account_type
     *
     * @ORM\Column(name="account_type", type="string", length=25)
     */
    protected $account_type;

   

  

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255,nullable=true)
     */
    protected $country;

 
    /**
     * @var string $mobile_number
     *
     * @ORM\Column(name="mobile_number", type="string", length=25,nullable=true)
     */
    protected $mobile_number;

  

    /**
     * @ORM\OneToOne(targetEntity="RealProfile", mappedBy="PortalUser" , cascade={"persist","remove"})
     *
     */
    protected $RealProfile;

 

    /**
     * @ORM\OneToMany(targetEntity="RealProfileEdit", mappedBy="PortalUser")
     */
    protected $RealProfileEdit;

    /**
     * @ORM\OneToOne(targetEntity="RealApplicationTranslation", mappedBy="PortalUser" , cascade={"persist","remove"})
     *
     */
    protected $RealApplicationTranslation;

    /**
     * @ORM\OneToMany(targetEntity="TradingAccount", mappedBy="PortalUser")
     */
    protected $TradingAccounts;



    /**
     * @ORM\OneToMany(targetEntity="\Webit\UserLogBundle\Entity\UserLog", mappedBy="LoggedUser", cascade={"persist","remove"})
     */
    protected $UserLogs;


    /**
     * @ORM\OneToMany(targetEntity="DocumentsTranslation", mappedBy="PortalUser", orphanRemoval=true)
     */
    protected $DocumentsTranslations;

    /**
     * @var string
     *
     * @ORM\Column(name="reg_step", type="smallint" ,nullable=true)
     */
    protected $regStep;

 

    /**
     * @var string
     *
     * @ORM\Column(name="pdf_doc", type="string", length=255, nullable=true)
     */
    protected $pdfDoc;

    /**
     * @ORM\ManyToOne(targetEntity="\Webit\CMSBundle\Entity\SecurityQuestion", inversedBy="PortalUsers" , cascade={"persist","remove"})
     * @ORM\JoinColumn(name="security_question_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    protected $security_question;

    /**
     * @var string $security_question_answer
     *
     * @ORM\Column(name="security_question_answer", type="string", nullable=true)
     */
    protected $security_question_answer;

   

    /**
     * @var string
     *
     * @ORM\Column(name="communication_language", type="string", length=8, options={"default" = "en"})
     */
    protected $communicationLanguage;

    function getCommunicationLanguage() {
        return $this->communicationLanguage;
    }

    function setCommunicationLanguage($communicationLanguage) {
        $this->communicationLanguage = $communicationLanguage;
        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return SfGuardUser
     */
    public function setSalt($salt) {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt() {
        return $this->salt;
    }



    /**
     * Set regStep
     *
     * @param string $regStep
     * @return SfGuardUser
     */
    public function setRegStep($regStep) {
        $this->regStep = $regStep;

        return $this;
    }

    /**
     * Get regStep
     *
     * @return string 
     */
    public function getRegStep() {
        return (int)$this->regStep;
    }

 

    /**
     * Set md5_key
     */
    public function setMd5Key() {
        $this->md5_key = md5(mt_rand(10000, 99999) . time());
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if (!isset($this->created_at)) {
            $this->created_at = new \DateTime();
        }
        $this->setMd5Key();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->updated_at = new \DateTime();
    }

    public function serialize() {
        return serialize(array(
            $this->id,
       //     $this->password,
            $this->username,
            $this->first_name,
            $this->last_name,
            $this->account_type,
            $this->active,
        ));
    }

    public function unserialize($serialized) {
        list(
                $this->id,
                //$this->password,
                $this->username,
                $this->first_name,
                $this->last_name,
                $this->account_type,
                $this->active,
                ) = unserialize($serialized);
    }

    public function __sleep() {
        return array('id', 'username', 'first_name', 'last_name', 'account_type', 'active');
    }

    public function __toString() {
        return $this->username;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return PortalUser
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set alternative_email
     *
     * @param string $alternative_email
     * @return PortalUser
     */
    public function setAlternativeEmail($alternative_email) {
        $this->alternative_email = $alternative_email;

        return $this;
    }

    /**
     * Get alternative_email
     *
     * @return string
     */
    public function getAlternativeEmail() {
        return $this->alternative_email;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return PortalUser
     */
    public function setActive($active) {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Get md5_key
     *
     * @return string
     */
    public function getMd5Key() {
        return $this->md5_key;
    }

    /**
     * Get securityToken
     *
     * @return string
     */
    public function getSecurityToken() {
        return $this->md5_key;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return PortalUser
     */
    public function setFirstName($firstName) {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string
     */
    public function getFirstName() {
        return $this->first_name;
    }

   


    /**
     * Set last_name
     *
     * @param string $lastName
     * @return PortalUser
     */
    public function setLastName($lastName) {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string
     */
    public function getLastName() {
        return $this->last_name;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return PortalUser
     */
    public function setCreatedAt($createdAt) {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return PortalUser
     */
    public function setUpdatedAt($updatedAt) {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt() {
        return $this->updated_at;
    }

    /**
     * Set account_type
     *
     * @param string $accountType
     * @return PortalUser
     */
    public function setAccountType($accountType) {
        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get account_type
     *
     * @return string
     */
    public function getAccountType() {
        return $this->account_type;
    }

    /**
     * Get account_type label
     *
     * @return string
     */
    public function getAccountTypeLabel() {
        return self::$account_types_keys[$this->account_type];
    }

    /**
     * Get firstname+lastname
     *
     * @return \Webit\ForexCoreBundle\Entity\SubAccount
     */
    public function getFullName() {

        return $this->first_name .' ' . $this->last_name;
    }


  

    /**
     * Set country
     *
     * @param string $country
     * @return PortalUser
     */
    public function setCountry($country) {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set mobile_number
     *
     * @param string $mobileNumber
     * @return PortalUser
     */
    public function setMobileNumber($mobileNumber) {
        $this->mobile_number = $mobileNumber;

        return $this;
    }

  

    /**
     * Get mobile_number
     *
     * @return string
     */
    public function getMobileNumber() {
        return $this->mobile_number;
    }

  

  

    /**
     * Set RealProfile
     *
     * @param \Webit\ForexCoreBundle\Entity\RealProfile $realProfile
     * @return PortalUser
     */
    public function setRealProfileIndividual(\Webit\ForexCoreBundle\Entity\RealProfile $realProfile = null) {
        $this->RealProfile = $realProfile;

        return $this;
    }

    /**
     * Get RealProfile
     *
     * @return \Webit\ForexCoreBundle\Entity\RealProfile
     */
    public function getRealProfile() {
        return $this->RealProfile;
    }

  

  

    /**
     * Set pdfDoc
     *
     * @param string $pdfDoc
     * @return PortalUser
     */
    public function setPdfDoc($pdfDoc) {
        $this->pdfDoc = $pdfDoc;

        return $this;
    }

    /**
     * Get pdfDoc
     *
     * @return string
     */
    public function getPdfDoc() {
        return $this->pdfDoc;
    }

    

    /**
     * Constructor
     */
    public function __construct() {
        $this->TradingAccounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->UserLogs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add TradingAccounts
     *
     * @param \Webit\ForexCoreBundle\Entity\TradingAccount $tradingAccount
     * @return PortalUser
     */
    public function addTradingAccount(\Webit\ForexCoreBundle\Entity\TradingAccount $tradingAccount) {
        $this->TradingAccounts[] = $tradingAccount;

        return $this;
    }

    /**
     * Remove TradingAccount
     *
     * @param \Webit\ForexCoreBundle\Entity\TradingAccount $tradingAccount
     */
    public function removeTradingAccount(\Webit\ForexCoreBundle\Entity\TradingAccount $tradingAccount) {
        $this->TradingAccounts->removeElement($tradingAccount);
    }

    /**
     * Get TradingAccounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTradingAccounts() {
        return $this->TradingAccounts;
    }

    public function getFirstTradingAccount() {
        $ret = '';
        $i = 0;
        foreach ($this->getTradingAccounts() as $tradingAccount) {
            if ($i == 0) {
                $ret .= $tradingAccount->getLogin();
            } else {
                $ret .= ' - Sub Account: ' . $tradingAccount->getLogin();
            }

            $i++;
        }
        return $ret;
    }

    public function getRealTradingAccounts() {
        $ret = '';
        $i = 0;
        foreach ($this->getTradingAccounts() as $tradingAccount) {
            if ($tradingAccount->getIsDemo() == true) {
                continue;
            }

            if ($i == 0) {
                $ret .= $tradingAccount->getLogin();
            } else {
                $ret .= ', ' . $tradingAccount->getLogin();
            }

            $i++;
        }
        return $ret;
    }



    /**
     * Remove UserLogs
     *
     * @param \Webit\UserLogBundle\Entity\UserLog $userlogs
     */
    public function removeUserLogs(\Webit\UserLogBundle\Entity\UserLog $userlog) {
        $this->UserLogs->removeElement($userlog);
    }

    /**
     * Add UserLogs
     *
     * @param \Webit\UserLogBundle\Entity\UserLog $userLog
     * @return PortalUser
     */
    public function addUserLogs(\Webit\UserLogBundle\Entity\UserLog $userlog) {
        $this->UserLogs[] = $userlog;

        return $this;
    }

    /**
     * Get User Logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserLogs() {
        return $this->UserLogs;
    }


    /**
     * Set RealProfileEdit
     *
     * @param \Webit\ForexCoreBundle\Entity\RealProfileEdit $realProfileEdit
     * @return PortalUser
     */
    public function setRealProfileEdit(\Webit\ForexCoreBundle\Entity\RealProfileEdit $realProfileEdit = null) {
        $this->RealProfileEdit = $realProfileEdit;

        return $this;
    }

    /**
     * Get RealProfileEdit
     *
     * @return \Webit\ForexCoreBundle\Entity\RealProfileEdit
     */
    public function getRealProfileEdit() {
        return $this->RealProfileEdit;
    }

 

    /* used for login purposes */

   

    /**
     * @inheritDoc
     */
    public function getRoles() {
        return array('ROLE_TRADER');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() {
        
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonExpired() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isCredentialsNonExpired() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked() {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled() {
        return $this->active;
    }

    /* end login logic */

    public function getDocumentsTranslations() {
        return $this->DocumentsTranslations;
    }

    public function getRealApplicationTranslation() {
        return $this->RealApplicationTranslation;
    }

    public function setRealApplicationTranslation($obj) {
        $this->RealApplicationTranslation = $bj;
        return $this;
    }

    /**
     * Set SecurityQuestion
     *
     * @param \Webit\CMSBundle\Entity\SecurityQuestion $SecurityQuestion
     * @return PortalUser
     */
    public function setSecurityQuestion(\Webit\CMSBundle\Entity\SecurityQuestion $SecurityQuestion = null) {
        $this->security_question = $SecurityQuestion;

        return $this;
    }

    /**
     * Get SecurityQuestion
     *
     * @return \Webit\CMSBundle\Entity\SecurityQuestion
     */
    public function getSecurityQuestion() {
        return $this->security_question;
    }

    /**
     * Set security_question_answer
     *
     * @param string $security_question_answer
     * @return RealProfile
     */
    public function setSecurityQuestionAnswer($security_question_answer) {
        $this->security_question_answer = password_hash($security_question_answer, PASSWORD_BCRYPT);

        return $this;
    }

    /**
     * Get security_question_answer
     *
     * @return string
     */
    public function getSecurityQuestionAnswer() {
        return $this->security_question_answer;
    }

    public function getCountryLabel() {
         return \Locale::getDisplayRegion('-'.$this->getCountry(),'en');
    }

  
    public function drowTradingAccounts() {
        return $this->getTradingAccounts();
    }

    public function getFirstOneTradingAccount() {
        $ret = 0;
        $i = 0;
        foreach ($this->getTradingAccounts() as $tradingAccount) {
            if ($i == 0) {
                $ret = $tradingAccount->getLogin();
            }
            break;
        }
        return $ret;
    }
    
     public function getUserStep() {
      $user_step = (int)$this->regStep;
      $account_step = 5;
      if($account_step > $user_step){
          return $user_step;
      }else{
          return null;
      }
 
    }
    
    public function getPassword() {
        return ($this->getRealProfile() ? $this->getRealProfile()->getPassword() : '');
    }
    
}
