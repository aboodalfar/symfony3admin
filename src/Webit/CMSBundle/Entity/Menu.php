<?php

namespace Webit\CMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webit\CMSBundle\Entity\MenuItem;

/**
 * Menu
 *
 * @ORM\Table(name="cms_menu")
 * @ORM\Entity(repositoryClass="Webit\CMSBundle\Repository\MenuRepository")
 */
class Menu
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @var ArrayCollection $menu_items
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="menu", cascade={"persist","remove"})
     * */
    private $menu_items;
    public function __construct()
    {
        $this->menu_items = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Menu
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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

    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * Add menu_items
     *
     * @param \Webit\CMSBundle\Entity\MenuItem $menuItems
     * @return Menu
     */
    public function addMenuItem(\Webit\CMSBundle\Entity\MenuItem $menuItems)
    {
        $this->menu_items[] = $menuItems;

        return $this;
    }

    /**
     * Remove menu_items
     *
     * @param \Webit\CMSBundle\Entity\MenuItem $menuItems
     */
    public function removeMenuItem(\Webit\CMSBundle\Entity\MenuItem $menuItems)
    {
        $this->menu_items->removeElement($menuItems);
    }

    /**
     * Get menu_items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuItems()
    {
        return $this->menu_items;
    }
}
