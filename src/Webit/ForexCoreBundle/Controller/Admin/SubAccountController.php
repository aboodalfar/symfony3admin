<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Webit\ForexCoreBundle\Entity\SubAccount;
//use Webit\ForexCoreBundle\Form\SubAccount;

/**
 *
 * @Route("/traders-cabinet/subaccount")
 */
class SubAccountController extends Controller
{
    /**
     * action to approve subaccount request by redirecting to "create sub account" action in portal user module
     *
     * @param int $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function approveSubaccountRequestAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $real_admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode('forex.admin.real');
        return $this->redirect($real_admin->generateObjectUrl('open_sub_account', $object->getPortalUser()->getRealProfile(), array('subaccount_request_id'=>$id)));
    }

    /**
     * reject the current sub account object
     *
     * @param int $id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function rejectSubaccountRequestAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->setAsRejected($object);
        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'subaccount request has been rejected');

        return $this->render($this->admin->getTemplate('show'), array('action' => 'show',
                                                                'object' => $object,
                                                                'elements' => $this->admin->getShow(),
                                                            ));
    }

    /**
     * set subaccount object as rejected
     * @param \Webit\ForexCoreBundle\Entity\SubAccount $object
     */
    protected function setAsRejected(SubAccount $object)
    {
       $object->setStatus(SubAccount::STATUS_REJECTED);

       $em = $this->getDoctrine()->getManager();
       $em->persist($object);
       $em->flush();

       $dispacher = $this->container->get('event_dispatcher');
       $dispacher->dispatch('user.log', new \Symfony\Component\EventDispatcher\GenericEvent("Sub account request rejected", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Sub account request #".$object->getId()." is rejected.")));
    }
}
