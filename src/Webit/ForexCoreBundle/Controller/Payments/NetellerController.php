<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\Deposit;
use \Symfony\Component\Form\Form;
use GuzzleHttp\Client as GuzzleClient;
use Webit\MailtemplateBundle\Helper\MailHelper;

class NetellerController extends BaseController {
    
    /**
     * process the form of neteller payment upon submission
     * @param Form $form
     * @param Deposit $deposit_obj
     * @param PortalUser $portal_user
     */
    protected function processForm(Form $form, Deposit $deposit_obj, PortalUser $portal_user = null, &$error_message = null) {

        if ($form->isValid()) {
            $this->saveObjectToDB($deposit_obj, $portal_user, 'Neteller');
            try {
                $ret_data = $this->sendNetellerRequest($form, $deposit_obj);
            } catch (\Exception $ex) {
                $ret_data['success'] = false;
                $ret_data['error_message'] = 'Cannot process request'; //TODO: handle in cleaner way
                $this->get('logger')->error('Neteller payment failure, error: ' . $ex->getMessage());
            }
            if (isset($ret_data->transaction->status) === true &&
                    $ret_data->transaction->status === 'accepted') {

                $this->NetellersendEmail($deposit_obj);
                $this->markDepositAsComplete($deposit_obj);

                return true;
            } else {
                $error_message = $ret_data['error_message'];
                return false;
            }
        }
    }

    /**
     * sending email to Back Office with Neteller transaction details
     * @param \Webit\ForexCoreBundle\Entity\Deposit $Deposit
     */
    protected function NetellersendEmail(\Webit\ForexCoreBundle\Entity\Deposit $Deposit) {
        $emails = $this->container->getParameter('emails');
        $email_params = array(
            '%full_name%' => $Deposit->getFullName(),
            '%email%' => $Deposit->getEmail(),
            '%trading_account%' => $Deposit->getTradingAccountNumber(),
            '%currency%' => $Deposit->getTradingCurrency(),
            '%amount%' => $Deposit->getAmount(),
        );

        $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('neteller_success', $email_params, $emails['back_office'], $deposit->getPortalUser()->getCommunicationLanguage());

        $this->get('mailer')->send($message);
    }

    /**
     * sending Neteller request using curl
     * @param Form $form
     * @param Deposit $order
     * @return string
     */
    protected function sendNetellerRequest(Form $form, $order) {
        
        //   return $this->generateTestResponse();        

        $rest_data = $this->preparePaymentGatewayData($form, $order);
        $response_arr = $this->sendRestRequest($rest_data['url'], $rest_data['post_data'], $rest_data['oauth']);

        return $response_arr;
    }

    /**
     * returning array of data and url to be used in the neteller REST API
     * @param Form $form
     * @param \Webit\ForexCoreBundle\Entity\Deposit $order
     * @return array
     */
    protected function preparePaymentGatewayData(Form $form, Deposit $order) {
        
        $final_data = array(
            "paymentMethod" => array(
                "type" => "neteller",
                "value" => $form['accountNumber']->getData(),
            ),
            "transaction" => array(
                "amount" => $form['amount']->getData() * 100,
                "currency" => $form['tradingCurrency']->getData()->__toString(),
                "merchantRefId" => date('YmdH') . $order->getId(),
            ),
            "verificationCode" => $form['secureidNumber']->getData()
        );

        $oauth_data = array();
        $neteller_params = $this->container->getParameter('neteller');
        if ($this->container->getParameter('kernel.environment') === 'dev') {
            $endpoint_url = $neteller_params['dev']['endpoint_url'];
            $oauth_data = array(
                'client_id' => $neteller_params['dev']['app_client_id'],
                'client_secret' => $neteller_params['dev']['app_client_secret'],
            );
        } else {
            $endpoint_url = $neteller_params['prod']['endpoint_url'];
            $oauth_data = array(
                'client_id' => $neteller_params['prod']['app_client_id'],
                'client_secret' => $neteller_params['prod']['app_client_secret'],
            );
        }

        return array('post_data' => $final_data, 'url' => $endpoint_url, 'oauth' => $oauth_data);
    }

    /**
     * send REST request to neteller end point and return xml response
     * @param string $endpoint_url
     * @param array $post_data
     * @param array|NULL $oauth_data
     * @throws \Exception
     * @return string
     */
    protected function sendRestRequest($endpoint_url, $post_data, $oauth_data = null) {
        $client = new GuzzleClient(['base_url' => $endpoint_url]);

        $encoded_credentials = base64_encode($oauth_data['client_id'] . ':' . $oauth_data['client_secret']);
        $request_token = $client->createRequest('post', $endpoint_url . 'v1/oauth2/token?grant_type=client_credentials');
        $request_token->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $request_token->setHeader('Cache-Control', 'no-cache');
        $request_token->setHeader('Authorization', 'Basic ' . $encoded_credentials);

        $response_token = $client->send($request_token);
        $oauth_params = $response_token->json();

        if (isset($oauth_params['error'])) {
            throw new \Exception('cannot authenticate request, reason: ' . $oauth_data['error']);
        }

        $headers = ['Content-Type' => 'application/json;charset=UTF-8',
            'Authorization' => $oauth_params['tokenType'] . ' ' . $oauth_params['accessToken'],
                //'Cache-Control' => 'no-cache',
        ];

        $request_payment = $client->createRequest('POST', $endpoint_url . 'v1/transferIn', array('body' => json_encode($post_data)));
        $request_payment->setHeaders($headers);
        $response_transfer = $client->send($request_payment);
        $ret_arr = json_decode($response_transfer->getBody());

        return $ret_arr;
    }

}
