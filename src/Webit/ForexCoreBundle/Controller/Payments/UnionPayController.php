<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\UnionPayType;
use Webit\ForexCoreBundle\Form\Payments\UnionPay2Type;
use Webit\ForexCoreBundle\Entity\Deposit;

class UnionPayController extends BaseController {

    /**
     * This method is responsable for requesting Deposit by the user
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {

        $portal_user = $this->getPortalUser();
        if ($portal_user->getAccountType() == PortalUser::DemoAccount) {
            return $this->redirect($this->generateUrl('UpgradetoReal'));
        }

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;
        $activeClass = 'union_pay';

        $form_type = new UnionPayType(array('portal_user' => $portal_user));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST') {
            $valid = $this->processForm($form, $deposit_obj, $portal_user);
            if ($valid === true) {
                $gateway_values = $this->getGatewayFormData($deposit_obj);
                $submit_to_gateway = true;
            }
        }

        $template_params = array('trading_accounts' => true,
            'form' => $form->createView(),
            'submit_to_gateway' => $submit_to_gateway,
            'gateway_values' => isset($gateway_values) ? $gateway_values : array(),
            'activeClass' => $activeClass);

        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::MemberArea/Payments/UnionPay/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/UnionPay/index.html.twig', $template_params);
    }

    /**
     * This method is responsable for requesting Deposit by the user (non-logged)
     * @param Request $request
     * @return Response
     */
    public function nonloggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new UnionPay2Type(array('portal_user' => null));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST' &&
                $this->processForm($form, $deposit_obj) === true) {

            $gateway_values = $this->getGatewayFormData($deposit_obj, $request);
            $submit_to_gateway = true;
        }

        $template_params = array('trading_accounts' => true,
            'form' => $form->createView(),
            'submit_to_gateway' => $submit_to_gateway,
            'gateway_values' => isset($gateway_values) ? $gateway_values : array());

        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::MemberArea/Payments/UnionPay/form_nonlogged.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/UnionPay/nonlogged.html.twig', $template_params);
    }

    /**
     * process the form of neteller payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $deposit_obj->setAddress($form->get('address')->getData());
            $this->saveObjectToDB($deposit_obj, $portal_user, 'austuni');
            $this->sendNotificationEmail($deposit_obj);
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of union_pay
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit     
     */
    protected function sendNotificationEmail(Deposit $deposit) {

        $email_params = array(
            '%full_name%' => $deposit->getFullName(),
            '%email_addr%' => $deposit->getEmail(),
            '%amount%' => $deposit->getAmount(),
            '%trading_account%' => $deposit->getTradingAccountNumber(),
            '%reference_number%' => $deposit->getPaymentGatewayReferenceId(),
        );

        $emails = $this->container->getParameter('emails');

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('union_pay_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('union_pay');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to union_pay gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj) {

        $gateway_params = $this->getMerchantInfo();

        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields'] = array(
            'merchantid' => $gateway_params['merchant_id'],
            'siteid' => $gateway_params['site_id'],
            'email' => $deposit_obj->getEmail(),
            'order_id' => $deposit_obj->getId(),
            'Amount' => $deposit_obj->getAmount(),
            'firstname' => $deposit_obj->getFirstName(),
            'lastname' => $deposit_obj->getLastName(),
            'address' => $deposit_obj->getAddress(),
            'phone' => $deposit_obj->getPhone(),
        );


        return $ret_data;
    }

}
