<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Webit\ForexCoreBundle\Entity\PortalUser
 *
 *
 * @ORM\Table(name="forex_real_profile_edit")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\RealProfileEditRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class RealProfileEdit {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=255,nullable=true)
     */
    protected $city;

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
     * @var string $personal_id
     *
     * @ORM\Column(name="personal_id", type="string", length=255,nullable=true)
     */
    protected $personal_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="postal_code", type="integer", nullable=true)
     */
    private $postal_code;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="smallint",nullable=true)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="full_address", type="text",nullable=true)
     */
    private $full_address;

    /**
     * @ORM\ManyToOne(targetEntity="PortalUser", inversedBy="RealProfileEdit")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="restrict")
     */
    protected $PortalUser;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    public static $status_arr = array(
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_REJECTED => 'rejected',
    );

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if (!isset($this->created_at)) {
            $this->created_at = new \DateTime();
        }
        $this->status = self::STATUS_PENDING; //it's pending on the first save
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return RealProfileEdit
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
     * @return RealProfileEdit
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
     * Set city
     *
     * @param string $city
     * @return RealProfileEdit
     */
    public function setCity($city) {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return RealProfileEdit
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
     * @return RealProfileEdit
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
     * Set personal_id
     *
     * @param string $personalId
     * @return RealProfileEdit
     */
    public function setPersonalId($personalId) {
        $this->personal_id = $personalId;

        return $this;
    }

    /**
     * Get personal_id
     *
     * @return string
     */
    public function getPersonalId() {
        return $this->personal_id;
    }

    /**
     * Set postalCode
     *
     * @param integer $postalCode
     * @return RealProfileEdit
     */
    public function setPostalCode($postalCode) {
        $this->postal_code = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return integer
     */
    public function getPostalCode() {
        return $this->postal_code;
    }

    /**
     * Set PortalUser
     *
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portalUser
     * @return RealProfileEdit
     */
    public function setPortalUser(\Webit\ForexCoreBundle\Entity\PortalUser $portalUser = null) {
        $this->PortalUser = $portalUser;

        return $this;
    }

    /**
     * Get PortalUser
     *
     * @return \Webit\ForexCoreBundle\Entity\PortalUser
     */
    public function getPortalUser() {
        return $this->PortalUser;
    }

    /**
     * Set status
     *
     * @param smallint $status
     * @return RealProfileEdit
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return smallint
     */
    public function getStatus() {
        return $this->status;
    }

    public function getStatusLabel() {
        return self::$status_arr[$this->status];
    }

    public function __toString() {
        return $this->getPortalUser() . ', edit#' . $this->getId();
    }

    /**
     * Set full_address
     *
     * @param string $fullAddress
     * @return RealProfile
     */
    public function setFullAddress($fullAddress) {
        $this->full_address = $fullAddress;

        return $this;
    }

    /**
     * Get full_address
     *
     * @return string
     */
    public function getFullAddress() {
        return $this->full_address;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->updated_at = new \DateTime();
    }

}
