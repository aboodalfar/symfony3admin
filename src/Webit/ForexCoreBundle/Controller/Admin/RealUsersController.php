<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexCoreBundle\Entity\TradingAccount;
use Webit\ForexCoreBundle\Entity\SubAccount;
use Webit\ForexCoreBundle\Helper\MT4API;
use Webit\ForexCoreBundle\Form\BoCompliance as BoComplianceForms;
use Webit\MailtemplateBundle\Helper\MailHelper;
use \Symfony\Component\HttpFoundation\RedirectResponse;

class RealUsersController extends Controller {

    public function ResendActivationAction($id, Request $request) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id)->getPortalUser();
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == RealProfile::FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "This application is locked because it's forwarded to the compliance.");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

        try {
            $this->sendActivationEmail($object, $request);
        } catch (Exception $exc) {
            $this->get('logger')->critical('Resending activation email error, ' . $exc->getMessage());
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to send activation email');
        }

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object->getRealProfile(),
                    'elements' => $this->admin->getShow(),
        ));
    }

    public function editAction($id = null, \Symfony\Component\HttpFoundation\Request $request = NULL) {
        $templateKey = 'edit';
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        if ($this->admin->getObject($id)->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

        $newly_created = $this->get('request')->getSession()->get('newly_created');
        if ($newly_created) { //generation of PDF made on first edit after creation, I don't know why it doesn't work on create method
            $this->generateSavePdf($object->getPortalUser());
            $this->get('request')->getSession()->set('newly_created', 0);
        }


        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->bind($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $this->admin->update($object);
                $this->addFlash('sonata_flash_success', 'flash_edit_success');

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                // redirect to edit mode
                if ($this->get('request')->get('btn_update_show')) {
                    return $this->redirect("show");
                }
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', 'flash_edit_error');
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        if ($this->get('request')->get('btn_update_show')) {
            return $this->redirect("show");
        }
        return $this->render($this->admin->getTemplate($templateKey), array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
        ));
    }

    public function showAction($id = null, \Symfony\Component\HttpFoundation\Request $request = NULL) {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        if ($object->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::RECENT) {
            $object->setBoStatus(\Webit\ForexCoreBundle\Entity\RealProfile::PENDING);
            $this->getDoctrine()->getManager()->persist($object);
            $this->getDoctrine()->getManager()->flush();

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("New user log", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => 'The application is opened/checked')));
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    /**
     * return the Response object associated to the create action
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return Response
     */
    public function createAction(\Symfony\Component\HttpFoundation\Request $request = NULL) {
        // the key used to lookup the template
        $templateKey = 'edit';

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->bind($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $this->admin->create($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('Salesforce', new GenericEvent("Sync. Salesforce", array('type' => 'Salesforce', 'object' => $object)));

                $this->addFlash('sonata_flash_success', 'flash_create_success');
                // redirect to edit mode
                $this->get('request')->getSession()->set('newly_created', 1);
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', 'flash_create_error');
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
                    'action' => 'create',
                    'form' => $view,
                    'object' => $object,
        ));
    }

    /**
     * save generated PDF to user profile field
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_usr
     */
    protected function generateSavePdf(PortalUser $portal_usr) {
        $pdf_path = $this->generatePdf($portal_usr);
        $portal_usr->setPdfDoc("/uploads/wkhtmltopdf/" . basename($pdf_path));
        $this->getDoctrine()->getManager()->persist($portal_usr);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * generate PDF for the user application, and return the name of file
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $user
     * @return string
     */
    protected function generatePdf(PortalUser $user) {
        $html = $this->renderView('WebitForexCoreBundle::Pdf/new_real_user.html.twig', array(
            'user' => $user
        ));

        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/wkhtmltopdf/';
        $file_name = md5(time() . $user->getId()) . '.pdf';
        $this->get('knp_snappy.pdf')->generateFromHtml($html, $dir . $file_name, array(), true);

        return $dir . $file_name;
    }

    /**
     * action to allow users create custom log message via form
     * @return Response
     */
    public function addNewLogAction() {
        $request = $this->get('request');
        $id = $request->get('id');
        $object = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfile')->find($id);

        $form = $this->createForm(new \Webit\UserLogBundle\Form\UserLogType());

        if ($request->getMethod() == 'POST') {
            $form = $form->bind($request);
            if ($form->isValid()) {
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Log Added manually", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => $form->get('body')->getData())));

                $this->addFlash('sonata_flash_success', 'New log has been added successfully');

                return new \Symfony\Component\HttpFoundation\RedirectResponse($this->admin->generateObjectUrl('show', $object));
            }
        }


        return $this->render("WebitForexCoreBundle::UserAdmin/add_new_log.html.twig", array('form' => $form->createView(), 'object' => $object, 'id' => $id));
    }

    /**
     * create new trading account from form values
     * handle both manual creation and automatic creation via API
     * @param int $id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function openSubAccountAction($id) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");
            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

//        $default_group = $this->getDoctrine()->
//                getRepository('\Webit\ForexCoreBundle\Entity\CodeTradingGroup')->
//                getOrCreateByName($object->getDefaultMt4GroupName());
        $subacc_request = $this->getSubaccountRequest();
        $form = $this->createForm(new BoComplianceForms\TradingAccountsType(array(
            'subacc_request' => $subacc_request,
            'real_user' => $object,
            //'default_group' => $default_group
        )));

        if ($this->get('request')->getMethod() == "POST") {
            $form->submit($this->get('request'));
            if ($form->isValid()) {
                return $this->processAccountCreation($form, $object, $subacc_request);
            } else {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'Please check form errors');
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/PortalUsers/createSubAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
    }

    /**
     * process action creation, set notice/error flags and return appropriate response
     *
     * @param \Symfony\Component\Form\Form $form
     * @param RealProfile $object
     * @param SubAccount|null $subacc_request
     * @return Response|RedirectResponse
     */
    protected function processAccountCreation($form, $object, $subacc_request = null) {
        $user = $object->getPortalUser();
        $trading_acc_created = $this->createTradingAccount($form, $user);
        if ($trading_acc_created === true) {

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Sub account created", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "mt4 sub-account number:<b>" . $form->get('login')->getData() . "</b> created successfully")));

            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'trading account created successfully and email send to the user');
            $this->linkToSubAccountRequest($subacc_request, $form->getData());
        } else {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "Cannot create trading account due to some error, please refer to technical support");
        }

        if ($subacc_request !== null) {
            $subaccount_admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode('forex.admin.forexsubaccount');
            return $this->redirect($subaccount_admin->generateObjectUrl('show', $subacc_request));
        } else {
            return $this->render($this->admin->getTemplate('show'), array('action' => 'show', 'object' => $object, 'elements' => $this->admin->getShow(),));
        }
    }

    /**
     * getting SubAccount request object if exists
     * @return SubAccount
     */
    protected function getSubaccountRequest() {
        $subaccount_request_id = $this->get('request')->get('subaccount_request_id');
        $subacc_req_obj = null;

        if (empty($subaccount_request_id) === false) {
            $subacc_req_obj = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\SubAccount')->find($subaccount_request_id);
        }

        return $subacc_req_obj;
    }

    /**
     * if subaccount creation is in response to user request,
     * link the data with that request and mark request as approved
     *
     * @param SubAccount $subacc_req_obj
     * @param TradingAccount $tradingAccount
     */
    protected function linkToSubAccountRequest($subacc_req_obj, $tradingAccount) {
        if ($subacc_req_obj) {
            $subacc_req_obj->setTradingAccount($tradingAccount);
            $subacc_req_obj->setStatus(SubAccount::STATUS_APPROVED);

            $em = $this->getDoctrine()->getManager();
            $em->persist($subacc_req_obj);
            $em->flush();
        }
    }

    public function batchActionSendNotificationEmail() {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');

        $targets = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass())->getBatchUsers($request->get('idx'));

        if ($targets === null) {
            $this->addFlash('sonata_flash_info', 'no users found');

            return new \Symfony\Component\HttpFoundation\RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        try {
            foreach ($targets as $user) {

                $message = $this->getDoctrine()->getRepository('WebitMailtemplateBundle:MailTemplate')->configEmailVars(
                        'client_notification', array(
                    '%full_name%' => $user->getPortalUser()->getFirstName() . ' ' . $user->getPortalUser()->getLastName(),
                        ), $user->getPortalUser()->getUsername()
                );

                $this->get('mailer')->send($message);
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Notification message", array('type' => 'log', 'user' => $user->getPortalUser(), 'body' => 'The expiration date message was sent to the user')));
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'an error occurred');

            return new \Symfony\Component\HttpFoundation\RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', 'The notification message sent successfully');

        return new \Symfony\Component\HttpFoundation\RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }

    public function batchActionSendExpirationEmail() {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');

        $targets = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass())->getBatchUsers($request->get('idx'));

        if ($targets === null) {
            $this->addFlash('sonata_flash_info', 'no users found');

            return new \Symfony\Component\HttpFoundation\RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        try {
            foreach ($targets as $user) {

                $message = $this->getDoctrine()->getRepository('WebitMailtemplateBundle:MailTemplate')->configEmailVars(
                        'expiration_email', array(
                    '%full_name%' => $user->getPortalUser()->getFirstName() . ' ' . $user->getPortalUser()->getLastName(),
                        ), $user->getPortalUser()->getUsername()
                );

                $this->get('mailer')->send($message);
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Expiration date message", array('type' => 'log', 'user' => $user->getPortalUser(), 'body' => 'The expiration date message was sent to the user')));
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'an error occurred');

            return new \Symfony\Component\HttpFoundation\RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', 'The expiration date message sent successfully');

        return new \Symfony\Component\HttpFoundation\RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }

    public function ApproveApplicationAction($id, Request $request) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }



        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);
        if ($object->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::APPROVED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'User account already approved!');
            return $this->render($this->admin->getTemplate('show'), array(
                        'action' => 'show',
                        'object' => $object,
                        'elements' => $this->admin->getShow(),
            ));
        }

        if ($this->admin->getObject($id)->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::FORWARDED) {

            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");

            return $this->render($this->admin->getTemplate("show"), array(
                        'action' => 'show',
                        'object' => $this->admin->getObject($id),
                        'elements' => $this->admin->getShow(),
            ));
        }

        if ($object->getBoIdStatus() != RealProfile::APPROVED || $object->getBoPorStatus() != RealProfile::APPROVED) {

            $form = $this->createFormBuilder()
                    ->add('boIdStatus', 'choice', array('required' => true, 'label' => 'ID Status', 'choices' => RealProfile::$docStatus, 'empty_value' => "Choose status..."))
                    ->add('boPorStatus', 'choice', array('required' => true, 'label' => 'Por Status', 'choices' => RealProfile::$docStatus, 'empty_value' => "Choose status..."))
                    ->add('idExpirationDate', 'date', array('required' => true, 'label' => 'Id Expiration Date', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->add('porExpirationDate', 'date', array('required' => true, 'label' => 'Por Expiration Date', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->getForm();


            if ($this->get('request')->getMethod() == "POST") {
                $form->submit($request);
                if ($form->isValid()) {

                    if ($form->get('boIdStatus')->getData() == 1 and $form->get('boPorStatus')->getData() == 1) {

                        $object->setBoIdStatus($form->get('boIdStatus')->getData());
                        $object->setBoPorStatus($form->get('boPorStatus')->getData());
                        $object->setIdExpirationDate($form->get('idExpirationDate')->getData());
                        $object->setPorExpirationDate($form->get('porExpirationDate')->getData());

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($object);
                        $em->flush();


                        $this->get('session')->getFlashBag()->add('sonata_flash_success', "this application successfully approved");

                        //return $this->ApproveApplicationAction($object->getId());
                        return $this->redirect($this->admin->generateObjectUrl('approve_application', $object));
                    } else {
                        $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to approve the application');
                    }
                }
            }


            return $this->render('WebitForexCoreBundle::sonata/PortalUsers/approve.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
        }


        $user = $object->getPortalUser();
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }



        $form = $this->createForm(new BoComplianceForms\TradingAccountsType);

        if ($this->get('request')->getMethod() == "POST") {
            $form->submit($this->get('request'));
            if ($form->isValid()) {
                if ($this->createTradingAccount($form, $user)) {
                    $this->approveUser($object);

                    $dispacher = $this->container->get('event_dispatcher');
                    $dispacher->dispatch('crm.event', new GenericEvent("Convert Account", array('type' => 'convert_lead', 'site_object' => $object->getPortalUser())));
                    $this->get('session')->getFlashBag()->add('sonata_flash_success', 'MT4 account created successfully and email send to the user');
                    
                    return $this->render($this->admin->getTemplate('show'), array(
                            'action' => 'show',
                            'object' => $object,
                            'elements' => $this->admin->getShow(),
                    ));                    
                    
                } else {
                    $mt4_error = $this->get('session')->getFlashBag()->get('mt4_error');
                    if (is_array($mt4_error) == true) {
                        $mt4_error = current($mt4_error);
                    }
                    $this->get('session')->getFlashBag()->add('sonata_flash_error', "Cannot create trading account, reason: $mt4_error, please review form data or refer to technical support");
                }



            }
        }

        return $this->render('WebitForexCoreBundle::sonata/PortalUsers/createTradingAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
    }

    /**
     * create new trading account from form values
     * handle both manual creation and automatic creation via API
     * @param \Symfony\Component\Form\Form $form
     * @param PortalUser $user
     * @return boolean $ret indicates successfull creation or not
     */
    protected function createTradingAccount(\Symfony\Component\Form\Form $form, $user) {
        $password = $form->get('onlinePassword')->getData();
        if (!$form->get('manual_create')->getData()) { //automatic creation from API
            $ret = $this->handleAPICreation($form, $user, $password,$login);
        } else { //manual
            $ret = $this->handleManualCreation($form, $user,$login);
        }

        if ($ret === true) {
            $this->sendAccountCreationEmail($user, $login, $password);
        }

        return $ret;
    }

    /**
     * handle automatic trading account creation via API
     * @param $form
     * @param PortalUser $user
     * @param String $password by reference to return value to email sending
     * $parm integer $login
     * @return boolean indicate successful operation
     */
    protected function handleAPICreation($form, $user, &$password,&$login) {
        $api_info = $this->container->getParameter('mt4_api');
        $em = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');
        $error_string = null;

        try {
            $api = new MT4API($em, $this->getUser()->getId());
            $api->OpenConnection($api_info['real']['host'], $api_info['real']['port']);

            $data = $this->getDataForAPI($form, $user);
            $login=$data['login'];//some times admin leave field zero then -> auto generated
            $api->openAccount($data, true, $error_string);
            $password = $data['password']; //store it in the database
            $this->saveAPIAccountToDB($form, $user, $data);

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Trading account created", array('type' => 'log', 'user' => $user, 'body' => "Application is approved and creation of trading account #" . $form->get('login')->getData())));
            $logger->info('MT4 account#' . $form->get('login')->getData() . ' created successfully via API');

            return true;
        } catch (\Exception $ex) {
            $logger->critical('MT4 account#' . $form->get('login')->getData() . ' cannot be created via API, reason:' . $ex->getMessage());
            $this->get('session')->getFlashBag()->add('mt4_error', $error_string);
            return false;
        }
    }

    /**
     * saving API account to database
     * @param Form $form
     * @param PortalUser $user
     * @param array $data
     * @return TradingAccount
     */
    protected function saveAPIAccountToDB($form, $user, $data) {
        $trading_acc = $form->getViewData();
        $trading_acc->setLogin($data['login']);
        $trading_acc->setRoPassword($data['password']);
        $trading_acc->setOnlinePassword($data['password_investor']);
        $trading_acc->setPortalUser($user);
        $trading_acc->setAccountType(TradingAccount::real_account);
        $em = $this->getDoctrine()->getManager();
        $em->persist($trading_acc);
        $em->flush();
    }

    /**
     * configure data to be passed for web API
     * @param Form $form
     * @param PortalUser $user
     * @return array
     */
    protected function getDataForAPI($form, $user) {
        $trading_acc = $form->getViewData();
        $real_usr = $user->getRealProfile();
        
        if($trading_acc->getLogin() == 0) {
             $trading_ret = $this->getDoctrine()->getRepository("\Webit\ReportingBundle\Entity\MT4Users", 'reporting_real')->getLastLogin();
             $last_login = $trading_ret[0]['login'];
             $login = $last_login + 1;
         }else{
             $login = $trading_acc->getLogin();
         }
        
        $arr = array(
            'login' => $login,
            'comment' => $trading_acc->getComment(),
            'agent_account' => $trading_acc->getAgentAccount(),
            'group' => $trading_acc->getCodeTradingGroup()->getName(),
            'name' => $user->getFullName(),
            'leverage' => $trading_acc->getLeverage(),
            'email' => $user->getUsername(),
            'city' => $user->getCity(),
            'country' => $user->getCountryLabel(),
            'address' => $real_usr->getFullAddress(),
            'postal_code' => $real_usr->getPostalCode(),
            'phone_number' => $user->getMobileNumber(),
            'passport_id' => $real_usr->getPersonalId(),
        );

        return $arr;
    }

    /**
     * handle manual trading account creation; insert to DB, and log results
     * @param $form
     * @param PortalUser $user
     * @return boolean indicate successful operation
     */
    protected function handleManualCreation($form, PortalUser $user,&$login) {
        $dispacher = $this->container->get('event_dispatcher');
        $logger = $this->get('logger');
        $trading_acc = $form->getViewData();
        if($form->get('login')->getData()==0)//get login from api
        {
            $api_info = $this->container->getParameter('mt4_api');
            $em = $this->getDoctrine()->getManager();
            $api = new MT4API($em, $this->getUser()->getId());
            $api->OpenConnection($api_info['real']['host'], $api_info['real']['port']);
            $data = $this->getDataForAPI($form, $user);
            $login=$data['login'];
            
        }
        else
        {
           $login=$form->get('login')->getData(); 
        }
        try {
            $trading_acc->setLogin($login);
            $trading_acc->setRoPassword($form->get('roPassword')->getData());
            $trading_acc->setOnlinePassword($form->get('onlinePassword')->getData());
            $trading_acc->setPortalUser($user);
            $trading_acc->setAccountType(TradingAccount::real_account);
            $em = $this->getDoctrine()->getManager();
            $em->persist($trading_acc);
            $em->flush();

            $dispacher->dispatch('user.log', new GenericEvent("Trading account created", array('type' => 'log', 'user' => $user, 'body' => "Creation of trading account #" . $trading_acc->getLogin() . ' manually')));
            $logger->info('MT4 account #' . $trading_acc->getLogin() . ' has been created manually');

            return true;
        } catch (\Exception $ex) {
            $logger->error('cannot create Mt4 account, due to this error: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * set user as 'backoffice approved' in the database
     * @param \Webit\ForexCoreBundle\Entity\RealProfile $object
     */
    protected function approveUser(RealProfile $object) {
        $object->setBoStatus(RealProfile::APPROVED);
        if ($object->getCompStatus() != RealProfile::APPROVED) { //if compliance not approved it previously...
            $object->setCompStatus(RealProfile::PENDING);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();

        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent("User Application approved", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Application is approved")));
    }

    /**
     * send email to client notifying him of trading account creation
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $user
     * @param int $login
     * @param string $password
     */
    public function sendAccountCreationEmail(PortalUser $user, $login, $password) {
        $email_params = array(
            '%full_name%' => $user->getFirstName(),
            '%username%' => $user->getUsername(),
            '%login%' => $login,
            '%password%' => $password,
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('mt4_creation_success', $email_params, $user->getUsername(), $this->get('request')->getLocale());

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

        if ($this->admin->getObject($id)->getBoStatus() == RealProfile::FORWARDED) {

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

                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("User Application Rejected", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "Application is rejected, reason: " . nl2br($form->get("reason")->getData()))));

                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This application has been rejected successfully');

                return $this->render($this->admin->getTemplate('show'), array(
                            'action' => 'show',
                            'object' => $object,
                            'elements' => $this->admin->getShow(),
                ));
            }
        }

        return $this->render('WebitForexCoreBundle::sonata/PortalUsers/reject.html.twig', array(
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
        $object->setBoStatus(RealProfile::REJECT);
        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * send rejection email to the client
     * @param RealProfile $object
     * @param Symfony\Component\Form\Form $form
     */
    protected function sendRejectionEmail($object, $form) {
        if (!$form->get('notify_client')->getData()) {
            //notify client not checked, don't send any email
            return;
        }

        $user = $object->getPortalUser();
        $email_params = array(
            '%full_name%' => $user->getFirstName(),
            '%reason%' => nl2br($form->get("reason")->getData()),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('reject_application', $email_params, $user->getUsername(), $this->get('request')->getLocale());
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

        $this->setUserAsForwarded($object);
        $this->sendForwardEmailToCompliance($object);

        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent("New user log", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => 'The application is forwarded to compliance')));
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
    protected function setUserAsForwarded($object) {
        $object->setBoStatus(RealProfile::FORWARDED);
        $object->setCompStatus(RealProfile::PENDING);
        $object->setForwardedComplianceDate(new \DateTime());

        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * send notification email to complaince upon forwarding new application to him
     * @param RealProfile $object
     */
    protected function sendForwardEmailToCompliance($object) {
        $user = $object->getPortalUser();
        $emails = $this->container->getParameter('emails');
        $email_params = array(
            '%full_name%' => $user->getFirstName() . ' ' . $user->getLastName(),
            '%username%' => $user->getUsername(),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('forwared_to_compliace_email  ', $email_params, $emails['compliance'], $this->get('request')->getLocale());
        $this->get('mailer')->send($message);
    }

    /**
     * re-send activation email to user if not already activated
     * @param RealUser $object
     * @param Request $request
     */
    protected function sendActivationEmail($object, Request $request) {
        if (!$object->getActive()) {
            $email_params = array(
                '%full_name%' => ucfirst($object->getFirstName()) . ' ' . $object->getLastName(),
                '%activation_link%' => $request->getScheme() . "://" . $request->getHost() . $this->generateUrl("activate_real", array('md5_key' => $object->getMd5Key())),
            );

            $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
            $message = $message_helper->configureMail('activation_email', $email_params, $object->getUsername(), $this->get('request')->getLocale());
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Activation email has been sent successfully');
        } else {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'The user already active');
        }
    }
    
    /**
     *  resend trading info from admin dashboard
     * @param integer $id
     * @param Request $request
     * @return \Webit\ForexCoreBundle\Controller\Admin\RedirectResponse
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
        public function ResendInfoAction($id, Request $request) {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $portal_user = $object->getPortalUser();

        try {
            $sended = $this->sendPersonalInfo($portal_user);

            if ($sended) {
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Personal information resent", array('type' => 'log', 'user' => $portal_user,
                    'body' => 'Personal information was resent to the user')));
            }
        } catch (Exception $exc) {
            $this->get('logger')->critical('Resending personal information error, ' . $exc->getMessage());
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to send personal information');
        }

        return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
    }
        /**
     * re-send personal information to user
     * @param PortalUser $object
     * @return bool
     */
    protected function sendPersonalInfo(PortalUser $object, $with_flash = true) {
        $request = $this->get('request');
        $myikon_lnk = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('homepage');
        $reset_password = $request->getScheme() . "://" . $request->getHost()
                . $this->generateUrl('members_reset_forgot_pass', array('md5_key' => $object->getMd5Key()));

        $trading_accounts = $object->getTradingAccounts();
        if (count($trading_accounts) > 0) {
            $trading = $trading_accounts[0];
            $email_params = array(
                '%full_name%' => ucfirst($object->getFirstName()) . ' ' . $object->getLastName(),
                '%username%' => $object->getUsername(),
                '%reset_pass_link%' => $reset_password,
                '%mt4_login%' => $trading->getLogin(),
                '%mt4_password%' => $trading->getRoPassword() ? $trading->getRoPassword() : '',
                '%mt4_investor_password%' => $trading->getOnlinePassword() ? $trading->getOnlinePassword() : '',
                '%myikon_link%' => $myikon_lnk,
            );
            $message_helper = new MailHelper($this->get('templating'), $this->getDoctrine());
            $message = $message_helper->configureMail('ResendTradingInformation', $email_params, $object->getUsername(), $object->getCommunicationLanguage());
            $message->setCc('welcomeletter@ikonfx.com');
            $this->get('mailer')->send($message);

            if ($with_flash) {
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Trading account information has been sent successfully');
            }
            return true;
        } else {
            if ($with_flash) {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to find the trading account for user');
            }
            return false;
        }
    }

}
