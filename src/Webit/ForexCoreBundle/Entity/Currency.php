<?php

namespace Webit\ForexCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Currency
 *
 * @ORM\Table(name="forex_currency")
 * @ORM\Entity(repositoryClass="Webit\ForexCoreBundle\Repository\CurrencyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Currency
{

   /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name ;
    /**
     *
     * @ORM\Column(name="pip", type="string", length=255, nullable=true)
     */
    private $pip ;
    /**
     *
     * @ORM\Column(name="margin", type="string", length=255, nullable=true)
     */
    private $margin ;


    /**
     * @ORM\Column(name="price", type="decimal", scale=8, precision=14)
     */
    private $price;

    /**
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     *
     * @ORM\Column(name="ask", type="decimal", scale=8)
     */
    private $ask;
    /**
     *
     * @ORM\Column(name="high", type="decimal", scale=8, precision=14 , nullable=true)
     */
    private $high;
    /**
     * @ORM\Column(name="low", type="decimal", scale=8, precision=14, nullable=true)
     */
    private $low;
    /**
     *
     * @ORM\Column(name="status", type="string", length=7)
     */
    private $status;
   /**
     *
     * @ORM\Column(name="change_time", type="datetime", nullable=true)
     */
    private $change_time;
    /**
     *
     * @ORM\Column(name="weight", type="integer", length=11, nullable=true)
     */
    private $weight;


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
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }


    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if(!isset($this->created_at))
            $this->created_at=new \DateTime();
    }
    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param decimal $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * Get price
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set ask
     *
     * @param decimal $ask
     */
    public function setAsk($ask)
    {
        $this->ask = $ask;
    }

    /**
     * Get ask
     *
     * @return decimal
     */
    public function getAsk()
    {
        return $this->ask;
    }

    /**
     * Set high
     *
     * @param decimal $high
     */
    public function setHigh($high)
    {
        $this->high = $high;
    }

    /**
     * Get high
     *
     * @return decimal
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Set low
     *
     * @param decimal $low
     */
    public function setLow($low)
    {
        $this->low = $low;
    }

    /**
     * Get low
     *
     * @return decimal
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set change_time
     *
     * @param datetime $changeTime
     */
    public function setChangeTime($changeTime)
    {
        $this->change_time = $changeTime;
    }

    /**
     * Get change_time
     *
     * @return datetime
     */
    public function getChangeTime()
    {
        return $this->change_time;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set pip
     *
     * @param string $pip
     */
    public function setPip($pip)
    {
        $this->pip = $pip;
    }

    /**
     * Get pip
     *
     * @return string
     */
    public function getPip()
    {
        return $this->pip;
    }

    /**
     * Set margin
     *
     * @param string $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    /**
     * Get margin
     *
     * @return string
     */
    public function getMargin()
    {
        return $this->margin;
    }
}
