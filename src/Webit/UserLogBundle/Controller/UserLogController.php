<?php

namespace Webit\UserLogBundle\Controller;


//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Webit\UserLogBundle\Entity\UserLog;

/**
 * UserLog controller.
 *
 * @Route("/userlog")
 */
class UserLogController extends Controller
{        
    /**
     * Lists all UserLog entities.
     *
     * @Route("/", name="userlog")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WebitUserLogBundle:UserLog')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a UserLog entity.
     *
     * @Route("/{id}", name="userlog_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id = NULL, \Symfony\Component\HttpFoundation\Request $request = NULL)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WebitUserLogBundle:UserLog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserLog entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }

}
