<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Webit\ForexCoreBundle\Entity\PortalDemoProfile
 *
 *
 * @ORM\Table(name="forex_demo_profile")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\DemoProfileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class DemoProfile {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255)
     * @Assert\Email()
     */
    protected $username;

    /**
     * @var string $active
     *
     * @ORM\Column(name="active", type="boolean",nullable=true)
     */
    protected $active;

    /**
     * @var string $md5_key
     *
     * @ORM\Column(name="md5_key", type="string", length=32,nullable=true)
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
     * @var datetime $expiration_date
     *
     * @ORM\Column(name="expiration_date", type="datetime",nullable=true)
     */
    protected $expiration_date;


    /**
     * @var string
     *
     * @ORM\Column(name="communication_language", type="string", length=8, options={"default" = "en"},nullable=true)
     */
    protected $communicationLanguage;
    
    private $login;




    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set expiration_date
     *
     * @param \DateTime $expirationDate
     * @return DemoProfile
     */
    public function setExpirationDate($expirationDate) {
        $this->expiration_date = $expirationDate;

        return $this;
    }

    /**
     * Get expiration_date
     *
     * @return \DateTime
     */
    public function getExpirationDate() {
        return $this->expiration_date;
    }

    public function __toString() {
        return $this->getUsername().($this->login?' (MT5#'.$this->login.')':'');
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

    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->first_name,
            $this->last_name,
            $this->active,
        ));
    }

    public function unserialize($serialized) {
        list(
                $this->id,
                $this->username,
                $this->first_name,
                $this->last_name,
                $this->active,
                ) = unserialize($serialized);
    }

    public function __sleep() {
        return array('id', 'username', 'first_name', 'last_name', 'active');
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
     * Get active
     *
     * @return string
     */
    public function getActiveLabel() {
        if (isset(self::$active_types[$this->active])) {
            return self::$active_types[$this->active];
        } else {
            return 'Not Active';
        }
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
     * Get country
     *
     * @return string
     */
    public function getCountryLabel() {
        return \Locale::getDisplayRegion('-'.$this->getCountry(),'en');
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

    
    public function getFullName() {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    function getCommunicationLanguage() {
        return $this->communicationLanguage;
    }

    function setCommunicationLanguage($communicationLanguage) {
        $this->communicationLanguage = $communicationLanguage;
        return $this;
    }
}
