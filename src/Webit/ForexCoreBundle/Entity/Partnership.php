<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Partnership
 *
 * @ORM\Table(name="forex_partnership")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\PartnershipRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Partnership
{

    const IB = 1;
    const Whitelabel = 2;
    const RegionalRepresentatives = 3;
    const CapitalIntroduction = 4;
 
    public static $partnership_type = array(
        self::IB => "IB",
        self::Whitelabel => "Whitelabel",
        self::RegionalRepresentatives => "Affiliate",
    );
    
    public static $slug = array(
        self::IB => "introducing-broker",
        self::Whitelabel => "whitelabel",
        self::RegionalRepresentatives => "regional-representatives",
        self::CapitalIntroduction => "capital-introduction"
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $type
     *
     * @ORM\Column(name="p_type", type="string", length=25)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

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
     * @ORM\Column(name="phone_number", type="string", length=255)
     */
    private $phoneNumber;
    
    /**
     * @var string
     *
     * @ORM\Column(name="company",type="string",length=255,nullable=true)
     */
    private $company;
    
    /**
     * @var string
     *
     * @ORM\Column(name="number_clients",type="string",length=255,nullable=true)
     */
    private $numberClients;
    
    /**
     * @var string
     *
     * @ORM\Column(name="funds",type="string",length=255,nullable=true)
     */
    private $funds;
    
    /**
     * @var string
     *
     * @ORM\Column(name="website",type="string",length=255,nullable=true)
     */
    private $website;
    
    /**
     * @var string
     *
     * @ORM\Column(name="skype",type="string",length=255,nullable=true)
     */
    private $skype;



    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
     * Set type
     *
     * @param string $type
     *
     * @return Partnership
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Partnership
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Partnership
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
     *
     * @return Partnership
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return Partnership
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Partnership
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
    public function prePersist() {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }


    /**
     * Set company
     *
     * @param string $company
     *
     * @return Partnership
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set numberClients
     *
     * @param string $numberClients
     *
     * @return Partnership
     */
    public function setNumberClients($numberClients)
    {
        $this->numberClients = $numberClients;

        return $this;
    }

    /**
     * Get numberClients
     *
     * @return string
     */
    public function getNumberClients()
    {
        return $this->numberClients;
    }

    /**
     * Set funds
     *
     * @param string $funds
     *
     * @return Partnership
     */
    public function setFunds($funds)
    {
        $this->funds = $funds;

        return $this;
    }

    /**
     * Get funds
     *
     * @return string
     */
    public function getFunds()
    {
        return $this->funds;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Partnership
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set skype
     *
     * @param string $skype
     *
     * @return Partnership
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string
     */
    public function getSkype()
    {
        return $this->skype;
    }
    public function __toString() {
        return (string)$this->email;
    }
}
