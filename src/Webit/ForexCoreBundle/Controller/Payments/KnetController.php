<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\KnetType;
use Webit\ForexCoreBundle\Entity\Deposit;
use Webit\ForexCoreBundle\Helper\e24PaymentPipe;

class KnetController extends BaseController {

    /**
     * This method is responsable for requesting Deposit by the user  (non-logged)
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new KnetType(array('portal_user' => null));
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
            return $this->render('AppBundle::MemberArea/Payments/Knet/form.html.twig', $template_params);
        }

        return $this->render('AppBundle::MemberArea/Payments/Knet/index.html.twig', $template_params);
    }

    /**
     * action to display K-Net error page
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function failFundAction(Request $request) {
        
    }

    /**
     * action to handle process of verifying knet gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function completeFundAction(Request $request) {

        $PaymentID = $request->get('paymentid');
        $presult = $request->get('result');
        $postdate = $request->get('postdate');
        $tranid = $request->get('tranid');
        $auth = $request->get('auth');
        $ref = $request->get('ref');
        $TrackID = $request->get('trackid');
        $udf1 = $request->get('udf1');
        $udf2 = $request->get('udf2');
        $udf3 = $request->get('udf3');
        $udf4 = $request->get('udf4');
        $udf5 = $request->get('udf5');

        if ($presult == "CAPTURED") {
            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($TrackID);
            $this->markDepositAsComplete($deposit, $ref);
            $this->sendNotificationEmail($deposit);
        }
        return new Response();
    }

    /**
     * process the form of knet payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'knet');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successfull completion of knet
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
        $message = $message_helper->configureMail('knet_success', $email_params, $emails['bo'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * getting form data to be used when submitting information to knet gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $deposit_obj, Request $request) {

        $response_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('KnetComplete');
        $error_url = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('KnetFail');

        $response_url = 'https://payment.orbex.com/en/deposit/knetResponse';
        $error_url = str_replace('http://', 'https://', $error_url);

        $directoryPath = $this->container->getParameter('kernel.root_dir') . '/Resources/knet-plugins/';

        $Pipe = new e24PaymentPipe();
        $Pipe->setAction(1);
        $Pipe->setCurrency($deposit_obj->getTradingCurrency()->__toString());
        $Pipe->setLanguage($request->getLocale() == 'ar' ? "ARA" : "ENG");
        $Pipe->setResponseURL($response_url);
        $Pipe->setErrorURL($error_url);
        $Pipe->setAmt($deposit_obj->getAmount());
        $Pipe->setResourcePath($directoryPath); //change the path where your resource file is
        $Pipe->setAlias("orbex"); //set your alias name here
        $Pipe->setTrackId($deposit_obj->getId());

        $Pipe->setUdf1($deposit_obj->getTradingAccountNumber());
        $Pipe->setUdf2($deposit_obj->getEmail());
        $Pipe->setUdf3($deposit_obj->getFirstName() . ' ' . $deposit_obj->getLastName());
        $Pipe->setUdf4(""); //set User defined value
        $Pipe->setUdf5(""); //set User defined value
        //
        //get results
        if ($Pipe->performPaymentInitialization() != $Pipe->SUCCESS) {
            return $this->redirect($this->generateUrl('KnetFail'));
        } else {
            $payID = $Pipe->getPaymentId();
            $payURL = $Pipe->getPaymentPage();
            header("Location: " . $payURL . "?PaymentID=" . $payID);
        }
    }

}
