<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexCoreBundle\Helper\MT4API;

use Webit\ForexCoreBundle\Form\BoCompliance as BoComplianceForms;

class ComplianceController extends Controller
{
    /**
     * action to approve application by compliance
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function ApproveApplicationAction($id, Request $request)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        $user = $object->getPortalUser();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($object->getCompStatus() == RealProfile::APPROVED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'User account already approved!');
            return $this->render($this->admin->getTemplate('show'), array('action' => 'show', 'object' => $object, 'elements' => $this->admin->getShow(), ));
        }

        if ($object->getCompIdStatus() != RealProfile::APPROVED ||
            $object->getCompPorStatus() != RealProfile::APPROVED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'To approve user account, you must first approve the id and por documents');
            return $this->editAction($object->getId());
        }

        //change tranding account from read only mode to active mode
        if ($object->getBoStatus() == RealProfile::APPROVED &&
            $login = $mt4_account->getLogin() &&
            $mt4_account->getPlatformType() == 'mt4') {
            $this->removeMT4AccountReadOnly();
        } else {
            $this->setUserAsBOPending($user);
            $this->sendCompApprovalEmail($user);
        }
        $this->setComplianceApproved($user);

        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent("Compliance: User Application approved", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Compliance:  Application is approved")));
        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'User account has been approved successfully');

        return $this->listAction();
    }

    /**
     * action to rejection application by compliance
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function RejectApplicationAction($id = null, Request $request)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $form = $this->createForm(new BoComplianceForms\RejectApplicationType());

        if ($request->getMethod() == "POST") {

            $form->submit($request);
            if ($form->isValid()) {
                $this->setAsComplianceReject($object);
                $this->sendRejectNotificationToBo($object);

                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Compliance: User Application Rejected", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Compliance: Application is rejected, reason: " . nl2br($form->get("reason")->getData()))));
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This application has been rejected successfully');

                return $this->listAction();
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/PortalUsers/reject.html.twig', array(
                    'action' => 'reject',
                    'object' => $object,
                    'form' => $form->createView(),
        ));
    }

    /**
     * action to edit, overriden to  redirect to show in this case (compliance cannot edit profile)
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function editAction($id = null, \Symfony\Component\HttpFoundation\Request $request = NULL)
    {
        if ($this->get('request')->get('btn_update_show')) {
            parent::editAction($id);
            return $this->redirect("show");
        }
       return parent::editAction($id);
    }

    /**
     * action to create custom log message by compliance
     * @param Request $request
     * @return Response
     */
    public function addNewLogAction(Request $request)
    {
        $id = $request->get('id');
        $object = $this->getDoctrine()->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')->find($id);
        $form = $this->createForm(new \Webit\UserLogBundle\Form\UserLogType());

        if($this->getRequest()->getMethod()=='POST'){
            $form = $form->bind($this->getRequest());

            if($form->isValid()){
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Log Added manually", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => $form->get('body')->getData() )) );
                $this->addFlash('sonata_flash_success', 'log added successfully');

                return new \Symfony\Component\HttpFoundation\RedirectResponse($this->admin->generateObjectUrl('show', $object));
            }
        }


        return $this->render("WebitForexCoreBundle::UserAdmin/add_new_log.html.twig", array('form'=>$form->createView(), 'object'=>$object, 'id'=>$id));
    }


    /**
     * set user backoffice status as "pending"
     * @param RealProfile $user
     */
    protected function setUserAsBOPending($object)
    {
        $real_user = $object->getRealProfile();
        $real_user->setBoStatus(\Webit\ForexCoreBundle\Entity\RealProfile::PENDING);

        $em = $this->getDoctrine()->getManager();
        $em->persist($real_user);
        $em->flush();
    }

    /**
     * send notification to backoffice upon compliance approval of user
     * @param RealProfile $user
     */
    protected function sendCompApprovalEmail($user)
    {
        $emails = $this->container->getParameter('emails');
        $email_params = array(
            '%full_name%' => ucfirst($user->getFirstName()) . ' ' . $user->getLastName(),
            '%username%' => $user->getUsername(),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('compliance_to_backoffice_approved_email', $email_params, $emails['bo'], $this->getRequest()->getLocale());
        $this->get('mailer')->send($message);
    }

    /**
     * set user compliance status as "approved"
     * @param RealProfile $user
     */
    protected function setComplianceApproved($object)
    {
        $user = $object->getRealProfile();
        $user->setCompStatus(\Webit\ForexCoreBundle\Entity\RealProfile::APPROVED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }

    /**
     * remove MT4 account readonly field, upon compliance activation
     * @param RealProfile $user
     */
    protected function removeMT4AccountReadOnly($user)
    {
        $api_info = $this->container->getParameter('mt4_api');
        $mt4_account = $this->getDoctrine()->getRepository('WebitForexCoreBundle:TradingAccount')->findOneBy(
            array('PortalUser' => $user->getId()), array('id' => 'ASC')
        );

        try{
            $api = new MT4API($em, $user->getId());
            $api->OpenConnection($api_info['real']['host'], $api_info['real']['port']);
            $api->removeAccountReadOnly($mt4_account->getLogin());

            return true;
        }catch(\Exception $ex){
            $logger->error('readonly mode for MT4 account#'.$form_data['login'].' cannot be removed, error: '.$ex->getMessage());
            return false;
        }
    }


    /**
     * send notification email to Backoffice upon rejection from compliance
     * @param RealProfile $object
     */
    protected function sendRejectNotificationToBo($object)
    {
        $user = $object->getPortalUser();
        $emails = $this->container->getParameter('emails');

        $email_params = array(
            '%full_name%' => ucfirst($user->getFirstName()) . ' ' . $user->getLastName(),
            '%username%' => $user->getUsername(),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('compliance_to_backoffice_reject_email', $email_params, $emails['bo'], $this->getRequest()->getLocale(), array($emails['compliance']=>'Compliance'));
        $this->get('mailer')->send($message);
    }

    /**
     * mark user application as rejected by compliance
     * @param RealProfile $object
     */
    protected function setAsComplianceReject($object)
    {
        $object->setCompStatus(\Webit\ForexCoreBundle\Entity\RealProfile::REJECT);
        if($object->getBoStatus() == RealProfile::FORWARDED){
            $object->setBoStatus(\Webit\ForexCoreBundle\Entity\RealProfile::PENDING);
        }
        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

}
