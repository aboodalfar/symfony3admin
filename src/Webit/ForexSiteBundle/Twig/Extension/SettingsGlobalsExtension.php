<?php namespace Webit\ForexSiteBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Webit\ForexCoreBundle\Entity\Setting;
class SettingsGlobalsExtension extends \Twig_Extension
{

   protected $em;

   public function __construct(EntityManager $em)
   {
      $this->em = $em;
   }

   public function getGlobals()
   {
      return array (
              "settings" => $this->em->getRepository('WebitForexCoreBundle:Setting')
              ->getByKeys(array_keys(Setting::all),'array'),
      );
   }

   public function getName()
   {
      return "WebitForexSiteBundle:SettingsGlobalsExtension";
   }

}