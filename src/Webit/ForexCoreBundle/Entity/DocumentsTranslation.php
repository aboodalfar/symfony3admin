<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DocumentsTranslation
 *
 * @ORM\Table(name="forex_user_documents_translation")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\DocumentsTranslationRepository")
 * @UniqueEntity(fields={"userId", "documentType"}, message="document translation already exists")
 * @ORM\HasLifecycleCallbacks()
 */
class DocumentsTranslation implements \Serializable
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
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="document_type", type="smallint")
     */
    private $documentType;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="reference_id", type="string", length=255, nullable=true)
     */
    private $referenceId;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=255, nullable=true)
     */
    private $nationality;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1000, nullable=true)
     */
    private $address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_issue", type="date")
     */
    private $dateOfIssue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_expiry", type="date")
     */
    private $dateOfExpiry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;


    /**
     * @ORM\ManyToOne(targetEntity="PortalUser", inversedBy="DocumentsTranslations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="restrict")
     */
    protected $PortalUser;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;
     /**
     * Set date_of_birth
     *
     * @param \Date $dateOfBirth
     * 
     */
    public function setDateOfBirth($dateOfBirth) {
        if(!is_null($dateOfBirth))
        {
        $this->dateOfBirth = $dateOfBirth;
        }

        return $this;
    }

    /**
     * Get date_of_birth
     *
     * @return \Date
     */
    public function getDateOfBirth() {
        return $this->dateOfBirth;
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
     * Set userId
     *
     * @param integer $userId
     * @return DocumentsTranslation
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set documentType
     *
     * @param integer $documentType
     * @return DocumentsTranslation
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return integer 
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    public function getDocumentTypeLabel(){
        if(isset(self::$doc_type_arr[$this->documentType])){
            return self::$doc_type_arr[$this->documentType];
        }else{
            return 'N.A';
        }
    }

    /**
     * Set country
     *
     * @param string $country
     * @return DocumentsTranslation
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return DocumentsTranslation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set referenceId
     *
     * @param string $referenceId
     * @return DocumentsTranslation
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Get referenceId
     *
     * @return string 
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return DocumentsTranslation
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return DocumentsTranslation
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set nationality
     *
     * @param string $nationality
     * @return DocumentsTranslation
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Get nationality
     *
     * @return string 
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return DocumentsTranslation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set dateOfIssue
     *
     * @param \DateTime $dateOfIssue
     * @return DocumentsTranslation
     */
    public function setDateOfIssue($dateOfIssue)
    {
        $this->dateOfIssue = $dateOfIssue;

        return $this;
    }

    /**
     * Get dateOfIssue
     *
     * @return \DateTime 
     */
    public function getDateOfIssue()
    {
        return $this->dateOfIssue;
    }

    /**
     * Set dateOfExpiry
     *
     * @param \DateTime $dateOfExpiry
     * @return DocumentsTranslation
     */
    public function setDateOfExpiry($dateOfExpiry)
    {
        $this->dateOfExpiry = $dateOfExpiry;

        return $this;
    }

    /**
     * Get dateOfExpiry
     *
     * @return \DateTime 
     */
    public function getDateOfExpiry()
    {
        return $this->dateOfExpiry;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return DocumentsTranslation
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
     * @return DocumentsTranslation
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
    
    /**
     *
     * @param PortalUser $portalUser
     * @return \Webit\ForexCoreBundle\Entity\DocumentsTranslation
     */
    public function setPortalUser($portalUser)
    {
        $this->PortalUser = $portalUser;

        return $this;
    }

    /**
     * Get PortalUser
     *
     * @return PortalUser
     */
    public function getPortalUser()
    {
        return $this->PortalUser;
    }    
    
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!isset($this->createdAt)){
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
    
    
    /* constants */                
    CONST DOC_TYPE_PASSPORT = 1;        
    CONST DOC_TYPE_ID_CARD = 2;        
    CONST DOC_TYPE_DRIVING_LICENSE = 3;        
    CONST DOC_TYPE_POR = 4;        
    CONST DOC_TYPE_OTHER_ID = 5;
    
    public static $doc_type_arr = array(        
        self::DOC_TYPE_ID_CARD => 'ID Card',
        self::DOC_TYPE_POR => 'Proof of residence (POR)',
        self::DOC_TYPE_PASSPORT => 'Passport',
        self::DOC_TYPE_DRIVING_LICENSE => 'Driving License',
        self::DOC_TYPE_OTHER_ID => 'Other ID',
    );
    /* end constants */
    
   public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->documentType,
            $this->country,
            $this->city,
            $this->referenceId,
            $this->firstName,
            $this->lastName,
            $this->nationality,
            $this->address,
            $this->dateOfIssue,
            $this->dateOfExpiry,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->documentType,
            $this->country,
            $this->city,
            $this->referenceId,
            $this->firstName,
            $this->lastName,
            $this->nationality,
            $this->address,
            $this->dateOfIssue,
            $this->dateOfExpiry,
                ) = unserialize($serialized);
    }    
    
    public function toArray(){
        return array(
            'documentType'  => $this->documentType,
            'country'       => $this->country,
            'city'          => $this->city,
            'referenceId'   => $this->referenceId,
            'firstName'     => $this->firstName,
            'lastName'      => $this->lastName,
            'nationality'   => $this->nationality,
            'address'       => $this->address,
            'dateOfIssue'   => $this->dateOfIssue,
            'dateOfExpiry'  => $this->dateOfExpiry,
            'dateOfBirth'  => $this->dateOfBirth,
        );
    }

    public function __toString(){
        return $this->getDocumentTypeLabel();
    }
}
