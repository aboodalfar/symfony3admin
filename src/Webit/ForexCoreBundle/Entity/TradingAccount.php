<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TradingAccount
 *
 * @ORM\Table(name="forex_trading_account")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\TradingAccountRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TradingAccount
{ 

    const real_account = 1; //duplicate field and data to be invistigated to be removed
    const demo_account = 0; // isDemo field has this semantic meaning

    static $currency_list = array(
        'USD' => 'USD',
        'EURO' => 'EURO',
    );
    static $account_type_list = array(
        self::real_account => 'Real',
        self::demo_account => 'Demo'
    );
    static $list_leverage = array(
        100 => '1:100',
        200 => '1:200',
        300 => '1:300',
        400 => '1:400',
        500 => '1:500',
    );
    static $list_leverage_value = array(
        1 => '1:100',
        2 => '1:200',
        3 => '1:300',
        4 => '1:400',
        5 => '1:500',
    );
    
    static $list_leverage2 = array( //same as previous array, client S*** requests
         100 => '1:100',
        200 => '1:200',
        300 => '1:300',
        400 => '1:400',
        500 => '1:500',
        /*100 => 'STOP OUT 10%',
        200 => 'STOP OUT 10%',
        300 => 'STOP OUT 10%',
        400 => 'STOP OUT 100%',
        500 => 'STOP OUT 100%',
        */
    );
    
    static $list_leverage_value2 = array(
        1 => 'STOP OUT 10%',
        2 => 'STOP OUT 10%',
        3 => 'STOP OUT 10%',
        4 => 'STOP OUT 100%',
        5 => 'STOP OUT 100%',
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
     * @var integer
     *
     * @ORM\Column(name="login", type="bigint", nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="ro_password", type="string", length=255, nullable=true)
     */
    private $roPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="online_password", type="string", length=255, nullable=true)
     */
    private $onlinePassword;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_demo", type="boolean", nullable=true)
     */
    private $isDemo;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $user_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    private $groupId;

    /**
     * @var string $account_type
     *
     * @ORM\Column(name="account_type", type="string", length=255, options={"default" = 0})
     */
    protected $account_type;

    /**
     * @ORM\ManyToOne(targetEntity="PortalUser", inversedBy="TradingAccounts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $PortalUser;


    /**
     * @var string $leverage
     *
     * @ORM\Column(name="leverage", type="string", length=255,nullable=true)
     */
    protected $leverage;

    /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="string", length=255,nullable=true)
     */
    protected $comment;

    /**
     * @var string $agent_account
     *
     * @ORM\Column(name="agent_account", type="bigint", length=255,nullable=true)
     */
    protected $agent_account;
    
    /**
     * @var string $acc_exec_type
     *
     * @ORM\Column(name="account_execution_type", type="string", length=255, nullable=true)
     */
    protected $acc_exec_type;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="average_spread", type="float", nullable=true)
     */
    protected $avg_spread;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function copy()
    {
        $account = clone $this;
        $account->id = null;
        return $account;
    }

    /**
     * Set login
     *
     * @param integer $login
     * @return TradingAccount
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return integer
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set roPassword
     *
     * @param string $roPassword
     * @return TradingAccount
     */
    public function setRoPassword($roPassword)
    {
        $this->roPassword = $roPassword;

        return $this;
    }

    /**
     * Get roPassword
     *
     * @return string
     */
    public function getRoPassword()
    {
        return $this->roPassword;
    }
    
    /**
     * Set fontPassword
     *
     * @param string $fontPassword
     * @return TradingAccount
     */
    public function setFontPassword($fontPassword) {
        $this->fontPassword = $fontPassword;

        return $this;
    }

    /**
     * Get roPassword
     *
     * @return string
     */
    public function getFontPassword() {
        return $this->fontPassword;
    }
    

    /**
     * Set onlinePassword
     *
     * @param string $onlinePassword
     * @return TradingAccount
     */
    public function setOnlinePassword($onlinePassword)
    {
        $this->onlinePassword = $onlinePassword;

        return $this;
    }

    /**
     * Get onlinePassword
     *
     * @return string
     */
    public function getOnlinePassword() {
        return $this->onlinePassword;
    }

    /**
     * Set isDemo
     *
     * @param boolean $isDemo
     * @return TradingAccount
     */
    public function setIsDemo($isDemo) {
        $this->isDemo = $isDemo;

        return $this;
    }

    /**
     * Get isDemo
     *
     * @return boolean
     */
    public function getIsDemo()
    {
        return $this->isDemo;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return TradingAccount
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
     * Set PortalUsers
     *
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portalUser
     * @return TradingAccount
     */
    public function setPortalUser(\Webit\ForexCoreBundle\Entity\PortalUser $portalUser = null) {
        $this->PortalUser = $portalUser;

        return $this;
    }

    /**
     * Get PortalUsers
     *
     * @return \Webit\ForexCoreBundle\Entity\PortalUser
     */
    public function getPortalUser() {
        return $this->PortalUser;
    }


    /**
     * Set account_type
     *
     * @param string $accountType
     * @return TradingAccount
     */
    public function setAccountType($accountType) {
        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get account_type
     *
     * @return string
     */
    public function getAccountType() {
        return $this->account_type;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist() {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getAccountTypeLabel() {
        if (self::$account_type_list[$this->getAccountType()]) {
            return self::$account_type_list[$this->getAccountType()];
        } else {
            return 'N.A';
        }
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return TradingAccount
     */
    public function setUserId($userId) {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * Set leverage
     *
     * @param string $leverage
     * @return TradingAccount
     */
    public function setLeverage($leverage) {
        $this->leverage = $leverage;

        return $this;
    }

    /**
     * Get leverage
     *
     * @return string
     */
    public function getLeverage() {
        return $this->leverage;
    }
    
    public function getLeverageLabel2()
    {
        if(self::$list_leverage2[$this->getLeverage()]){
            return self::$list_leverage2[$this->getLeverage()];
        }else{
            return 'N.A';
        }
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return TradingAccount
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set agent_account
     *
     * @param string $agentAccount
     * @return TradingAccount
     */
    public function setAgentAccount($agentAccount) {
        $this->agent_account = $agentAccount;

        return $this;
    }

    /**
     * Get agent_account
     *
     * @return string
     */
    public function getAgentAccount() {
        return $this->agent_account;
    }
    
    /**
     * Set acc_exec_type
     *
     * @param string $acc_exec_type
     * @return TradingAccount
     */
    public function setAccExecType($acc_exec_type)
    {
        $this->acc_exec_type = $acc_exec_type;

        return $this;
    }

    /**
     * Get acc_exec_type
     *
     * @return string
     */
    public function getAccExecType()
    {
        return $this->acc_exec_type;
    }
    
    /**
     * Set avg_spread
     *
     * @param float $avg_spread
     * @return TradingAccount
     */
    public function setAvgSpread($avg_spread)
    {
        $this->avg_spread = $avg_spread;

        return $this;
    }

    /**
     * Get avg_spread
     *
     * @return float
     */
    public function getAvgSpread()
    {
        return $this->avg_spread;
    }
    
    public function getGroupId(){
        return $this->groupId;
    }

    
    public function __toString()
    {
        return (string)$this->getLogin();
    }
   
}
