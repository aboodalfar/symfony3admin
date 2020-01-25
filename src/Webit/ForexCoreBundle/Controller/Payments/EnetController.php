<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Form\Payments\EnetType;
use Webit\ForexCoreBundle\Entity\Deposit;

class EnetController extends BaseController {
    
    CONST USD_KWD_RATE = 0.332;
    CONST EUR_KWD_RATE = 0.3673;
    
    /**
     * This method is responsible for requesting Deposit by the user  (non-logged)
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {

        $deposit_obj = new Deposit();
        $submit_to_gateway = false;

        $form_type = new EnetType(array('portal_user' => null));
        $form = $this->createForm($form_type, $deposit_obj);

        if ($this->getRequest()->getMethod() == 'POST' &&
                $this->processForm($form, $deposit_obj) === true) {
            $gateway_values = $this->getGatewayFormData($deposit_obj, $request);
            $submit_to_gateway = true;
        }

        $template_params = array(
            'trading_accounts' => true,
            'form' => $form->createView(),
            'submit_to_gateway' => $submit_to_gateway,
            'gateway_values' => isset($gateway_values) ? $gateway_values : array(),            
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::ForexSite/Forms/enet_deposit_form.html.twig', $template_params);
        }

        return $this->render('AppBundle::ForexSite/Forms/enet_deposit.html.twig', $template_params);
    }

    /**
     * action to display K-Net error page
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function failFundAction(Request $request) {
        $messageText = 'Failed deposit';
        $messageType = 'error';
               
        return $this->redirect($this->generateUrl('system_message',
                ['message_type' => $messageType,
                 'message' => $messageText,   
                ]));        
    }

    /**
     * action to handle process of verifying enet gateway
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function processAction(Request $request) {        
        $parameters = $request->request->all();
        $gatewayParams = $this->getParameter('enet');                
        
        $messageText = null;
        $messageType = null;
        
        $this->get('logger')->critical('enet process parameters: '. http_build_query($parameters));
               
        if(!isset($parameters['transaction_id']) || empty($parameters['transaction_id'])){
            
            //payment not found
            $messageText = 'Payment not found';
            $messageType = 'error';
        }else{
            $deposit = $this->getDoctrine()->getManager()
                            ->getRepository("\Webit\ForexCoreBundle\Entity\Deposit")->find($parameters['transaction_id']);
            
            $secKey = $parameters['sec_key'];
            unset($parameters['sec_key']);
            
            $orderedString = 'transaction_id='.$parameters['transaction_id']
                    .'&amount='.$parameters['amount']
                    .'&processpage='.$request->getScheme().'://'.$request->getHost().
                    $this->generateUrl('enetPaymentProcess')
                    .'&'.$gatewayParams['remote_password'];
            $this->get('logger')->critical('enet ordered string: '.$orderedString);
            $calculatedKey = md5($orderedString);
            if($secKey != $calculatedKey){
                
                $this->get('logger')->critical('enet keys invalid: security key='.$secKey.' calculated='.$calculatedKey);
                
                $messageText = 'failed deposit, keys not matched';
                $messageType = 'error';
            }elseif ($parameters['result'] == "CAPTURED") {                        
                $this->markDepositAsComplete($deposit, '');
                $this->sendNotificationEmail($deposit);
            
                $messageText = 'ENET_SUCCESS_MESSAGE';
                $messageType = 'success';
            }else{
                //deposit failed
               $messageText = 'failed deposit, '.$parameters['message'];
               $messageType = 'error';
            }
        }
        
        $host = 'http://1stfx.net'; //TODO: move to parameters.yml
        $url = $host. $this->generateUrl('system_message',
                ['message_type' => $messageType,
                 'message' => $messageText,   
                ]);
        
        //TODO: amend later
        return new Response('<script>window.location="'.$url.'";</script>');
    }

    /**
     * process the form of enet payment upon submission
     * @param \Symfony\Component\Form\Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit_obj
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return bool indicate successful submission or not
     */
    protected function processForm(\Symfony\Component\Form\Form $form, Deposit $deposit_obj, PortalUser $portal_user = null) {
        $form->submit($this->get('request'));
        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'enet');
            return true;
        } else {
            return false;
        }
    }

    /**
     * send notification email to the backoffice upon successful completion of enet
     * @param \Webit\ForexCoreBundle\Entity\Deposit $deposit     
     */
    protected function sendNotificationEmail(Deposit $deposit) {

        $email_params = array(
            '%full_name%' => $deposit->getFullName(),
            '%email_addr%' => $deposit->getEmail(),
            '%amount%' => $deposit->getAmountFormatted(),
            '%trading_account%' => $deposit->getTradingAccountNumber(),
            '%reference_number%' => $deposit->getPaymentGatewayReferenceId(),
        );
        
        $emails = $this->container->getParameter('emails');
        $toEmail = $emails['accountant'];
        
        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('enet_success', $email_params, $toEmail,'en');        
        $this->get('mailer')->send($message);
    }

    /**
     * getting form data to be used when submitting information to enet gateway
     * @param \Webit\ForexCoreBundle\Entity\Deposit $depositObj
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    protected function getGatewayFormData(Deposit $depositObj, Request $request) {
        
        $gatewayParams = $this->getParameter('enet');
        $amount = $depositObj->getAmount();
        
       /* if($depositObj->getPaymentType()=='C'){ //credit card
            $amount = $amount+ 2.5*$amount/100;
        }*/
        
        if($depositObj->getTradingCurrency()->getIsoCode()=='USD'){
            $amount = $amount*self::USD_KWD_RATE;
        }elseif($depositObj->getTradingCurrency()->getIsoCode()=='EUR'){
            $amount = $amount*self::EUR_KWD_RATE;
        }
        
        $arr = [
            'url' => $gatewayParams['url'], //TODO: change into params
            'fields' => [
                'merchant' => $gatewayParams['merchant_id'],
                'transaction_id' => $depositObj->getId(),
                'amount' => $amount,
                'processpage' => $request->getScheme().'://'.$request->getHost().
                    $this->generateUrl('enetPaymentProcess'),
                'sec_key' => md5($depositObj->getId().$amount.$gatewayParams['remote_password']),
                'op_post' => 'true',
                'md_flds' => 'transaction_id:amount:processpage',
                'user_email' => $depositObj->getEmail(),
                'currency' => 'KWD',
                'UDF1' => 'Trading Account number '.$depositObj->getTradingAccountNumber(),         
                'options' => 'op_ssl=false',                 
                'payment_type'  =>  $depositObj->getPaymentType(),
            ]
        ];

        return $arr;
    }

}
