<?php

/**
 * This BaseController hold utility functions that are shared between all
 * payment gateways
 * 
 * @author Zaid Rashwani<zaid@wewebit.com>
 */

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\Deposit;

class BaseController extends Controller {

    /**
     * This method returns the portal user according to User Id
     * @return PortalUser
     */
    protected function getPortalUser() {
        $user_id = $this->getUser()->getId();
        return $this->getDoctrine()->getRepository('WebitForexCoreBundle:PortalUser')->find($user_id);
    }

    /**
     * saving Deposit data request into database
     * @param Deposit $deposit_obj
     * @param PortalUser $user_obj
     * @param String $gateway_name
     */
    protected function saveObjectToDB($deposit_obj, $user_obj, $gateway_name) {
        $em = $this->getDoctrine()->getManager();
        $deposit_obj->setPortalUser($user_obj);
        $deposit_obj->setStatus(Deposit::STATUS_PENDING);
        $deposit_obj->setPaymentGateway(
                $em->getReference('\Webit\ForexCoreBundle\Entity\PaymentGateway', $gateway_name)); //ex. 'Fasapay'

        $em->persist($deposit_obj);
        $em->flush();
    }

    /**
     * set deposit status to approved upon confirmation from payment gateway side
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit
     * @param String|NULL $reference_num reference number returned from the gateway
     */
    protected function markDepositAsComplete(Deposit $deposit, $reference_num = null) {
        $em = $this->getDoctrine()->getManager();

        $deposit->setStatus(Deposit::STATUS_APPROVED);
        $deposit->setPaymentGatewayReferenceId($reference_num);
        $em->persist($deposit);
        $em->flush();

        if ($deposit->getPortalUser()) {
            $this->eventDispatcher("Deposit Completed",array('user'=>$deposit->getPortalUser(),
                'body'=>"Deposit #{$deposit->getId()} completed successfully via " . $deposit->getPaymentGateway()));
        }
    }
    /*
     save what user doing in log table in DB
     * eventDispatcher
     * @param string $title
     * @param array $info
     */
      protected function eventDispatcher($title, array $info)
    {
        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent($title, array('type' => 'log', 'user' => $info['portal_user'], 'body' =>$info['body'])));
    }

}
