<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\CashUType;
use Webit\ForexCoreBundle\Form\Payments\CashU2Type;
use Webit\ForexCoreBundle\Entity\Deposit;

class CashUController extends BaseController {

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
        $activeClass = 'cashu';

        $form_type = new CashUType(array('portal_user' => $portal_user));
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
            return $this->render('AppBundle::MemberArea/Payments/CashU/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/CashU/index.html.twig', $template_params);
    }

    /**
     * This method is responsable for requesting Deposit by the user (non-logged)
     * @param Request $request
     * @return Response
     */
    public function nonloggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new CashU2Type(array('portal_user' => null));
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
            return $this->render('AppBundle::MemberArea/Payments/CashU/form_nonlogged.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/CashU/nonlogged.html.twig', $template_params);
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
            $this->saveObjectToDB($deposit_obj, $portal_user, 'cashu');
            return true;
        } else {
            return false;
        }
    }

    /**
     * action to handle display of page appears after CashU payment cancelled
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'error',
                            'message' => 'CACHU_CANCEL_MSG',
        )));
    }

    /**
     * action to handle display of page appears after CashU payment completed
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completeFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'success',
                            'message' => 'CACHU_SUCCESS_MSG',
        )));
    }

    /**
     * action to handle process of verifying CashU gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statusAction(Request $request) {
        $merchant_info = $this->getMerchantInfo();
        $order_id = $txt1 = $request->get('txt1');
        $transaction_number = $request->get('trn_id');
        $verificationString = $request->get('verificationString');

        $secret = $merchant_info['secret']; //secret keyword from gateway settings
        $merchant_id = $merchant_info['merchant_id'];

        if ($verificationString == sha1(strtolower($merchant_id . ':' . $transaction_number) . ':' . $secret)) {
            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($order_id);
            $this->markDepositAsComplete($deposit, $transaction_number);
            $this->sendNotificationEmail($deposit, $transaction_number);

            $returnUrl = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('CashUComplete');
            return $this->render('AppBundle::MemberArea/Payments/CashU/cashUDone.html.twig', array('returnUrl' => $returnUrl));
        } else {
            $response = $this->forward('WebitForexCoreBundle:Payments/CashU:cancelFund');
        }
        return $response;
    }

    /**
     * send notification email to the backoffice upon successfull completion of cashu
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit     
     */
    protected function sendNotificationEmail(Deposit $deposit, $transaction_number) {

        $email_params = array(
            '%amount%' => $deposit->getAmount(),
            '%trading_account%' => $deposit->getTradingAccountNumber(),
            '%transaction_number%' => $transaction_number
        );

        $emails = $this->container->getParameter('emails');

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('cashu_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('cashu');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to cashu gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $gateway_params = $this->getMerchantInfo();

        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields'] = array(
            'merchant_id' => $gateway_params['merchant_id'],
            'language' => strtoupper($request->getLocale()),
            'amount' => $deposit_obj->getAmount(),
            'currency' => $deposit_obj->getTradingCurrency()->getIsoCode(),
            'token' => md5(strtolower($gateway_params['merchant_id'] . ':' . $deposit_obj->getAmount() . ':' . $deposit_obj->getTradingCurrency()->getIsoCode()) . ':' . $gateway_params['secret']),
            'display_text' => 'ORBEX Fund account',
            'session_id' => md5(time() . $deposit_obj->getId()),
            'txt1' => $deposit_obj->getId(),
            'txt2' => '',
            'txt3' => '',
            'txt4' => '',
            'txt5' => '',
            'test_mode' => $gateway_params['test_mode'],
            'servicesName' => 'myOrbex_testing'
        );

        return $ret_data;
    }

}
