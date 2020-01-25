<?php

namespace Webit\UserLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserLog
 *
 * @ORM\Table(name="webit_user_log")
 * @ORM\Entity(repositoryClass="Webit\UserLogBundle\Entity\UserLogRepository")
 */
class UserLog
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
     * @ORM\Column(name="subject", type="string", length=1000)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="string", length=1000)
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;



    /**
     * @var \LoggedUser
     *
     * @ORM\ManyToOne(targetEntity="\Webit\ForexCoreBundle\Entity\PortalUser", inversedBy="UserLogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $LoggedUser;

    /**
     * @var \LoggedUser
     *
     * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fos_user_id", referencedColumnName="id")
     * })
     */
    protected $FosUser;
    
    /**
     * @var \LoggedUser
     *
     * @ORM\ManyToOne(targetEntity="\Webit\ForexCoreBundle\Entity\DemoProfile", inversedBy="UserLogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demo_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $demoUser;

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
     * Set subject
     *
     * @param string $subject
     * @return UserLog
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return UserLog
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return UserLog
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

    public function getModifierUser()
    {
        return $this->modifierUser;
    }

    public function setModifierUser(/*\FOS\UserBundle\Entity\User*/ $user)
    {
        $this->modifierUser = $user;
        return $this;
    }

    public function getLoggedUser()
    {
        return $this->LoggedUser;
    }
    public function setLoggedUser(\Webit\ForexCoreBundle\Entity\PortalUser $user)
    {
        $this->LoggedUser = $user;
        return $this;
    }

    public function getFosUser(){
        return $this->FosUser;
    }

    public function setFosUser($FosUser = null){
        $this->FosUser = $FosUser;
        return $this;
    }

    public function getFullDescription()
    {
        return $this->getCreatedAt()->format('Y-m-d H:i').'\n\n'.$this->getSubject().'\n'.$this->getBody();
    }

    public function __toString()
    {
        return $this->getFullDescription();
    }

    /**
     * Set demoUser
     *
     * @param \Webit\ForexCoreBundle\Entity\DemoProfile $demoUser
     *
     * @return UserLog
     */
    public function setDemoUser(\Webit\ForexCoreBundle\Entity\DemoProfile $demoUser = null)
    {
        $this->demoUser = $demoUser;

        return $this;
    }

    /**
     * Get demoUser
     *
     * @return \Webit\ForexCoreBundle\Entity\DemoProfile
     */
    public function getDemoUser()
    {
        return $this->demoUser;
    }
}
