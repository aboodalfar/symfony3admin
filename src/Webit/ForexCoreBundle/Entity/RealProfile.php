<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Webit\ForexCoreBundle\Entity\UserCustomDocuments;

/**
 * RealProfile
 *
 * @ORM\Table(name="forex_real_profile", 
 *        uniqueConstraints={
 *           @ORM\UniqueConstraint(name="user_id_idx", columns={"user_id"}) 
 *        })
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\RealProfileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class RealProfile {
        static $how_know_list = array(
        'Friend' => 'Friend',
        'Media Report' => 'Media Report',
        'Via Internet' => 'Via Internet',
        'TV' => 'TV',
        'Referred By Existing Client' => 'Referred By Existing Client'
    );
        
    const REJECT = 0;
    const APPROVED = 1;
    const PENDING = 2;
    const RECENT = 3;
    const FORWARDED = 4;

    static $ApplicationStatuses = array(                
        self::RECENT => 'Recent',
        self::PENDING => 'Pending',
        self::APPROVED => 'Approved',
        self::FORWARDED => 'Forwarded',
        self::REJECT => 'Rejected',        
    );
 
    static $leverage_list = array(
        '1:1'=>1,
        '1:50'=>50,
        '1:100'=>100,
        '1:200'=>200,
        '1:300'=>300,
        '1:400'=>400,
        '1:500'=>500,
    );
    
    static $currency_list = array(
        'USD'=>'USD'
    );
 
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $date_of_birth;
   

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string",length=20, nullable=true)
     */
    private $zipCode;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string",length=100, nullable=true)
     */
    private $city;
    
    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string",length=100, nullable=true)
     */
    private $state;
  

    /**
     * @var string
     *
     * @ORM\Column(name="full_address", type="text",nullable=true)
     */
    private $full_address;
    
    
    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255,nullable=true)
     */
    protected $password;
    
    /**
     * @ORM\OneToOne(targetEntity="PortalUser", inversedBy="RealProfile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $PortalUser;
    
    
    /**
     * @var smallint $leverage
     *
     * @ORM\Column(name="leverage", type="smallint", nullable=true)
     */
     private $leverage;
     
     
    /**
     * @var smallint $currency
     *
     * @ORM\Column(name="currency",type="string",length=25,nullable=true)
     */
     private $currency;

    /**
     * @var string $account_type
     *
     * @ORM\Column(name="account_type",type="string",length=25,nullable=true)
     */
    protected $accountType;
    
    
    /**
     * @var string $howKnow
     *
     * @ORM\Column(name="how_know", type="string", nullable=true,length=50)
     */
    private $howKnow;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="document_id", type="string", length=255, nullable=true)
     */
    private $documentId;

    /**
     * @var string
     *
     * @ORM\Column(name="document_id2", type="string", length=255, nullable=true)
     */
    private $documentId2;

    /**
     * @var string
     *
     * @ORM\Column(name="document_por", type="string", length=255, nullable=true)
     */
    private $documentPor;
    
    /**
     * @var string
     *
     * @ORM\Column(name="bo_id_status", type="smallint", nullable=true)
     */
    private $boIdStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="bo_por_status", type="smallint", nullable=true)
     */
    private $boPorStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="comp_id_status", type="smallint", nullable=true)
     */
    private $compIdStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="comp_por_status", type="smallint", nullable=true)
     */
    private $compPorStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="bo_status", type="smallint", nullable=true)
     */
    private $boStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="comp_status", type="smallint", nullable=true)
     */
    private $compStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="id_expiration_date", type="date", nullable=true)
     */
    private $idExpirationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="por_expiration_date", type="date", nullable=true)
     */
    private $porExpirationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="forwarded_compliance_date", type="datetime", nullable=true)
     */
    private $forwardedComplianceDate;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set date_of_birth
     *
     * @param \DateTime $dateOfBirth
     * @return RealProfile
     */
    public function setDateOfBirth($dateOfBirth) {
        $this->date_of_birth = $dateOfBirth;

        return $this;
    }

    /**
     * Get date_of_birth
     *
     * @return \DateTime
     */
    public function getDateOfBirth() {
        return $this->date_of_birth;
    }

 


    public function __toString() {
        return $this->getPortalUser() ? $this->getPortalUser()->getUsername() : 'N.A';
    }

    /**
     * Set fullAddress
     *
     * @param string $fullAddress
     *
     * @return RealProfile
     */
    public function setFullAddress($fullAddress)
    {
        $this->full_address = $fullAddress;

        return $this;
    }

    /**
     * Get fullAddress
     *
     * @return string
     */
    public function getFullAddress()
    {
        return $this->full_address;
    }

    /**
     * Set portalUser
     *
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portalUser
     *
     * @return RealProfile
     */
    public function setPortalUser(\Webit\ForexCoreBundle\Entity\PortalUser $portalUser = null)
    {
        $this->PortalUser = $portalUser;

        return $this;
    }

    /**
     * Get portalUser
     *
     * @return \Webit\ForexCoreBundle\Entity\PortalUser
     */
    public function getPortalUser()
    {
        return $this->PortalUser;
    }
      /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        $this->getPortalUser()->setAccountType(PortalUser::RealAccount);
    }


    /**
     * handle uploading all the files
     */
    public function uploadAll() {
        $por_copy = $this->getPassportCopy();

        if (!empty($por_copy)) {
            $file_name = $this->uploadOneFile($por_copy);
            $this->setPassportCopy($file_name);
        }
    }

    /**
     * upload single file to the uploads directory
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string generated uploaded file name
     */
    public function uploadOneFile($file) {
        if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {

            $upload_path = $this->getUploadDirAbsolute();
            if (!(file_exists($upload_path) && is_dir($upload_path))) {
                mkdir($upload_path);
            }
            $file_name =  md5(time().$this->getPortalUser()->getId().$file->getClientOriginalName()).'.'.
                    $file->getClientOriginalExtension();
            $file->move($upload_path, $file_name);
            
            return $file_name;
        } else {
            return $file; //return the same value without change, in case its not file
        }
    }
    

    protected function getUploadDirAbsolute() {
        return __DIR__ . '/../../../../web/uploads/userDocuments';
    }

    public function getUploadDirRelative() {
        return 'uploads/userDocuments/';
    }    

    public function getHowKnowLabel() {
        $ret = '';
        if (isset(self::$how_know_list[$this->howKnow])) {
            $ret = self::$how_know_list[$this->howKnow];
        }
        return $ret;
    }


    /**
     * Set otherClient
     *
     * @param string $otherClient
     *
     * @return RealProfile
     */


    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return RealProfile
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return RealProfile
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
     * Set state
     *
     * @param string $state
     *
     * @return RealProfile
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return RealProfile
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set leverage
     *
     * @param integer $leverage
     *
     * @return RealProfile
     */
    public function setLeverage($leverage)
    {
        $this->leverage = $leverage;

        return $this;
    }

    /**
     * Get leverage
     *
     * @return integer
     */
    public function getLeverage()
    {
        return $this->leverage;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return RealProfile
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set accountType
     *
     * @param string $accountType
     *
     * @return RealProfile
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * Get accountType
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * Set howKnow
     *
     * @param string $howKnow
     *
     * @return RealProfile
     */
    public function setHowKnow($howKnow)
    {
        $this->howKnow = $howKnow;

        return $this;
    }

    /**
     * Get howKnow
     *
     * @return string
     */
    public function getHowKnow()
    {
        return $this->howKnow;
    }
    


    /**
     * Set documentId
     *
     * @param string $documentId
     *
     * @return RealProfile
     */
    public function setDocumentId($documentId)
    {
        $documentId = $this->uploadOneFile($documentId);
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * Get documentId
     *
     * @return string
     */
    public function getDocumentId($fullPath=false)
    {
        if($fullPath){
            return $this->getUploadDirRelative().$this->documentId;;
        }
        return $this->documentId;
    }

    /**
     * Set documentId2
     *
     * @param string $documentId2
     *
     * @return RealProfile
     */
    public function setDocumentId2($documentId2)
    {
        $documentId2 = $this->uploadOneFile($documentId2);
        $this->documentId2 = $documentId2;

        return $this;
    }

    /**
     * Get documentId2
     *
     * @return string
     */
    public function getDocumentId2($fullPath = false)
    {
        if($fullPath){
            return $this->getUploadDirRelative().$this->documentId2;;
        }
        return $this->documentId2;
    }

    /**
     * Set documentPor
     *
     * @param string $documentPor
     *
     * @return RealProfile
     */
    public function setDocumentPor($documentPor)
    {
        $documentPor = $this->uploadOneFile($documentPor);
        $this->documentPor = $documentPor;

        return $this;
    }

    /**
     * Get documentPor
     *
     * @return string
     */
    public function getDocumentPor($fullPath = false)
    {
        if($fullPath){
            return $this->getUploadDirRelative().$this->documentPor;;
        }
        return $this->documentPor;
    }
    
    public function getDocumentIdPath() {
        if ($this->documentId) {
            return $this->getUploadDirRelative() . $this->documentId;
        }
    }

    public function getDocumentId2Path() {
        if ($this->documentId2) {
            return $this->getUploadDirRelative() . $this->documentId2;
        }
    }

    public function getDocumentPorPath() {
        if ($this->documentPor) {
            return $this->getUploadDirRelative() . $this->documentPor;
        }
    }
    
    public function getBoStatus() {
        return $this->boStatus;
    }
    

    /**
     * Set boIdStatus
     *
     * @param string $boIdStatus
     *
     * @return RealProfile
     */
    public function setBoIdStatus($boIdStatus)
    {
        $this->boIdStatus = $boIdStatus;

        return $this;
    }

    /**
     * Get boIdStatus
     *
     * @return string
     */
    public function getBoIdStatus()
    {
        return $this->boIdStatus;
    }

    /**
     * Set boPorStatus
     *
     * @param string $boPorStatus
     *
     * @return RealProfile
     */
    public function setBoPorStatus($boPorStatus)
    {
        $this->boPorStatus = $boPorStatus;

        return $this;
    }

    /**
     * Get boPorStatus
     *
     * @return string
     */
    public function getBoPorStatus()
    {
        return $this->boPorStatus;
    }

    /**
     * Set compIdStatus
     *
     * @param string $compIdStatus
     *
     * @return RealProfile
     */
    public function setCompIdStatus($compIdStatus)
    {
        $this->compIdStatus = $compIdStatus;

        return $this;
    }

    /**
     * Get compIdStatus
     *
     * @return string
     */
    public function getCompIdStatus()
    {
        return $this->compIdStatus;
    }

    /**
     * Set compPorStatus
     *
     * @param string $compPorStatus
     *
     * @return RealProfile
     */
    public function setCompPorStatus($compPorStatus)
    {
        $this->compPorStatus = $compPorStatus;

        return $this;
    }

    /**
     * Get compPorStatus
     *
     * @return string
     */
    public function getCompPorStatus()
    {
        return $this->compPorStatus;
    }

    /**
     * Set boStatus
     *
     * @param string $boStatus
     *
     * @return RealProfile
     */
    public function setBoStatus($boStatus)
    {
        $this->boStatus = $boStatus;

        return $this;
    }

    /**
     * Set compStatus
     *
     * @param string $compStatus
     *
     * @return RealProfile
     */
    public function setCompStatus($compStatus)
    {
        $this->compStatus = $compStatus;

        return $this;
    }

    /**
     * Get compStatus
     *
     * @return string
     */
    public function getCompStatus()
    {
        return $this->compStatus;
    }

    /**
     * Set idExpirationDate
     *
     * @param \DateTime $idExpirationDate
     *
     * @return RealProfile
     */
    public function setIdExpirationDate($idExpirationDate)
    {
        $this->idExpirationDate = $idExpirationDate;

        return $this;
    }

    /**
     * Get idExpirationDate
     *
     * @return \DateTime
     */
    public function getIdExpirationDate()
    {
        return $this->idExpirationDate;
    }

    /**
     * Set porExpirationDate
     *
     * @param \DateTime $porExpirationDate
     *
     * @return RealProfile
     */
    public function setPorExpirationDate($porExpirationDate)
    {
        $this->porExpirationDate = $porExpirationDate;

        return $this;
    }

    /**
     * Get porExpirationDate
     *
     * @return \DateTime
     */
    public function getPorExpirationDate()
    {
        return $this->porExpirationDate;
    }

    /**
     * Set forwardedComplianceDate
     *
     * @param \DateTime $forwardedComplianceDate
     *
     * @return RealProfile
     */
    public function setForwardedComplianceDate($forwardedComplianceDate)
    {
        $this->forwardedComplianceDate = $forwardedComplianceDate;

        return $this;
    }

    /**
     * Get forwardedComplianceDate
     *
     * @return \DateTime
     */
    public function getForwardedComplianceDate()
    {
        return $this->forwardedComplianceDate;
    }
}
