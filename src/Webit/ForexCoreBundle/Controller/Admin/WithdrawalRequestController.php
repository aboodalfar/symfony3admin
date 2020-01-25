<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Webit\ForexCoreBundle\Entity\WireTransfer;
use Webit\ForexCoreBundle\Entity\WithdrawalRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Webit\MailtemplateBundle\Helper\MailHelper;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * withdrawalRequest controller.
 *
 * @Route("/traders-cabinet/withdrawal-transaction")
 */
class WithdrawalRequestController extends Controller {

    public function approveWithdrawalAction() {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($object->getStatus() == WithdrawalRequest::STATUS_APPROVED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Withdrawal request already approved!');
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        } else {
            $this->approveRequest($object);
            $this->sendApprovalEmail($object);

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Withdrawal Request Approved", array(
                'type' => 'log',
                'user' => $object->getPortalUser(),
                'body' => 'Withdrawal Request Approved'
            )));
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This changes has been approved successfully');
        }

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    protected function approveRequest($object) {
        $object->setStatus(WithdrawalRequest::STATUS_APPROVED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    protected function sendApprovalEmail($object) {
        $user = $object->getPortalUser();
        $email_params = array(
            '%full_name%' => $user->getFirstName(),
            '%login%' => $object->getTradingAccount()->getLogin(),
            '%amount%' => $object->getAmount(),
            '%purpose%' => $object->getWithdrawalPurpose(),
            '%type%' => $object->getWithdrawalTypeLabel(),
        );
        $emails = $this->container->getParameter('emails');

        $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('approve_withdrawal_request', $email_params, $user->getUsername(), $user->getCommunicationLanguage()
        );
        $this->get('mailer')->send($message);
    }
    
    /**
     *  overwrite function
     */
    public function editAction($id = null, Request $request = null)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $old_object = $this->admin->getObject($id);
        $view = parent::editAction();
        $new_object = $this->admin->getObject($id);
        if ($new_object->getStatus() == WithdrawalRequest::STATUS_APPROVED && $old_object->getStatus() != WithdrawalRequest::STATUS_APPROVED) {
            $this->sendApprovalEmail($new_object);

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Withdrawal Request Approved", array(
                'type' => 'log',
                'user' => $new_object->getPortalUser(),
                'body' => 'Withdrawal Request Approved'
            )));
        }
        return $view;
    }

}
