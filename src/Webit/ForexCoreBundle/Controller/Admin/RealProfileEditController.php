<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
#use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Webit\ForexCoreBundle\Entity\RealProfileEdit;
use Webit\ForexCoreBundle\Form\RealProfileEditType;
use Webit\ForexCoreBundle\Form\BoCompliance as BoComplianceForms;

/**
 * RealProfileEdit controller.
 *
 * @Route("/realprofileedit")
 */
class RealProfileEditController extends Controller
{
    public function ApproveChangesAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->copyChangesToUser($object);
        $this->sendChangeApprovalEmail($object);

        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This changes has been approved successfully');

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    public function RejectChangesAction($id = null)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $request = $this->getRequest();
        $form = $this->createForm(new BoComplianceForms\RejectApplicationType());

        if ($request->getMethod() == "POST") {

            $form->submit($request);
            if ($form->isValid()) {

                $this->setEditAsRejected($object);
                $this->sendChangeRejectionEmail($object, $form);
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This changes has been rejected');

                return $this->render($this->admin->getTemplate('show'), array(
                            'action' => 'show',
                            'object' => $object,
                            'elements' => $this->admin->getShow(),
                ));
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/PortalUsers/rejectProfileChanges.html.twig', array(
                    'action' => 'reject',
                    'object' => $object,
                    'form' => $form->createView(),
        ));
    }

    /**
     * copy data from "edit" object to the user profile
     * @param RealProfileEdit $object
     */
    protected function copyChangesToUser($object)
    {
        $object->getPortalUser()->setCity($object->getCity());
        $object->getPortalUser()->setMobileNumber($object->getMobileNumber());
        $object->getPortalUser()->setCountry($object->getCountry());
        $object->getPortalUser()->getRealProfile()->setPostalCode($object->getPostalCode());
        $object->getPortalUser()->getRealProfile()->setPersonalId($object->getPersonalId());
        $object->setStatus(RealProfileEdit::STATUS_APPROVED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    protected function setEditAsRejected($object)
    {
        $object->setStatus(RealProfileEdit::STATUS_REJECTED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    /**
     * send email to the user to notify him of successful apply of data
     * @param RealProfileEdit
     */
    protected function sendChangeApprovalEmail($object)
    {
        $user = $object->getPortalUser();
        $email_params = array('%full_name%' => $user->getFirstName());
        $emails = $this->container->getParameter('emails');

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('approve_profile_changes', $email_params, $emails['bo'], $this->getRequest()->getLocale());
        $this->get('mailer')->send($message);
    }


    /**
     * send email to the user to notify him of rejection of data change request
     * @param RealProfileEdit profile edit option
     * @param \Symfony\Component\Form\Form $form reason of rejection
     */
    protected function sendChangeRejectionEmail($object, $form)
    {
        if($form->get('notify_client')->getData()){
            $user = $object->getPortalUser();
            $email_params = array('%full_name%' => $user->getFirstName(), '%reason%'=> $form->get('reason')->getData() );
            $emails = $this->container->getParameter('emails');

            $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
            $message = $message_helper->configureMail('reject_profile_changes', $email_params, $emails['bo'], $this->getRequest()->getLocale());
            $this->get('mailer')->send($message);
        }
    }


}
