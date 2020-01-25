<?php

namespace Webit\UserLogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BatchActivityLog
 *
 * @ORM\Table(name="webit_batch_activity_log")
 * @ORM\Entity(repositoryClass="Webit\UserLogBundle\Entity\BatchActivityLogRepository")
 */
class BatchActivityLog
{
    CONST OPERATION_TYPE_EXPORT = 1;
    //other operations types shall  be added later

    public static $operation_types = [
      self::OPERATION_TYPE_EXPORT => 'Export',  
    ];
    
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
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=255)
     */
    private $ipAddress;

    /**
     * @var integer
     *
     * @ORM\Column(name="operation_type", type="smallint")
     */
    private $operationType;

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=255)
     */
    private $module;

    /**
     * @var integer
     *
     * @ORM\Column(name="record_count", type="integer")
     */
    private $recordCount;

    /**
     * @var datetime
     *
     * @ORM\Column(name="time", type="datetime")
     */
    private $time;

    /**
     * @var \LoggedUser
     *
     * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $FosUser;    

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
     * @return BatchActivityLog
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
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return BatchActivityLog
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set operationType
     *
     * @param integer $operationType
     * @return BatchActivityLog
     */
    public function setOperationType($operationType)
    {
        $this->operationType = $operationType;

        return $this;
    }

    /**
     * Get operationType
     *
     * @return integer 
     */
    public function getOperationType()
    {
        return $this->operationType;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return BatchActivityLog
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string 
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set recordCount
     *
     * @param integer $recordCount
     * @return BatchActivityLog
     */
    public function setRecordCount($recordCount)
    {
        $this->recordCount = $recordCount;

        return $this;
    }

    /**
     * Get recordCount
     *
     * @return integer 
     */
    public function getRecordCount()
    {
        return $this->recordCount;
    }

    /**
     * Set time
     *
     * @param \Datetime $time
     * @return BatchActivityLog
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \Datetime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set FosUser
     *
     * @param \Application\Sonata\UserBundle\Entity\User $fosUser
     * @return BatchActivityLog
     */
    public function setFosUser(\Application\Sonata\UserBundle\Entity\User $fosUser = null)
    {
        $this->FosUser = $fosUser;

        return $this;
    }

    /**
     * Get FosUser
     *
     * @return \Application\Sonata\UserBundle\Entity\User 
     */
    public function getFosUser()
    {
        return $this->FosUser;
    }
    
    public function getActivityTypeLabel(){
        return isset(BatchActivityLog::$operation_types[$this->operationType])
                ?BatchActivityLog::$operation_types[$this->operationType]:'';
    }
}
