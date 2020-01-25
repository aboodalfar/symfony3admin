<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserCustomDocuments
 *
 * @ORM\Table(name="user_custom_documents", indexes={@ORM\Index(name="user_custom_documents_fk2", columns={"user_id"})})
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class UserCustomDocuments
{
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
     * @ORM\Column(name="document_path", type="string", length=255, nullable=true)
     */
    private $documentPath;

    /**
     * @var string
     *
     * @ORM\Column(name="document_name", type="string", length=255, nullable=true)
     */
    private $documentName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="RealProfile", inversedBy="UserCustomDocuments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $RealProfile;

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
     * Set documentPath
     *
     * @param string $documentPath
     * @return UserCustomDocuments
     */
    public function setDocumentPath($documentPath)
    {
        $this->documentPath = $documentPath;
        return $this;
    }

    /**
     * Get documentPath
     *
     * @return string
     */
    public function getDocumentPath()
    {
        return $this->documentPath;
    }

    public function getDocumentPathFull()
    {
        return '/uploads/userDocuments/'.$this->documentPath;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return UserCustomDocuments
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
     * Set RealProfile
     *
     * @param \Webit\ForexCoreBundle\Entity\RealProfile $realUsers
     * @return UserCustomDocuments
     */
    public function setRealProfile(\Webit\ForexCoreBundle\Entity\RealProfile $realUsers = null)
    {
        $this->RealProfile = $realUsers;

        return $this;
    }

    /**
     * Get RealProfile
     *
     * @return \Webit\ForexCoreBundle\Entity\RealProfile
     */
    public function getRealProfile()
    {
        return $this->RealProfile;
    }

    public function getPortalUsers()
    {
        return $this->getRealProfile()->getPortalUsers();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!isset($this->createdAt))
            $this->createdAt = new \DateTime();
    }

    /**
     * Set documentName
     *
     * @param string $documentName
     * @return UserCustomDocuments
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;

        return $this;
    }

    /**
     * Get documentName
     *
     * @return string
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

}
