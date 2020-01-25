<?php namespace Webit\ForexCoreBundle\Entity;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Webit\ForexCoreBundle\Entity\Spread
 *
 * @ORM\Table(name="forex_spreads", 
 *        uniqueConstraints={
 *           @ORM\UniqueConstraint(name="symbol_idx", columns={"symbol"}) 
 *        })
 * @ORM\Entity()
 */

class Spread{
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $symbol
     *
     * @ORM\Column(name="symbol", type="string", length=255)
     */
    protected $symbol;
    
    /**
     * @var string $standardMinimumSpread
     *
     * @ORM\Column(name="standard_minimum_spread", type="string",length=255)
     */
    protected $standardMinimumSpread;
    
    
    /**
     * @var string $proMinimumSpread
     *
     * @ORM\Column(name="pro_minimum_spread", type="string",length=255)
     */
    protected $proMinimumSpread;
    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="`order`",type="integer",nullable=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $order;
    
    

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
     * Set symbol
     *
     * @param string $symbol
     *
     * @return Spread
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set standardMinimumSpread
     *
     * @param string $standardMinimumSpread
     *
     * @return Spread
     */
    public function setStandardMinimumSpread($standardMinimumSpread)
    {
        $this->standardMinimumSpread = $standardMinimumSpread;

        return $this;
    }

    /**
     * Get standardMinimumSpread
     *
     * @return string
     */
    public function getStandardMinimumSpread()
    {
        return $this->standardMinimumSpread;
    }

    /**
     * Set proMinimumSpread
     *
     * @param string $proMinimumSpread
     *
     * @return Spread
     */
    public function setProMinimumSpread($proMinimumSpread)
    {
        $this->proMinimumSpread = $proMinimumSpread;

        return $this;
    }

    /**
     * Get proMinimumSpread
     *
     * @return string
     */
    public function getProMinimumSpread()
    {
        return $this->proMinimumSpread;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Spread
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    public function __toString() {
        return (string) $this->getSymbol();
    }
}
