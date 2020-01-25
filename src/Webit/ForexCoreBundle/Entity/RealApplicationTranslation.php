<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Webit\ForexCoreBundle\Entity\PortalUser;

/**
 * RealApplicationTranslation
 *
 * @ORM\Table(name="forex_real_application_translation")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class RealApplicationTranslation
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
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="second_name", type="string", length=255, nullable=true)
     */
    private $secondName;

    /**
     * @var string
     *
     * @ORM\Column(name="third_name", type="string", length=255, nullable=true)
     */
    private $thirdName;

    /**
     * @var string
     *
     * @ORM\Column(name="corporate_name", type="string", length=255, nullable=true)
     */
    private $corporateName;

    /**
     * @var string
     *
     * @ORM\Column(name="business_sector", type="string", length=255, nullable=true)
     */
    private $businessSector;

    /**
     * @var string
     *
     * @ORM\Column(name="person_to_contact", type="string", length=255, nullable=true)
     */
    private $personToContact;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="street_name", type="string", length=255, nullable=true)
     */
    private $streetName;


    /**
     * @var string
     *
     * @ORM\Column(name="building_name_number", type="string", length=255, nullable=true)
     */
    private $buildingNameNumber;


    /**
     * @var string
     *
     * @ORM\Column(name="state_province", type="string", length=255, nullable=true)
     */
    private $stateProvince;

    /**
     * @var string
     *
     * @ORM\Column(name="city_town", type="string", length=255, nullable=true)
     */
    private $cityTown;
    

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", length=255, nullable=true)
     */
    private $occupation;    

    /**
     * @var string
     *
     * @ORM\Column(name="name_of_attorney", type="string", length=255, nullable=true)
     */
    private $nameOfAttorney;

    /**
     * @var string
     *
     * @ORM\Column(name="attorney_location", type="string", length=255, nullable=true)
     */
    private $attorneyLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1000, nullable=true)
     */
    private $address;

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
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;


    /**
     * @ORM\OneToOne(targetEntity="PortalUser", inversedBy="RealApplicationTranslation" , cascade={"remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\Valid()
     */
    protected $PortalUser;

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
     * Set firstName
     *
     * @param string $firstName
     * @return RealApplicationTranslation
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
     * @return RealApplicationTranslation
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
     * Set secondName
     *
     * @param string $secondName
     * @return RealApplicationTranslation
     */
    public function setSecondName($secondName)
    {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * Get secondName
     *
     * @return string 
     */
    public function getSecondName()
    {
        return $this->secondName;
    }

    /**
     * Set thirdName
     *
     * @param string $thirdName
     * @return RealApplicationTranslation
     */
    public function setThirdName($thirdName)
    {
        $this->thirdName = $thirdName;

        return $this;
    }

    /**
     * Get thirdName
     *
     * @return string 
     */
    public function getThirdName()
    {
        return $this->thirdName;
    }

    /**
     * Set corporateName
     *
     * @param string $corporateName
     * @return RealApplicationTranslation
     */
    public function setCorporateName($corporateName)
    {
        $this->corporateName = $corporateName;

        return $this;
    }

    /**
     * Get corporateName
     *
     * @return string 
     */
    public function getCorporateName()
    {
        return $this->corporateName;
    }

    /**
     * Set businessSector
     *
     * @param string $businessSector
     * @return RealApplicationTranslation
     */
    public function setBusinessSector($businessSector)
    {
        $this->businessSector = $businessSector;

        return $this;
    }

    /**
     * Get businessSector
     *
     * @return string 
     */
    public function getBusinessSector()
    {
        return $this->businessSector;
    }

    /**
     * Set personToContact
     *
     * @param string $personToContact
     * @return RealApplicationTranslation
     */
    public function setPersonToContact($personToContact)
    {
        $this->personToContact = $personToContact;

        return $this;
    }

    /**
     * Get personToContact
     *
     * @return string 
     */
    public function getPersonToContact()
    {
        return $this->personToContact;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return RealApplicationTranslation
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
     * Set streetName
     *
     * @param string $streetName
     * @return RealApplicationTranslation
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * Get streetName
     *
     * @return string 
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return RealApplicationTranslation
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return RealApplicationTranslation
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
     * @return RealApplicationTranslation
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
     * Set userId
     *
     * @param integer $userId
     * @return RealApplicationTranslation
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

    public function getBuildingNameNumber(){
        return $this->buildingNameNumber;
    }

    public function setBuildingNameNumber($buildingNameNumber){
        $this->buildingNameNumber = $buildingNameNumber;
    }

    public function getPortalUser(){
        return $this->PortalUser;
    }

    public function setPortalUser(PortalUser $portal_usr){
        $this->PortalUser = $portal_usr;
    }

    public function getStateProvince(){
        return $this->stateProvince;
    }

    public function setStateProvince($stateProvince){
        $this->stateProvince = $stateProvince;
        return $this;
    }

    public function getCityTown(){
        return $this->cityTown;
    }

    public function setCityTown($cityTown){
        $this->cityTown = $cityTown;
        return $this;
    }

    public function getOccupation(){
        return $this->occupation;
    }

    public function setOccupation($occupation){
        $this->occupation = $occupation;
        return $this;
    }

    public function getNameOfAttorney(){
        return $this->nameOfAttorney;
    }

    public function setNameOfAttorney($nameOfAttorney){
        $this->nameOfAttorney = $nameOfAttorney;
        return $this;
    }

    public function getAttorneyLocation(){
        return $this->attorneyLocation;
    }

    public function setAttorneyLocation($attorneyLocation){
        $this->attorneyLocation = $attorneyLocation;
        return $this;
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

    public function getFullName() {
        if (!empty($this->firstName) && !empty($this->lastName)) {
            return trim($this->lastName.' '.$this->firstName); //names are reversed because this is the logic for chinese names
        }
        return '';
    }
}
