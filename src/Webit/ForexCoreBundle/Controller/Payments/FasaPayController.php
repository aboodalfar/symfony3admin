<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\Deposit;
use Webit\ForexCoreBundle\Form\Payments\FasaPayType;
use Webit\ForexCoreBundle\Form\Payments\FasaPay2Type;

class FasaPayController extends BaseController {

    /**
     * action to handle fasapay gateway deposit form from members area
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     */
    public function indexAction(Request $request) {
        $portal_user = $this->getPortalUser();
        if ($portal_user->getAccountType() == PortalUser::DemoAccount) {
            return $this->redirect($this->generateUrl('UpgradetoReal'));
        }

        $deposit_obj = new Deposit();
        $success = false;
        $activeClass = 'fasapay';

        $form_type = new FasaPayType(array('portal_user' => $portal_user));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST' &&
                $this->processForm($form, $deposit_obj, $portal_user)) {
            $gateway_values = $this->getGatewayFormData($deposit_obj, $form->get('fasapayAccountNumber')->getData());
            $success = true;
        }

        $template_vals = array('trading_accounts' => true,
            'form' => $form->createView(),
            'success' => $success,
            'gateway_values' => isset($gateway_values) ? $gateway_values : null,
            'activeClass' => $activeClass
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::MemberArea/Payments/Fasapay/form.html.twig', $template_vals);
        } else {
            return $this->render('AppBundle::MemberArea/Payments/Fasapay/index.html.twig', $template_vals);
        }
    }

    /**
     * action to handle fasapay gateway deposit form from members area
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     */
    public function nonLoggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $success = false;

        $form_type = new FasaPay2Type(array('portal_user' => null));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST' &&
                $this->processForm($form, $deposit_obj) === true) {
            $gateway_values = $this->getGatewayFormData($deposit_obj, $form->get('fasapayAccountNumber')->getData());
            $success = true;
        }


        $template_vals = array('trading_accounts' => true,
            'form' => $form->createView(),
            'success' => $success,
            'gateway_values' => isset($gateway_values) ? $gateway_values : null
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('WebitForexCoreBundle::Payments/Fasapay/form_nonlogged.html.twig', $template_vals);
        } else {
            return $this->render('WebitForexCoreBundle::Payments/Fasapay/nonlogged.html.twig', $template_vals);
        }
    }

    /**
     * action to be called from fasapay gateway to confirm payment (called internally from gateway side)
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     */
    public function confirmStatusAction(Request $request) {

        $deposit_id = $request->get('fp_merchant_ref');
        $deposit = $this->getDoctrine()->getRepository('\Webit\ForexCoreBundle\Entity\Deposit')
                ->find($deposit_id);

        $paidto = $request->get('fp_paidto');
        $paidby = $request->get('fp_paidby');
        $store = $request->get('fp_store');
        $amount = $request->get('fp_amnt');
        $batchnumber = $request->get('fp_batchnumber');
        $currency = $request->get('fp_currency');
        $hash = $request->get('fp_hash');

        $fasapay_params = $this->container->getParameter('fasapay');
        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $secret = $fasapay_params['dev']['merchant_secret_code'];
        } else {
            $secret = $fasapay_params['prod']['merchant_secret_code'];
        }

        $concat_hash = $paidto . ':' . $paidby . ':' . $store . ':' . $amount . ':' . $batchnumber . ':' . $currency . ':' . $secret;
        $concat_hash = hash('SHA256', $concat_hash);


        if ($hash == $concat_hash) { //validating the payment
            $this->sendFasaPayEmails($deposit);
            $this->markDepositAsComplete($deposit);
        } else {
            $this->get('logger')->error('error in validating fasapay payment, concat_hash=' . $concat_hash . ', hash=' . $hash);
        }

        return new Response('done...');
    }

    /**
     * action to handle showing success message after fasapay completion
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function successAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'success',
                            'message' => 'FASAPAY_SUCCESS_MSG',
        )));
    }

    /**
     * action to handle showing error message after fasapay failure
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function failureAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'error',
                            'message' => 'FASAPAY_ERROR_MSG',
        )));
    }

    /**
     * send confirmation emails to finance department upon completion of fasapay payment
     * @param Deposit $deposit
     */
    protected function sendFasaPayEmails(Deposit $deposit) {

        $email_params = array(
            '%full_name%' => $deposit->getFullName(),
            '%email_addr%' => $deposit->getEmail(),
            '%amount%' => $deposit->getAmount(),
            '%trading_account%' => $deposit->getTradingAccountNumber(),
        );

        $emails = $this->container->getParameter('emails');

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('fasapay_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * process the form of neteller payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser|NULL $portal_user
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'Fasapay');
            return true;
        } else {
            return false;
        }
    }

    /**
     * getting form data to be passed to fasapay iframe     
     * @param Deposit $deposit_obj
     * @param string $fasapay_acc_num
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, $fasapay_acc_num) {

        $fasapay_params = $this->container->getParameter('fasapay');
        $ret_data = array();

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $fasapay_params['dev'];
        } else {
            $gateway_params = $fasapay_params['prod'];
        }


        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields']['fp_acc'] = $gateway_params['FPID'];
        $ret_data['fields']['fp_store'] = $gateway_params['api_name'];

        $ret_data['fields']['fp_item'] = 'Deposit for Mt4#' . ($deposit_obj->getTradingAccountNumber());
        $ret_data['fields']['fp_amnt'] = $deposit_obj->getAmount();

        $ret_data['fields']['fp_currency'] = $deposit_obj->getTradingCurrency();
        $ret_data['fields']['fp_comments'] = 'Deposit for Mt4#' . ($deposit_obj->getTradingAccountNumber());
        $ret_data['fields']['fp_merchant_ref'] = $deposit_obj->getId();

        $ret_data['fields']['fp_acc_from'] = $fasapay_acc_num;

//        $request = $this->get('request');
//        $ret_data['fields']['fp_success_url'] = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('FasapaySuccess');
//        $ret_data['fields']['fp_success_method'] = 'POST';
//        $ret_data['fields']['fp_fail_url'] = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('FasapayFailure');
//        $ret_data['fields']['fp_fail_method'] = 'GET';
//        $ret_data['fields']['fp_status_url'] = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('FasapayConfirm');
//        $ret_data['fields']['fp_status_method'] = "POST";

        return $ret_data;
    }

}
