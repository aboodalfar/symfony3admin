<?php
namespace Webit\UserLogBundle\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine; 

/**
 * Custom login listener.
 */
class LogLoginListener
{
	/** @var \Symfony\Component\Security\Core\SecurityContext */
	private $securityContext;
	
	/** @var \Doctrine\ORM\EntityManager */
	private $em;
        
        private $doctrine;
	
	/**
	 * Constructor
	 * 
	 * @param SecurityContext $securityContext
	 * @param Doctrine        $doctrine
	 */
	public function __construct(SecurityContext $securityContext, Doctrine $doctrine)
	{
		$this->securityContext = $securityContext;
		$this->em              = $doctrine->getEntityManager();
                $this->doctrine        = $doctrine;
	}
	
	/**
	 * Do the magic.
	 * 
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
	{            
	
            $securityToken = $this->securityContext->getToken();
            $request_ip = $event->getRequest()->getClientIp();
            $user = $securityToken->getUser();
            if($user instanceof \Application\Sonata\UserBundle\Entity\User){
                $this->doctrine->getRepository('\Webit\UserLogBundle\Entity\LoginLog')
                     ->createLog($securityToken->getUser(), $request_ip);                    
            }
	}
}