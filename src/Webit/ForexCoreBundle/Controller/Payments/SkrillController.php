<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\SkrillType;
use Webit\ForexCoreBundle\Form\Payments\Skrill2Type;
use Webit\ForexCoreBundle\Entity\Deposit;

#use GuzzleHttp\Client as GuzzleClient;

class SkrillController extends BaseController {

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
        $activeClass = 'skrill';

        $form_type = new SkrillType(array('portal_user' => $portal_user));
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
            return $this->render('AppBundle::MemberArea/Payments/Skrill/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/Skrill/index.html.twig', $template_params);
    }

    /**
     * This method is responsable for requesting Deposit by the user (non-logged)
     * @param Request $request
     * @return Response
     */
    public function nonloggedAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new Skrill2Type(array('portal_user' => null));
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
            return $this->render('AppBundle::MemberArea/Payments/Skrill/form_nonlogged.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/Skrill/nonlogged.html.twig', $template_params);
    }

    /**
     * action to handle display of page appears after skrill payment cancelled
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'error',
                            'message' => 'SKRILL_CANCEL_MSG',
        )));
    }

    /**
     * action to handle display of page appears after skrill payment completed
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completeFundAction(Request $request) {
        return $this->redirect($this->generateUrl('system_message', array(
                            'message_type' => 'success',
                            'message' => 'SKRILL_SUCCESS_MSG',
        )));
    }

    /**
     * action to handle process of verifying skrill gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statusAction(Request $request) {

        $merchant_info = $this->getMerchantInfo();
        $deposit_id = $request->get('deposit_id');
        $transaction_id = $request->get('transaction_id');

        $concatFields = $request->get('merchant_id')
                . $transaction_id
                . strtoupper(md5($merchant_info['secret']))
                . $request->get('mb_amount')
                . $request->get('mb_currency')
                . $request->get('status');

        $MBEmail = $merchant_info['pay_to_email'];

        // Ensure the signature is valid, the status code == 2,
        // and that the money is going to you
        if (strtoupper(md5($concatFields)) == $request->get('md5sig') && $request->get('status') == 2 && $request->get('pay_to_email') == $MBEmail) {

            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($deposit_id);
            $this->markDepositAsComplete($deposit, $transaction_id);
            $this->sendNotificationEmail($deposit);
        }
        return new Response();
    }

    /**
     * process the form of skrill payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'Skrill');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of skrill
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
        $message = $message_helper->configureMail('skrill_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting merchant information stored in services.yml
     * depending on the environment you are using (development/sandbox or production/live merchant)
     * @return array 
     */
    protected function getMerchantInfo() {
        $din_params = $this->container->getParameter('skrill');

        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $gateway_params = $din_params['dev'];
        } else {
            $gateway_params = $din_params['prod'];
        }

        return $gateway_params;
    }

    /**
     * getting form data to be used when submitting information to skrill gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $gateway_params = $this->getMerchantInfo();
        $return_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('SkrillComplete');
        $cancel_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('SkrillCancel');
        $status_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('SkrillStatus');

        $ret_data['url'] = $gateway_params['endpoint_url'];
        $ret_data['fields'] = array(
            'pay_to_email' => $gateway_params['pay_to_email'],
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
            'status_url' => $status_url,
            'status_url2' => $gateway_params['pay_to_email'],
            'language' => strtoupper($request->getLocale()),
            'logo_url' => $request->getScheme() . "://" . $request->getHost() . '/logo.png',
            'recipient_description' => $this->get('translator')->trans('SITE_NAME'),
            'merchant_fields' => 'email,full_name,account_number,deposit_id',
            'email' => $deposit_obj->getEmail(),
            'full_name' => $deposit_obj->getFullName(),
            'account_number' => $deposit_obj->getTradingAccountNumber(),
            'deposit_id' => $deposit_obj->getId(),
            'amount' => $deposit_obj->getAmount(),
            'currency' => $deposit_obj->getTradingCurrency()->getIsoCode(),
            'firstname' => $deposit_obj->getFirstName(),
            'lastname' => $deposit_obj->getLastName(),
            'pay_from_email' => $deposit_obj->getEmail(),
            'detail1_description' => 'Fund Account #' . $deposit_obj->getTradingAccountNumber(),
            'detail1_text' => 'Fund Account #' . $deposit_obj->getTradingAccountNumber(),
        );

        return $ret_data;
    }

}
