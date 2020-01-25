<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\JccType;
use Webit\ForexCoreBundle\Form\Payments\Jcc2Type;
use Webit\ForexCoreBundle\Entity\Deposit;
use Webit\ForexCoreBundle\Entity\TradingCurrency;

class JccController extends BaseController {

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
        $activeClass = 'jcc';

        $form_type = new JccType(array('portal_user' => $portal_user));
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
            return $this->render('AppBundle::MemberArea/Payments/Jcc/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/Jcc/index.html.twig', $template_params);
    }

    /**
     * action to handle display of page appears after jcc payment cancelled
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'error',
                            'message' => 'JCC_CANCEL_MSG',
        )));
    }

    /**
     * action to handle display of page appears after jcc payment completed
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completeFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'success',
                            'message' => 'JCC_SUCCESS_MSG',
        )));
    }

    /**
     * action to handle process of verifying jcc gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statusAction(Request $request) {

        $merchant_info = $this->getMerchantInfo();
        $jcc_data = array();
        $jcc_data['merchant_id'] = $merchant_id = $request->get('MerID');
        $jcc_data['acquirer_id'] = $acquirer_id = $request->get('acqID');
        $jcc_data['order_id'] = $order_id = $request->get('OrderID');
        $jcc_data['response_code'] = intval($request->get('ResponseCode'));
        $jcc_data['reason_code'] = intval($request->get('ReasonCode'));
        $jcc_data['reason_descr'] = $request->get('ReasonCodeDesc');
        $jcc_data['ref'] = $request->get('ReferenceNo');
        $jcc_data['padded_card_no'] = $request->get('PaddedCardNo');
        $jcc_data['signature'] = $request->get('ResponseSignature');

        if ($jcc_data['reposnse_code'] == 1 && $jcc_data['reason_code']) {
            $jcc_data['auth_no'] = $request->get('AuthCode');
        }


        $merchant_id = $merchant_info['merchant_id'];
        $acquirer_id = $merchant_info['acquirer_id'];
        $password = $merchant_info['password'];


        $to_encrypt = $password . $merchant_id . $acquirer_id . $order_id . $jcc_data['response_code'] . $jcc_data['reason_code'];

        $sha1sign = sha1($to_encrypt);
        $expectedBase64 = base64_encode(pack('H*', $sha1sign));

        $verified_signature = ($expectedBase64 == $jcc_data['signature']);

        if ($verified_signature && $jcc_data['response_code'] == 1 && $jcc_data['reason_code'] == 1) {
            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($order_id);
            $this->markDepositAsComplete($deposit, $jcc_data['ref']);
            $this->sendNotificationEmail($deposit);
        }
        return new Response();
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
            $this->saveObjectToDB($deposit_obj, $portal_user, 'Jcc');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of jcc
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
        $message = $message_helper->configureMail('jcc_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('Jcc');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to jcc gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $gateway_params = $this->getMerchantInfo();
        $return_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('JccComplete');

        $amount = number_format($deposit_obj->getAmount(), 2, '.', ',');
        $amount = str_replace(',', '', $amount);
        $amount = str_pad($amount, 13, '0', STR_PAD_LEFT);
        $formatted_purchase = substr($amount, 0, 10) . substr($amount, 11);
        $currency = $deposit_obj->getTradingCurrency();
        $order_id = $deposit_obj->getId();
        $to_encypt = $gateway_params['password'] . $gateway_params['merchant_id'] . $gateway_params['acquirer_id'] . $order_id . $formatted_purchase . $currency;

        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields'] = array(
            'Version' => $gateway_params['version'],
            'MerID' => $gateway_params['merchant_id'],
            'AcqID' => $gateway_params['acquirer_id'],
            'MerRespURL' => $return_url,
            'PurchaseAmt' => $formatted_purchase,
            'PurchaseCurrency' => $currency,
            'OrderID' => $order_id,
            'PurchaseCurrencyExponent' => 2,
            'CaptureFlag' => "A",
            'Signature' => base64_encode(pack("H*", sha1($to_encypt))),
            'SignatureMethod' => 'SHA1'
        );

        return $ret_data;
    }

}
