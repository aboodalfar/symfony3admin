<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\PayOnlineType;
use Webit\ForexCoreBundle\Form\Payments\PayOnline2Type;
use Webit\ForexCoreBundle\Entity\Deposit;

#use GuzzleHttp\Client as GuzzleClient;

class PayOnlineController extends BaseController {

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

        $form_type = new PayOnlineType(array('portal_user' => $portal_user));
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
            return $this->render('AppBundle::MemberArea/Payments/PayOnline/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/PayOnline/index.html.twig', $template_params);
    }

    /**
     * This method is responsable for requesting Deposit by the user (non-logged)
     * @param Request $request
     * @return Response
     */
    public function nonloggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new PayOnline2Type(array('portal_user' => null));
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
            return $this->render('AppBundle::MemberArea/Payments/PayOnline/form_nonlogged.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/PayOnline/nonlogged.html.twig', $template_params);
    }

    /**
     * action to handle display of page appears after payonline payment cancelled
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function failFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'error',
                            'message' => 'PAYONLINE_CANCEL_MSG',
        )));
    }

    /**
     * action to handle process of verifying payonline gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function completeFundAction(Request $request) {

        $merchant_info = $this->getMerchantInfo();
        $deposit_id = $request->get('OrderId');
        $response_hash = $request->get('SecurityKey');
        $securityKey = $merchant_info['securityKey'];

        $baseQuery = "DateTime=" . $request->get('DateTime') .
                "&TransactionID=" . $request->get('TransactionID') . "&OrderId=" . $request->get('OrderId') . "&Amount=" . $request->get('Amount') . "&Currency=" . $request->get('Currency');


        $queryWithSecurityKey2 = $baseQuery . "&PrivateSecurityKey=" . $securityKey;
        $org_hash = md5($queryWithSecurityKey2);

        // and that the money is going to you
        if ($response_hash == $org_hash) {

            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($deposit_id);
            $this->markDepositAsComplete($deposit);
            $this->sendNotificationEmail($deposit);
        }
        return new Response();
    }

    /**
     * process the form of payonline payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'payonline');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of payonline
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
        $message = $message_helper->configureMail('payonline_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('payonline');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to payonline gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $gateway_params = $this->getMerchantInfo();

        $return_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('PayOnlineComplete');
        $fail_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('PayOnlineFail');

        $merchantId = $gateway_params['merchant_id'];
        $orderId = $deposit_obj->getId();
        $amount = $deposit_obj->getAmount();
        $currency = $deposit_obj->getTradingCurrency();
        $securityKey = $gateway_params['securityKey'];

        $baseQuery = "MerchantId=" . $merchantId .
                "&OrderId=" . $orderId .
                "&Amount=" . number_format($amount, 2, '.', '') .
                "&Currency=" . $currency;

        $queryWithSecurityKey = $baseQuery . "&PrivateSecurityKey=" . $securityKey;

        $hash = md5($queryWithSecurityKey);

        $clientQuery = $baseQuery . "&SecurityKey=" . $hash;
        $clientQuery .= "&ReturnUrl=" . $return_url;
        $clientQuery .= "&FailUrl=" . $fail_url;

        $culture = $request->getLocale() == 'ru' ? 'ru' : 'en';
        $paymentFormAddress = "https://secure.payonlinesystem.com/$culture/payment/?" . $clientQuery;

        header("Location: " . $paymentFormAddress);
        exit();
    }

}
