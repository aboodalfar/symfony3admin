<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webit\ForexCoreBundle\Entity\Partnership;
use Webit\ForexCoreBundle\Entity\TradingAccount;
use \Webit\MailtemplateBundle\Helper\MailHelper;
use Webit\ForexCoreBundle\Form\BoCompliance as BoComplianceForms;
use Webit\ForexCoreBundle\Helper\MT4API;

/**
 * Partnership controller.
 *
 */
class PartnershipController extends Controller {

    public function ResendActivationAction($id, Request $request) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == Partnership::STATUS_FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "This application is locked because it's forwarded to the compliance.");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

        try {
            $this->sendActivationEmail($object, $request);
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Activation email was resent successfully');
        } catch (Exception $exc) {
            $this->get('logger')->critical('Resending activation email error, ' . $exc->getMessage());
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to send activation email');
        }

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    public function ApproveApplicationAction($id, Request $request) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $ret_restrictions = $this->processApprovalRestrictions($object);
        if ($ret_restrictions) {
            return $ret_restrictions;
        }


        $form = $this->createForm(new BoComplianceForms\TradingAccountsType());

        if ($this->get('request')->getMethod() == "POST") {
            $form->submit($this->get('request'));
            if ($form->isValid()) {
                if ($this->createTradingAccount($form, $object) === true) {
                    $this->approveUser($object);

                    //TODO: dispatch event to convert user
                    $dispacher = $this->container->get('event_dispatcher');
                    $dispacher->dispatch('crm.event', new GenericEvent("Convert Account", array('type' => 'convert_lead', 'site_object' => $object)));

                    $this->get('session')->getFlashBag()->add('sonata_flash_success', 'MT4 agent account created successfully and email send to the user');
                    return $this->render($this->admin->getTemplate('show'), array(
                                'action' => 'show',
                                'object' => $object,
                                'elements' => $this->admin->getShow(),
                    ));
                } else {
                    $mt4_error = $this->get('session')->getFlashBag()->get('mt4_error');
                    if (is_array($mt4_error)) {
                        $mt4_error = current($mt4_error);
                    }
                    $error_message = "Cannot create trading account due to following error: $mt4_error, please review form data or refer to technical support";

                    $this->get('session')->getFlashBag()->add('sonata_flash_error', $error_message);
                }
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/Partnership/createTradingAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
    }

    /**
     * handle some restrictions before initiating approval process of partner
     * @param object $object
     * @return Response|NULL 
     */
    protected function processApprovalRestrictions($object) {
        if ($object->getBoStatus() == Partnership::STATUS_APPROVED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'User account already approved!');
            return $this->render($this->admin->getTemplate('show'), array(
                        'action' => 'show',
                        'object' => $object,
                        'elements' => $this->admin->getShow(),
            ));
        }

        if ($object->getBoStatus() == Partnership::STATUS_FORWARDED) {

            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $object,
                        'elements' => $this->admin->getShow(),
            ));
        }

        if ($object->getBoIdStatus() != Partnership::STATUS_APPROVED ||
                $object->getBoPorStatus() != Partnership::STATUS_APPROVED) {

            $doc_status = Partnership::$status_arr;
            unset($doc_status[Partnership::STATUS_RECENT], $doc_status[Partnership::STATUS_FORWARDED]);
            $form = $this->createFormBuilder()
                    ->add('boIdStatus', 'choice', array('required' => true, 'label' => 'ID Status', 'choices' => $doc_status, 'empty_value' => "Choose status..."))
                    ->add('boPorStatus', 'choice', array('required' => true, 'label' => 'Por Status', 'choices' => $doc_status, 'empty_value' => "Choose status..."))
                    ->add('idExpirationDate', 'date', array('required' => true, 'label' => 'Id Expiration Date', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->add('porExpirationDate', 'date', array('required' => true, 'label' => 'Por Expiration Date', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->getForm();


            if ($this->get('request')->getMethod() == "POST") {
                $form->submit($this->get('request'));
                if ($form->isValid()) {

                    if ($form->get('boIdStatus')->getData() == Partnership::STATUS_APPROVED && $form->get('boPorStatus')->getData() == Partnership::STATUS_APPROVED) {

                        $object->setBoIdStatus($form->get('boIdStatus')->getData());
                        $object->setBoPorStatus($form->get('boPorStatus')->getData());
                        $object->setIdExpirationDate($form->get('idExpirationDate')->getData());
                        $object->setPorExpirationDate($form->get('porExpirationDate')->getData());

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($object);
                        $em->flush();


                        $this->get('session')->getFlashBag()->add('sonata_flash_success', "this application successfully approved");

                        return $this->redirect($this->admin->generateObjectUrl('approve_application', $object));
                    } else {
                        $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to approve the application');
                    }
                }
            }


            return $this->render('WebitForexCoreBundle::sonata/Partnership/approve.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
        }
    }

    /**
     * process action creation, set notice/error flags and return appropriate response
     *
     * @param \Symfony\Component\Form\Form $form
     * @param Partnership $object     
     * @return Response|RedirectResponse
     */
    protected function processAccountCreation($form, $object) {
        $trading_acc_created = $this->createTradingAccount($form, $object);
        if ($trading_acc_created === true) {

            //$dispacher = $this->container->get('event_dispatcher');
            //$dispacher->dispatch('user.log', new GenericEvent("Sub account created", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "mt4 sub-account number:<b>" . $form->get('login')->getData() . "</b> created successfully")));
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'trading account created successfully and email send to the user');
        } else {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "Cannot create trading account due to some error, please refer to technical support");
        }


        return $this->render($this->admin->getTemplate('show'), array('action' => 'show', 'object' => $object, 'elements' => $this->admin->getShow(),));
    }

    /**
     * create new trading account from form values
     * handle both manual creation and automatic creation via API
     * @param \Symfony\Component\Form\Form $form
     * @param Partnership $partner
     * @return boolean $ret indicates successfull creation or not
     */
    protected function createTradingAccount(\Symfony\Component\Form\Form $form, Partnership $partner) {
        $password = $form->get('online_password')->getData();
        if (!$form->get('manual_create')->getData()) { //automatic creation from API
            $ret = $this->handleAPICreation($form, $partner, $password);
        } else { //manual
            $ret = $this->handleManualCreation($form, $partner);
        }


        if ($ret === true) {
            $partner_pass = null; //partner area password
            $login = $form->get('login')->getData();

            $this->createPartnerAreaAccess($login, $partner_pass);
            $this->sendAccountCreationEmail($partner, $login, $password, $partner_pass);
        }

        return $ret;
    }

    /**
     * create record for partner area access using the created MT4 login
     * @param int $login
     * @param string $partner_pass
     */
    protected function createPartnerAreaAccess($login, &$partner_pass) {
        $partner_pass = \Webit\ForexCoreBundle\Helper\UtilsHelper::generateRandomPassword(9);

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $partner_obj = $this->admin->getObject($id);

        $partner_login_info = new \Webit\AgentAreaBundle\Entity\LoginInfo();
        $partner_login_info->setMainMt4Account($login);
        $partner_login_info->setPassword($partner_pass);
        $partner_login_info->setActive(true);
        $partner_login_info->setPartnerId($partner_obj->getId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($partner_login_info);
        $em->flush();
    }

    /**
     * handle automatic trading account creation via API
     * @param $form
     * @param Partnership $partner
     * @param String $password by reference to return value to email sending
     * @return boolean indicate successful operation
     */
    protected function handleAPICreation($form, $partner, &$password) {
        $api_info = $this->container->getParameter('mt4_api');
        $error_string = null;
        $em = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

        try {
            $api = new MT4API($em, $this->getUser()->getId());
            $api->OpenConnection($api_info['real']['host'], $api_info['real']['port']);

            $data = $this->getDataForAPI($form, $partner);
            $api->openAccount($data, true, $error_string);
            $password = $data['password']; //store it in the database
            $this->saveAPIAccountToDB($form, $partner, $data);

            //TODO: handle logging for partner user ...
            //$dispacher = $this->container->get('event_dispatcher');
            //$dispacher->dispatch('user.log', new GenericEvent("Trading account created", array('type' => 'log', 'user' => $user, 'body' => "Application is approved and creation of trading account #" . $form->get('login')->getData())));
            //$logger->info('MT4 account#' . $form->get('login')->getData() . ' created successfully via API');

            return true;
        } catch (\Exception $ex) {
            $logger->critical('API MT4 error [account#' . $form->get('login')->getData() . '], ' . $ex->getMessage());
            $this->get('session')->getFlashBag()->add('mt4_error', $error_string);
            return false;
        }
    }

    /**
     * saving API account to database
     * @param Form $form
     * @param Partnership $partner
     * @param array $data
     * @return TradingAccount
     */
    protected function saveAPIAccountToDB($form, $partner, $data) {
        $trading_acc = $form->getViewData();

        $trading_acc->setRoPassword($data['password']);
        $trading_acc->setOnlinePassword($data['password_investor']);
        $trading_acc->setPartnerUser($partner);
        $trading_acc->setAccountType(TradingAccount::real_account);

        $em = $this->getDoctrine()->getManager();
        $em->persist($trading_acc);
        $em->flush();
    }

    /**
     * configure data to be passed for web API
     * @param Form $form
     * @param Partnership $partner
     * @return array
     */
    protected function getDataForAPI($form, Partnership $partner) {
        $trading_acc = $form->getViewData();

        $arr = array(
            'login' => $trading_acc->getLogin(),
            'comment' => $trading_acc->getComment(),
            'agent_account' => $trading_acc->getAgentAccount(),
            'group' => $trading_acc->getCodeTradingGroup()->getName(),
            'name' => $partner->getFullName(),
            'leverage' => $trading_acc->getLeverage(),
            'email' => $partner->getEmail(),
            'city' => 'city',
            'country' => $partner->getCountryLabel(),
            'address' => $partner->getCountry(),
            'postal_code' => '0000',
            'phone_number' => $partner->getPhoneNumber(),
            'passport_id' => $partner->getId(),
        );

        return $arr;
    }

    /**
     * handle manual trading account creation; insert to DB, and log results
     * @param $form
     * @param Partnership $partner
     * @return boolean indicate successful operation
     */
    protected function handleManualCreation($form, Partnership $partner) {
        //$dispacher = $this->container->get('event_dispatcher');
        $logger = $this->get('logger');
        $trading_acc = $form->getViewData();

        try {
            $trading_acc->setPartnerUser($partner);
            $trading_acc->setAccountType(TradingAccount::real_account);
            $em = $this->getDoctrine()->getManager();
            $em->persist($trading_acc);
            $em->flush();

            //TODO: handle logging for partner user
            //$dispacher->dispatch('user.log', new GenericEvent("Trading account created", array('type' => 'log', 'user' => $user, 'body' => "Creation of trading account #" . $trading_acc->getLogin() . ' manually')));
            //$logger->info('MT4 account #' . $trading_acc->getLogin() . ' has been created manually');

            return true;
        } catch (\Exception $ex) {
            $logger->error('cannot create Mt4 account, due to this error: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * set user as 'backoffice approved' in the database
     * @param Partnership $object
     */
    protected function approveUser(Partnership $object) {
        $object->setBoStatus(Partnership::STATUS_APPROVED);
        if ($object->getCompStatus() != Partnership::STATUS_APPROVED) { //if compliance not approved it previously...
            $object->setCompStatus(Partnership::STATUS_PENDING);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        //$dispacher = $this->container->get('event_dispatcher');
        //$dispacher->dispatch('user.log', new GenericEvent("User Application approved", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Application is approved")));
    }

    /**
     * send email to client notifying him of trading account creation
     * @todo RE-IMPLEMENT
     * @param Partnership $partner
     * @param int $login
     * @param string $password
     * @param string $partner_pass
     */
    public function sendAccountCreationEmail(Partnership $partner, $login, $password, $partner_pass) {
        $email_params = array(
            '%full_name%' => $partner->getFullName(),
            '%mt4_login%' => $login,
            '%mt4_password%' => $password,
            '%partner_password%' => $partner_pass,
        );

        $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('mt4_creation_success_partner', $email_params, $partner->getEmail(), $this->get('request')->getLocale());

        $this->get('mailer')->send($message);
    }

    /**
     * action for rejecting application for client
     * @param $id integer
     * @return Response
     */
    public function RejectApplicationAction($id = null) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == Partnership::STATUS_FORWARDED) {

            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

        $request = $this->get('request');
        $form = $this->createForm(new BoComplianceForms\RejectApplicationType());

        if ($request->getMethod() == "POST") {

            $form->submit($request);
            if ($form->isValid()) {

                $this->setUserAsRejected($object);
                $this->sendRejectionEmail($object, $form);

                //$dispacher = $this->container->get('event_dispatcher');
                //$dispacher->dispatch('user.log', new GenericEvent("User Application Rejected", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Application is rejected, reason: " . nl2br($form->get("reason")->getData()))));

                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This application has been rejected successfully');

                return $this->render($this->admin->getTemplate('show'), array(
                            'action' => 'show',
                            'object' => $object,
                            'elements' => $this->admin->getShow(),
                ));
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/Partnership/reject.html.twig', array(
                    'action' => 'reject',
                    'object' => $object,
                    'form' => $form->createView(),
        ));
    }

    /**
     * set user status as "rejected" and save to DB
     * @param RealUserProfile $object
     */
    protected function setUserAsRejected($object) {
        $object->setBoStatus(Partnership::STATUS_REJECTED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    /**
     * send rejection email to the client
     * @param Partnership $object
     * @param Symfony\Component\Form\Form $form
     */
    protected function sendRejectionEmail(Partnership $object, $form) {
        if (!$form->get('notify_client')->getData()) {
            //notify client not checked, don't send any email
            return;
        }

        $email_params = array(
            '%full_name%' => $object->getFullName(),
            '%reason%' => nl2br($form->get("reason")->getData()),
        );

        $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('reject_application_partner', $email_params, $object->getEmail(), $this->get('request')->getLocale());
        $this->get('mailer')->send($message);
    }

    /**
     * forward to compliance action
     * @return Response
     */
    public function forwardApplicationAction() {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->setPartnerAsForwarded($object);
        $this->sendForwardEmailToCompliance($object);

        //$dispacher = $this->container->get('event_dispatcher');
        //$dispacher->dispatch('user.log', new GenericEvent("New user log", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => 'The application is forwarded to compliance')));
        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This application has been Forwarded to compliance successfully');

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    /**
     * mark user as "forwarded to compliance" in the databases
     * @param RealProfile $object
     */
    protected function setPartnerAsForwarded($object) {
        $object->setBoStatus(Partnership::STATUS_FORWARDED);
        $object->setCompStatus(Partnership::STATUS_PENDING);
        $object->setForwardedComplianceDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    /**
     * send notification email to complaince upon forwarding new application to him
     * @param Partnership $object
     */
    protected function sendForwardEmailToCompliance($object) {

        $emails = $this->container->getParameter('emails');
        $email_params = array(
            '%full_name%' => $object->getFullName(),
            '%email%' => $object->getEmail(),
        );

        $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('forwared_to_compliace_partner', $email_params, $emails['compliance'], $this->get('request')->getLocale());
        $this->get('mailer')->send($message);
    }

    /**
     * send activation email to user after he registers to the Partnership form
     * @param Partnership $partnership
     * @param Request $request
     * */
    protected function sendActivationEmail(Partnership $partnership, Request $request) {
        $activation_link = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('PartnershipActivation', array('md5_key' => $partnership->getMd5Key()));

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('partnership_activation_email', array(
            '%full_name%' => $partnership->getFullName(),
            '%activation_link%' => $activation_link,
            '%partnership_type%' => Partnership::$partnership_type[$partnership->getType()],
                ), $partnership->getEmail()
        );
        $this->get('mailer')->send($message);
    }

}
