<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * PaymentGateway
 * Code table used to retrieve available payment gateway types
 * 
 * @ORM\Table(name="forex_payment_gateway_type", 
 *        uniqueConstraints={
 *           @ORM\UniqueConstraint(name="gateway_type_idx", columns={"name"}) 
 *        })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class PaymentGateway {


    /**
     * Symbol is a unique textual representation of payment gateway
     * @var string
     * @ORM\Id
     * @ORM\Column(name="code_symbol", type="string", length=10)
     */
    private $codeSymbol;    
    
    /**
     * @var string
     * 
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255,nullable=true)
     */
    private $icon;
    

    /**
     * Set name
     *
     * @param string $name
     * @return PaymentGateway
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return PaymentGateway
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return PaymentGateway
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PaymentGateway
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set codeSymbol
     *
     * @param string $codeSymbol
     * @return PaymentGateway
     */
    public function setCodeSymbol($codeSymbol) {
        $this->codeSymbol = $codeSymbol;

        return $this;
    }

    /**
     * Get codeSymbol
     *
     * @return string 
     */
    public function getCodeSymbol() {
        return $this->codeSymbol;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate() {
        $this->updatedAt = new \DateTime();
    }

    public function __toString() {
        return (string)$this->getName();
    }

    public function __construct() {
        $this->isActive = false;
    }


    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return PaymentGateway
     */
    public function setIcon($icon)
    {
        $upload_path = __DIR__ . '/../../../../web/uploads/content/icons/';
        if (!(file_exists($upload_path) && is_dir($upload_path))) {
            mkdir($upload_path);
        }
        if ($icon) {
            $file_name = md5(time().$icon->getClientOriginalName()).'.'.
                    $icon->getClientOriginalExtension();
            $new_file = $icon->move($upload_path, $file_name);
            $this->icon = 'uploads/content/icons/'.$file_name;
        }

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

}
