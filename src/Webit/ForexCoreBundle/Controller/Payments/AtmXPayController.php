<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\AtmXPayType;
use Webit\ForexCoreBundle\Form\Payments\AtmXPay2Type;
use Webit\ForexCoreBundle\Entity\Deposit;

#use GuzzleHttp\Client as GuzzleClient;

class AtmXPayController extends BaseController {

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
        $activeClass = '';

        $form_type = new AtmXPayType(array('portal_user' => $portal_user));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST') {
            $valid = $this->processForm($form, $deposit_obj, $portal_user);
            if ($valid === true) {
                $gateway_values = $this->getGatewayFormData($deposit_obj, $request);
                $submit_to_gateway = true;
            }
        }

        $template_params = array('trading_accounts' => true,
            'form' => $form->createView(),
            'submit_to_gateway' => $submit_to_gateway,
            'gateway_values' => isset($gateway_values) ? $gateway_values : array(),
            'activeClass' => $activeClass);

        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::MemberArea/Payments/AtmXPay/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/AtmXPay/index.html.twig', $template_params);
    }

    /**
     * This method is responsable for requesting Deposit by the user (non-logged)
     * @param Request $request
     * @return Response
     */
    public function nonloggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new AtmXPay2Type(array('portal_user' => null));
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
            return $this->render('AppBundle::MemberArea/Payments/AtmXPay/form_nonlogged.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/AtmXPay/nonlogged.html.twig', $template_params);
    }

    /**
     * action to handle showing success message after atmxpay completion
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function successAction(Request $request) {
        $order_id = $request->get('order_id');

        if ($order_id) {
            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($order_id);
            $this->markDepositAsComplete($deposit);
            $this->sendNotificationEmail($deposit);
        } else {
            $this->failureAction($request);
        }
    }

    /**
     * action to handle showing error message after atmxpay failure
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function failureAction(Request $request) {
        $this->redirect($this->generateUrl('system_message', array(
                    'message_type' => 'error',
                    'message' => 'ATMXPAY_ERROR_MSG',
        )));
    }

    /**
     * process the form of atmxpay payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'atmxpay');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of atmxpay
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
        $message = $message_helper->configureMail('atmxpay_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('atmxpay');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to atmxpay gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $gateway_params = $this->getMerchantInfo();
        $success_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('AtmXPaySuccess');
        $fail_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('AtmXPayFail');

        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields'] = array(
            'atmxpay_acc' => $gateway_params['atmxpay_acc'],
            'amount' => $deposit_obj->getAmount(),
            'atmxpay_currency' => 'atmxpayUSD',
            'atmxpay_comments' => 'ORBEX-Payment fund account',
            'atmxpay_merchant_ref' => $gateway_params['atmxpay_merchant_ref'],
            'order_id' => $deposit_obj->getId(),
            'atmxpay_success_url' => $success_url,
            'atmxpay_fail_url' => $fail_url
        );

        return $ret_data;
    }

}
