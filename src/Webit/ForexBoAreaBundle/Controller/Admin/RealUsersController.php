<?php

namespace Webit\ForexBoAreaBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexCoreBundle\Entity\TradingAccount;
use Webit\ForexCoreBundle\Entity\SubAccount;
use Webit\ForexCoreBundle\Entity\DocumentsTranslation;
use Webit\ForexCoreBundle\Entity\Platform;

use Webit\ForexBoAreaBundle\Form\BoCompliance as BoComplianceForms;
use Webit\ForexCommonBundle\Exceptions as ForexExceptions;
use Webit\ForexCoreBundle\Entity\Notification;
use Webit\ForexCoreBundle\Helper\UtilsHelper;
use Webit\ForexCoreBundle\Entity\RealApplicationTranslation;


class RealUsersController extends BaseController
{
    /**
     * documents translation action to handle non-english user application
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function TranslateDocumentsAction($id, Request $request =null){
         if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        
        $form  = $this->createForm(new BoComplianceForms\DocumentsTranslationType());                
        
        if($request->getMethod()=='POST'){
            $this->processDocTranslationForm($object, $form);
        }

        $translation_json = $this->getJsonTranslation($object->getPortalUser());        
        return $this->render('WebitForexBoAreaBundle::PortalUsers/translateDocuments.html.twig',
                array(  'translation_json'  =>  $translation_json,
                        'object'        =>  $object,
                        'action'        => 'translate_documents',
                        'form'          => $form->createView(),
                ));
    }

    protected function processDocTranslationForm($object, $form){
            $request = $this->get('request');
            $portal_usr = $object->getPortalUser();
            $trans_data = $request->get('doc_trans');

            $trans_rep = $this->get('doctrine')
                        ->getRepository('\Webit\ForexCoreBundle\Entity\DocumentsTranslation');
            $translation_obj = $trans_rep->getOrCreateObject($portal_usr,$trans_data['documentType']);
            $form = $this->createForm(new BoComplianceForms\DocumentsTranslationType(), $translation_obj);
            $form->submit($request);

            if($form->isValid()){
                $this->saveNewDocTranslationToDB($translation_obj, $portal_usr);
                $this->addFlash('sonata_flash_success', "Document translation data saved successfully");
            }else{
                $this->addFlash('sonata_flash_error', "Cannot save document translation due to some error");                
            }        
    }

    protected function getJsonTranslation($portal_usr){
        $trans_rep = $this->get('doctrine')
                        ->getRepository('\Webit\ForexCoreBundle\Entity\DocumentsTranslation');
        $translations = $portal_usr->getDocumentsTranslations();

        $ret = array();
        foreach($translations as $trans){
            $ret[$trans->getDocumentType()] = $trans->toArray();
        }
        return json_encode($ret);
    }

    protected function saveNewDocTranslationToDB($translation_obj, PortalUser $portal_usr){
        $translation_obj->setPortalUser($portal_usr);

        $em = $this->get('doctrine')->getManager();
        $em->persist($translation_obj);
        $em->flush();

        //reflect to real profile application
        $real_profile = $portal_usr->getRealProfile();
        if($translation_obj->getDocumentType() == DocumentsTranslation::DOC_TYPE_ID_CARD){
            $real_profile->setDateOfExpirePassportNumber($translation_obj->getDateOfExpiry());
        }elseif($translation_obj->getDocumentType() == DocumentsTranslation::DOC_TYPE_POR){
            $real_profile->setPorExpirationDate($translation_obj->getDateOfExpiry());
        }
        $em->persist($real_profile);
        $em->flush();

        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', new GenericEvent("Document Translation Saved", 
                    array('type' => 'log', 
                          'user' => $portal_usr, 
                          'body' => 'Document '.$translation_obj->getDocumentTypeLabel().' is translated')));        
    }
    

    public function ResendActivationAction($id, Request $request)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id)->getPortalUser();
        $real_user = $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == RealProfile::FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "This application is locked because it's forwarded to the compliance.");

            return new RedirectResponse($this->admin->generateObjectUrl('show', $real_user));
        }

        try {
            $this->sendActivationEmail($object, $request);
        } catch (Exception $exc) {
            $this->get('logger')->critical('Resending activation email error, ' . $exc->getMessage());
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'unable to send activation email');
        }

        return new RedirectResponse($this->admin->generateObjectUrl('show', $real_user));
    }

    public function editAction($id = null, Request $request = null)
    {
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

            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }

        $newly_created = $this->getRequest()->getSession()->get('newly_created');
        if ($newly_created) { //generation of PDF made on first edit after creation, I don't know why it doesn't work on create method
            $this->generateSavePdf($object->getPortalUser());
            $this->getRequest()->getSession()->set('newly_created', 0);
        }


        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));
                        
            $isFormValid = $form->isValid();
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $this->admin->update($object);
                $this->addFlash('sonata_flash_success', 
                        $this->admin->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                        );

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                // redirect to edit mode
                if ($this->getRequest()->get('btn_update_show')) {
                    return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
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

        if ($this->getRequest()->get('btn_update_show')) {
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }
        return $this->render($this->admin->getTemplate($templateKey), array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
        ));
    }

    public function showAction($id = null, Request $request = null)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        if ($object->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::RECENT) {
            $this->markApplicationAsSeen($object);            
        }

        $this->admin->setSubject($object);       
        $translation_form = $this->processTranslationForm($object,$request);

        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
                    'translation_form' => $translation_form->createView(),
        ));
    }

    protected function processTranslationForm($object,$request){
       
        $portal_usr = $object->getPortalUser();
        $translation_obj = $this->get('doctrine')
                            ->getRepository('\Webit\ForexCoreBundle\Entity\RealApplicationTranslation')
                            ->findOneBy(array('userId'=>$portal_usr->getId()))
                            ;

        if(!$translation_obj){
            $translation_obj = new RealApplicationTranslation();
            $translation_obj->setPortalUser($portal_usr);
        }

        $translation_form = $this->createForm(
             BoComplianceForms\RealApplicationTranslationType::class,
            $translation_obj
            );

        if($request->isMethod('POST')){
            $translation_form->submit($this->get('request'));
            if($translation_form->isValid()){
                $em = $this->get('doctrine')->getManager();
                $em->persist($translation_obj);
                $em->flush();
                $trading_accounts = $portal_usr->getTradingAccounts();
                
                $logger = $this->get('logger');
                $apiFactory = $this->get('trading.api.factory');
                $api = $apiFactory->createAPI('mt5', 'real');
                
                foreach ($trading_accounts as $trading_account) {
                    $api->OpenConnection(); 
                    try{
                      $this->SyncRealAccount($trading_account->getLogin(),$translation_obj,$api,$portal_usr);  
                    }catch (\Exception $e) {
                      $logger->critical('cannot sync mt5 real account translation update, error: ' . $e->getMessage());
                    }
                    sleep(3);
                }
                
                $api->closeConnection();

                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Application Translation Submitted", 
                    array('type' => 'log', 
                          'user' => $object->getPortalUser(), 
                          'body' => 'Application Translation Data Filled')));

                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Translation information has been saved successfully');
            }
        }

        return $translation_form;
    }

    /**
     * return the Response object associated to the create action
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return Response
     */
    public function createAction(Request $request = null)
    {
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

                $this->addFlash('sonata_flash_success', $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        ));
                // redirect to edit mode
                $this->getRequest()->getSession()->set('newly_created', 1);
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        ));
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
    protected function generateSavePdf(PortalUser $portal_usr)
    {
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
    protected function generatePdf(PortalUser $user)
    {
        $html = $this->renderView('WebitForexBoAreaBundle::Pdf/new_real_user.html.twig', array(
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
    public function addNewLogAction()
    {
        $id = $this->getRequest()->get('id');
        $object = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfile')->find($id);

        $form = $this->createForm(new \Webit\UserLogBundle\Form\UserLogType());

        if ($this->getRequest()->getMethod() == 'POST') {
            $form = $form->bind($this->getRequest());
            if ($form->isValid()) {
                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('user.log', new GenericEvent("Log Added manually", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => $form->get('body')->getData())));

                $this->addFlash('sonata_flash_success', 'New log has been added successfully');

                return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
            }
        }


        return $this->render("WebitForexBoAreaBundle::UserAdmin/add_new_log.html.twig", array('form' => $form->createView(), 'object' => $object, 'id' => $id));
    }

    /**
     * create new trading account from form values
     * handle both manual creation and automatic creation via API
     * @param int $id
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function openSubAccountAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($this->admin->getObject($id)->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::FORWARDED) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");
                return new RedirectResponse($this->admin->generateObjectUrl('show', $this->admin->getObject($id)));            
        }

        $subacc_request = $this->getSubaccountRequest();
        $form = $this->createForm(new BoComplianceForms\TradingAccountsType(array(
            'subacc_request' => $subacc_request,
        )));
        
        if ($this->getRequest()->getMethod() == "POST") {
            $form->submit($this->getRequest());
            if ($form->isValid()) {
                return $this->processAccountCreation($form, $object, $subacc_request);
            } else {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'Please check form errors');
            }
        }

        return $this->render('WebitForexBoAreaBundle::PortalUsers/createSubAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
    }

    /**
     * process action creation, set notice/error flags and return appropriate response
     *
     * @param \Symfony\Component\Form\Form $form
     * @param RealProfile $object
     * @param SubAccount|null $subacc_request
     * @return Response|RedirectResponse
     */
    protected function processAccountCreation($form, $object, $subacc_request = null)
    {
        $user = $object->getPortalUser();
        $trading_acc_created = $this->createTradingAccount($form, $user);
        if ($trading_acc_created === true) {

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Sub account created", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => "mt4 sub-account number:<b>" . $form->get('login')->getData() . "</b> created successfully")));

            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'account trader created successfully and email send to the user');
            $this->linkToSubAccountRequest($subacc_request, $form->getData());
        } else {
            $mt4_error = $this->get('session')->getFlashBag()->get('mt4_error');
            if(is_array($mt4_error)){
                $mt4_error = current($mt4_error);
            }
            $this->get('session')->getFlashBag()->add('sonata_flash_error', "Cannot create trading account, reason: ".$mt4_error);
        }

        if ($subacc_request !== null) {
            $subaccount_admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode('boforex.admin.forexsubaccount');
            return $this->redirect($subaccount_admin->generateObjectUrl('show', $subacc_request));
        } else {
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }
    }

    /**
     * getting SubAccount request object if exists
     * @return SubAccount
     */
    protected function getSubaccountRequest()
    {
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
    protected function linkToSubAccountRequest($subacc_req_obj, $tradingAccount)
    {
        if ($subacc_req_obj) {
            $subacc_req_obj->setTradingAccount($tradingAccount);
            $subacc_req_obj->setStatus(SubAccount::STATUS_APPROVED);

            $em = $this->getDoctrine()->getManager();
            $em->persist($subacc_req_obj);
            $em->flush();
            $this->notifyApproveSubAccount($subacc_req_obj->getPortalUser());
        }
    }

    public function batchActionSendNotificationEmail()
    {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');

        $targets = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass())->getBatchUsers($request->get('idx'));

        if ($targets === null) {
            $this->addFlash('sonata_flash_info', 'no users found');

            return new RedirectResponse(
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

            return new RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', 'The notification message sent successfully');

        return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }

    public function batchActionSendExpirationEmail()
    {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $request = $this->get('request');

        $targets = $this->getDoctrine()->getManager()->getRepository($this->admin->getClass())->getBatchUsers($request->get('idx'));

        if ($targets === null) {
            $this->addFlash('sonata_flash_info', 'no users found');

            return new RedirectResponse(
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

            return new RedirectResponse(
                    $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', 'The expiration date message sent successfully');

        return new RedirectResponse(
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
            
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }

        if ($this->admin->getObject($id)->getBoStatus() == \Webit\ForexCoreBundle\Entity\RealProfile::FORWARDED) {

            $this->get('session')->getFlashBag()->add('sonata_flash_error', "this application is locked because it's forwarded to the compliance");
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }

        if(!is_null($object->getUserStep())){
             $this->get('session')->getFlashBag()
                     ->add('sonata_flash_error', "this application is locked because it's not completed registration");
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }
        if ($object->getBoIdStatus() != RealProfile::APPROVED || $object->getBoPorStatus() != RealProfile::APPROVED) {
                $ret = $this->processUnapprovedObject($object);
                if(isset($ret)){
                    return $ret;
                }
        }
        
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        $portal_user = $object->getPortalUser();
        $trading_account = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\TradingAccount')
                    ->findOneBy(array('PortalUser'=>$portal_user,'account_type'=>TradingAccount::ACCOUNT_TYPE_REAL));
       
        if(is_null($trading_account)){
            // user not has real trading account
            $form = $this->createForm(new BoComplianceForms\TradingAccountsType());
            $ret = $this->processApprovedAccountForm($object);
            $this->approveUser($object);
            $this->sendEmailToTraders($portal_user);
            if(isset($ret)){
              return $ret;
            }
            return $this->render('WebitForexBoAreaBundle::PortalUsers/createTradingAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
        }else {
            $this->approveUser($object);
            $this->sendEmailToTraders($portal_user);
        }
       
        return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        
    }
    
    /**
     * send email to traders user when application is approved
     * @param PortalUser $user
     */
    public function sendEmailToTraders(PortalUser $user) {
//        $email_params = array(
//            '%full_name%'=>$user->getFullName()
//        );
//        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
//        $message = $mailtemplate_helper->configureMail('application_approved', 
//                $email_params, 
//                $user->getUsername(), 
//                'en');
//        
//        if(!is_null($user->getAlternativeEmail())){
//            $message->setBcc($user->getAlternativeEmail());
//        }
//        $mailtemplate_helper->send($message); 
        
         $this->createNotification($user,$this->getUser(),Notification::NOTIFICATION_APPROVE_APPLICATION);
    }


    /**
     * create new trading account from form values
     * handle both manual creation and automatic creation via API
     * @param \Symfony\Component\Form\Form $form
     * @param PortalUser $user
     * @return boolean $ret indicates successfull creation or not
     */
    protected function createTradingAccount(\Symfony\Component\Form\Form $form, $user)
    {
        if (!$form->get('manual_create')->getData()) { //automatic creation from API
            $account_data = [];
            $ret = $this->handleAPICreation($form, $user,$account_data);
            if($ret !== false){
               $this->createTradingAccountObject($form, $user,$ret); 
               $account_data['login'] = $ret['login'];
               $account_data['password'] = $ret['MasterPassword'];
            }          
        } else { //manual
            $account_data = [
                'login' => $form->get('login')->getData(),
                'password' => $form->get('online_password')->getData(),
            ];
            $ret = $this->handleManualCreation($form, $user);
            if($ret){
                /*@var $manager_card \Webit\TradersAreaBundle\Manager\CardManager */
                $manager_card = $this->get('traders.card');
                $manager_card->changeCardType($user);
            }
            
        }

        if ($ret === true || is_array($ret)) {
            $this->sendAccountCreationEmail($user, 
                    $account_data['login'], 
                    $account_data['password'],
                    $form->get('Platform')->getData()
                    );
            $ret = true ;
        }

        return $ret;
    }

    /**
     * handle automatic trading account creation via API
     * @param \Symfony\Component\Form\Form $form
     * @param PortalUser $user
     * @return boolean indicate successful operation
     */
    protected function handleAPICreation(\Symfony\Component\Form\Form  $form, PortalUser $user,&$data)
    {
        $group = $form->get('trading_account_type')->getData();
      
//        $isHaveLogin = $user->getFirstOneTradingAccount();
//        if($isHaveLogin === 0){
//            $group = 'preliminary';
//        }
//        elseif($group){
//            $group = isset(RealProfile::$trading_type_list[$group])
//                    ?strtolower(str_replace(" ","_",RealProfile::$trading_type_list[$group])):null;
//            
//            if($this->container->hasParameter('real.'.$group)){
//                $group = $this->container->getParameter('real.'.$group);
//            }else{
//                $group = $this->container->getParameter('real.ecn');
//            }
//
//        }else {
//            $group = $this->container->getParameter('real.ecn');
//        }
        
        $real_profile = $user->getRealProfile();
        $leverage = $form->get('leverage')->getData();
        $logger    = $this->get('logger');
        $InvestorPassword = UtilsHelper::generateRandomPassword(9);
        $MasterPassword = UtilsHelper::generateRandomPassword(9);
        $realMt5Groups = $this->container->getParameter('real_mt5_groups');
        $groupFixed = isset(RealProfile::$trading_type_list[$group])
                    ?strtolower(str_replace(" ","_",RealProfile::$trading_type_list[$group])):RealProfile::default_group;
        
       
      
        if($real_profile->getCompStatus() == RealProfile::APPROVED){
            // when open sub account from admin
            $enable = 1;
            $read_only = '0'; 
            $approvedGroups = $realMt5Groups['approved_group'];
            $finalGroup = $approvedGroups[$groupFixed];
            
        }else {
            $temporaryGroups = $realMt5Groups['temporary_group'];
            $finalGroup = $temporaryGroups[$groupFixed];
            $enable = 1;
            $read_only = '1'; 
        }
       
        $data = [
                "Login" => $form['login']->getData(),
                "Name" => $user->getFullName(),
                "address" => $real_profile->getFullAddress(),
                "Currency" => $form['TradingCurrency']->getData()->getIsoCode(),
                "Group" => $finalGroup,
                "Leverage" => $leverage,
                "Agent" => (int)$form['agent_account']->getData(),
                "Comment" => $form['comment']->getData(),
                "enabled" => $enable,
                "enableReadOnly" => $read_only,
                "Country" => $real_profile->getCountryForAPi(),
                "City" => $real_profile->getCity(),
                "Phone" => $user->getMobileNumber(),
                "Email" => $user->getUsername(),
                "id" => $real_profile->getIDAPi(),
                "zipcode" => $real_profile->getZipCode(),
                "InvestorPassword" => $InvestorPassword,
                "MasterPassword" => $MasterPassword,
        ];
        if($real_profile->getIndividualOrCorporations() == RealProfile::TypeCorporate){
            $data['company'] = $real_profile->getRealProfileCorporation()->getCorporateName();
        }
        
        try {
            /*@var $apiFactory \Webit\ForexCommonBundle\Helper\TradingAPIFactory */
            $apiFactory = $this->get('trading.api.factory');
            
            $api = $apiFactory->createAPI($form['Platform']->getData()->getCode(), 'real');
            $api->openConnection();
            $respose = $api->openAccount($data);
            if($data['Group'] == 'real\Moneyback' || $data['Group'] == 'preliminary\Moneyback'){
                sleep(3);
                $api->openConnection();
                $api->updateAccount(array('Login'=>$data['login'],'Agent'=>$data['login'])); 
                $data['Agent'] = $data['login'];
            }
            
            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("Trading account created", array('type' => 'log', 'user' => $user, 'body' => "Application is approved and creation of trading account #" . $form->get('login')->getData())));
            $logger->info('MT4 account#' . $data['Login'] . ' created successfully via API');
            return $data;
        } catch (ForexExceptions\MT4APIResponseException $ex) {
            $this->get('session')->getFlashBag()->set('mt4_error', $ex->getMessage());            
            $logger->error('MT4 account#' . $data['Login'] . ' cannot be created via API, reason:' . $ex->getMessage());  
            return false;
        }catch(ForexExceptions\SocketOpeningException $ex){
            $this->get('session')->getFlashBag()->set('mt4_error', $ex->getMessage());            
            $logger->error('MT4 account#' . $data['Login'] . ' cannot be created via API, reason:' . $ex->getMessage()); 
            return false;
        }        
    }

    /**
     * handle manual trading account creation; insert to DB, and log results
     * @param $form
     * @param PortalUser $user
     * @return boolean indicate successful operation
     */
    protected function handleManualCreation($form, PortalUser $user)
    {
        $dispacher = $this->container->get('event_dispatcher');
        $logger = $this->get('logger');
        $trading_acc = $form->getViewData();

        // get group name
        try {
            /* @var $apiFactory \Webit\ForexCommonBundle\Helper\TradingAPIFactory */
            $apiFactory = $this->get('trading.api.factory');
            $api = $apiFactory->createAPI($trading_acc->getPlatform()->getCode());

            $socket_opened = $api->openConnection();

            $data = $api->getAccountInfo($trading_acc->getLogin());
        } catch (\Exception $ex) {
            $data = [];
        }
        try {
            $trading_acc->setPortalUser($user);
            $trading_acc->setAccountType(TradingAccount::ACCOUNT_TYPE_REAL);
            if(isset($data['group'])){
                $trading_acc->setGroupName($data['group']);
            }
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
    protected function approveUser(RealProfile $object)
    {
        $object->setBoStatus(RealProfile::APPROVED);
        if ($object->getCompStatus() != RealProfile::APPROVED) { //if compliance not approved it previously...
            $object->setCompStatus(RealProfile::PENDING);
	    $this->setUserForwardDate($object);
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
     * @param Platform $platform
     */
    public function sendAccountCreationEmail(PortalUser $user, $login, $password, Platform $platform)
    {
        $email_params = array(
            '%login%' => $login,
            '%password%' => $password,
            '%full_name%'=>$user->getFullName()
        );

        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
        $message = $mailtemplate_helper->configureMail('mt5_real_creation_success', 
                $email_params, 
                $user->getUsername(), 
                $this->getRequest()->getLocale());

        $mailtemplate_helper->send($message); 
    }

    /**
     * action for rejecting application for client
     * @param $id integer
     * @return Response
     */
    public function RejectApplicationAction($id = null)
    {
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
            
            return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        }

        $request = $this->getRequest();
        $form = $this->createForm(new BoComplianceForms\RejectApplicationType());

        if ($request->getMethod() == "POST") {

            $form->submit($request);
            if ($form->isValid()) {
                
                $this->setUserAsRejected($object);
                $this->sendRejectionEmail($object, $form);
                $this->dispatchRejectEvent($object, $form);
                
                $reson = $this->getFormRejectionText($form);
                $replace_params = array('%missing_document%'=>$reson);
                $this->createNotification(
                        $object->getPortalUser(),$this->getUser(),Notification::NOTIFICATION_MISSING_DOCUMENTS,$replace_params);

                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This application has been rejected successfully');
               
                return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
            }
        }

        return $this->render('WebitForexBoAreaBundle::PortalUsers/reject.html.twig', array(
                    'action' => 'reject',
                    'object' => $object,
                    'form' => $form->createView(),
        ));
    }

    /**
     * getting friendly text for reject reason
     * @param Symfony\Component\Form\Form $form
     * @return string
     */
    protected function getFormRejectionText($form){
        $ret = '';
        $predefined_reasons = $form->get("predefined_reasons")->getData();

        foreach($predefined_reasons as $reason){
            $ret .= '<br/>'.$reason->getContent(); //TODO: handle different languages
        }

        $other_reject = $form->get("reason")->getData();
        if(!empty($other_reject)){
            $ret .= '<br/>'.nl2br($other_reject);
        }

        return $ret;
    }

    /**
     * set user status as "rejected" and save to DB
     * @param RealUserProfile $object
     */
    protected function setUserAsRejected($object)
    {
        $object->setBoStatus(RealProfile::REJECT);
        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * send rejection email to the client
     * @param RealProfile $object
     * @param Symfony\Component\Form\Form $form
     */
    protected function sendRejectionEmail($object, $form)
    {
        $user = $object->getPortalUser();
        $email_params = array(
            '%full_name%' => $user->getFirstName(),
            '%reason%' => $this->getFormRejectionText($form),
            '%link%' => $this->generateUrl('webit_tradersarea_editinfo',array(),0)
        );

        $mail_helper = $this->get('webit.mailtemplate.helper');
        $message = $mail_helper->configureMail('reject_application', $email_params, $user->getUsername(), $this->getRequest()->getLocale());
        $mail_helper->send($message);
    }

    /**
     * dispatch user log event after rejection of application
     * @param RealProfile $object
     * @param Symfony\Component\Form\Form $form
     */
    protected function dispatchRejectEvent($object, $form){
        $reject_reason = $this->getFormRejectionText($form);
        $dispacher = $this->container->get('event_dispatcher');
        $dispacher->dispatch('user.log', 
            new GenericEvent("User Application Rejected", 
                array('type' => 'log', 
                      'user' => $object->getPortalUser(), 
                      'body' => "Application is rejected, reason: " . $reject_reason )
                ));        
    }

    /**
     * forward to compliance action
     * @return Response
     */
    public function forwardApplicationAction()
    {
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

        return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
    }
    
    /**
     * mark user as "forwarded to compliance" in the databases
     * @param RealProfile $object
     */
    protected function setUserAsForwarded($object)
    {
        $object->setBoStatus(RealProfile::FORWARDED);
        $object->setCompStatus(RealProfile::PENDING);
        $object->setForwardedComplianceDate(new \DateTime());

        $this->getDoctrine()->getManager()->persist($object);
        $this->getDoctrine()->getManager()->flush();
    }

    protected function setUserForwardDate($object, $persist=false)
    {
        $object->setForwardedComplianceDate(new \DateTime());
	if($persist==true){
            $this->getDoctrine()->getManager()->persist($object);
            $this->getDoctrine()->getManager()->flush();
	}
    }

    /**
     * send notification email to compliance upon forwarding new application to him
     * @param RealProfile $object
     */
    protected function sendForwardEmailToCompliance($object)
    {
        $emails = $this->container->getParameter('emails');
        $user = $object->getPortalUser();
        $email_params = array(
            '%full_name%' => $user->getFirstName() . ' ' . $user->getLastName(),
            '%username%' => $user->getUsername(),
        );

        $mail_helper = $this->get('webit.mailtemplate.helper');
        $message = $mail_helper->configureMail('forwared_to_compliace_email  ', $email_params,$emails['compliance'], $this->getRequest()->getLocale());
        $mail_helper->send($message);
    }

    /**
     * re-send activation email to user if not already activated
     * @param RealUser $object
     * @param Request $request
     */
    protected function sendActivationEmail($object, Request $request)
    {
        if (!$object->getActive()) {
            $email_params = array(
                '%full_name%' => ucfirst($object->getFirstName()) . ' ' . $object->getLastName(),
                '%activation_link%' => $request->getScheme() . "://" . $request->getHost() . $this->generateUrl("activate_real", array('md5_key' => $object->getMd5Key())),
            );

            $mail_helper = $this->get('webit.mailtemplate.helper');
            $message = $mail_helper->configureMail('activation_email', $email_params, $object->getUsername(), $this->getRequest()->getLocale());
            $mail_helper->send($message);

            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'Activation email has been sent successfully');
        } else {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'The user already active');
        }
    }


    public function chartsStatsAction(){
        $start_date = date('Y-m-d H:i:s', time()-14*24*60*60); //retrieve for the past two weeks by default
        $end_date = date('Y-m-d H:i:s', time());
        
        $active_chart_data = $this->getActiveChartData($start_date, $end_date);
        $country_chart_data = $this->getCountryChartData($start_date, $end_date);
        $status_chart_data = $this->getStatusChartData('boStatus',$start_date, $end_date);
        $compstatus_chart_data = $this->getStatusChartData('compStatus',$start_date, $end_date);
        $acctype_chart_data = $this->getAccountTypeChartData($start_date, $end_date);
       // $createdat_chart_data = $this->getCreatedChartData($start_date, $end_date);
        $referer_chart_data = $this->getRefererChartData($start_date, $end_date);
        
       
        return $this->render('WebitForexBoAreaBundle::PortalUsers/chartsAndStats.html.twig',array(
            'status_chart_data'     => $status_chart_data,            
            'acctype_chart_data'    => $acctype_chart_data,
            'country_chart_data'    => $country_chart_data,
            'active_chart_data'     => $active_chart_data,
            'compstatus_chart_data' => $compstatus_chart_data,
            'createdat_chart_data'  => [],
            'referer_chart_data'    => $referer_chart_data,
        ));
    }    
    
    /**
     * getting chart data for "by status" chart
     * @param string $start_date
     * @param string $end_date
     * @return array 
     */
    protected function getStatusChartData($col = 'boStatus', $start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
                    ->getChartsData($col, $start_date, $end_date);
        
        $ret = array();
        foreach(RealProfile::$UserStatus as $status => $label){
            if(isset($query_data[$status]) === true){
                $ret[$status] = array('label' => $label, 'value' => $query_data[$status]);
            }
        }
        
        return $ret;
    }
    
    /**
     * getting chart data for "by account type" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     * @todo revise how to store account type list
     */
    protected function getAccountTypeChartData($start_date, $end_date){
//        $query_data = $this->getDoctrine()
//                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
//                    ->getChartsData('trading_account_type', $start_date, $end_date);
        
        $ret = array();
//        foreach(RealProfile::$trading_type_list as $status => $label){
//            if(isset($query_data[$status]) === true){
//                $ret[$status] = array('label' => $label, 'value' => $query_data[$status]);
//            }
//        }
        
        return $ret;
    }
    
    /**
     * getting chart data for "by country" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getCountryChartData($start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
                    ->getChartsData('p.country', $start_date, $end_date, 100);
        
        arsort($query_data);
        
        $final_data = array('other'=>0);
        $countries = \Symfony\Component\Locale\Locale::getDisplayCountries('en');
        $i=0;
        foreach($query_data as $country_code => $counter){
            if($i<5){
                if(isset($countries[$country_code])){
                    $final_data[$countries[$country_code]] = $counter;
                }else{
                    $final_data[$country_code] = $counter;
                }
            }else{
                $final_data['other'] += $counter;
            }
            $i++;
        }        
        
        return $final_data;
    }
    
    /**
     * getting chart data for "by country" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getActiveChartData($start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
                    ->getChartsData('p.active', $start_date, $end_date);
        
        $ret = array();
        foreach(PortalUser::$active_types as $status => $label){
            if(isset($query_data[$status]) === true){
                $ret[$status] = array('label' => $label, 'value' => $query_data[$status]);
            }
        }
        
        return $ret;
    }
    
    /**
     * getting chart data for "by country" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getCreatedChartData($start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
                    ->getChartsData('DATE(p.created_at)', $start_date, $end_date);
        
        uksort($query_data, function($a, $b){
            return strtotime($a) -  strtotime($b);
        });
        
        return $query_data;
    }
    
    
    /**
     * getting chart data for "by referer" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getRefererChartData($start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\RealProfile')
                    ->getChartsData('p.referer', $start_date, $end_date,10);
        
        return $query_data;
    }      

    protected function markApplicationAsSeen($object){
            $object->setBoStatus(\Webit\ForexCoreBundle\Entity\RealProfile::PENDING);
            $this->getDoctrine()->getManager()->persist($object);
            $this->getDoctrine()->getManager()->flush();

            $dispacher = $this->container->get('event_dispatcher');
            $dispacher->dispatch('user.log', new GenericEvent("New user log", array('type' => 'log', 'user' => $object->getPortalUser(), 'body' => 'The application is opened/checked')));        
    }

    protected function processUnapprovedObject($object){
            $form = $this->createFormBuilder()
                    ->add('boIdStatus', 'choice', array('required' => true, 'label' => 'ID Status', 'choices' => RealProfile::$docStatus, 'placeholder' => "Choose status..."))
                    ->add('boPorStatus', 'choice', array('required' => true, 'label' => 'Por Status', 'choices' => RealProfile::$docStatus, 'placeholder' => "Choose status..."))
                    ->add('dateOfExpirePassportNumber', 'date', array('data'=>$object->getDateOfExpirePassportNumber(),'required' => true, 'label' => 'Date Of Expire Passport Number or ID', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->add('porExpirationDate', 'date', array('required' => true, 'label' => 'Por Expiration Date', 'years' => range(date('Y') + 10, date('Y') - 10)), null)
                    ->getForm();


            if ($this->get('request')->getMethod() == "POST") {
                $form->submit($this->get('request'));
                if ($form->isValid()) {

                    if ($form->get('boIdStatus')->getData() == 1 and $form->get('boPorStatus')->getData() == 1) {

                        $object->setBoIdStatus($form->get('boIdStatus')->getData());
                        $object->setBoPorStatus($form->get('boPorStatus')->getData());
                        $object->setDateOfExpirePassportNumber($form->get('dateOfExpirePassportNumber')->getData());
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

            return $this->render('WebitForexBoAreaBundle::PortalUsers/approve.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));        
    }

    protected function processApprovedAccountForm($object){
        $user = $object->getPortalUser();
        $form = $this->createForm(new BoComplianceForms\TradingAccountsType());

        if ($this->get('request')->getMethod() == "POST") {
            $form->submit($this->get('request'));
            if ($form->isValid()) {
                if ($this->createTradingAccount($form, $user)) {
                    $dispacher = $this->container->get('event_dispatcher');
                    $dispacher->dispatch('crm.event', new GenericEvent("Convert Account", array('type' => 'convert_lead', 'site_object' => $object->getPortalUser())));
                    $this->get('session')->getFlashBag()->add('sonata_flash_success', 'account trader created successfully and email send to the user');
                    
					return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
                                        
                    
                } else {
                    $mt4_error = $this->get('session')->getFlashBag()->get('mt4_error');
                    if (is_array($mt4_error) == true) {
                        $mt4_error = current($mt4_error);
                    }
                    $this->get('session')->getFlashBag()->add('sonata_flash_error', "Cannot create trading account, reason: $mt4_error, please review form data or refer to technical support");
                }
            }
        }        
    }
    /**
     * 
     * @param PortalUser $portal_user
     * @param array $trading_info
     */
    public function createTradingAccountObject($form, PortalUser $user,$data) {
        $trading_acc = $form->getViewData();
        $trading_acc->setPortalUser($user);
        $trading_acc->setAccountType(TradingAccount::ACCOUNT_TYPE_REAL);
        $trading_acc->setLogin($data['login']);
        $comment = (!empty($form->get('comment')->getViewData())?$form->get('comment')->getViewData():
            'automatic real account creation');
        $trading_acc->setComment($comment);
        $trading_acc->setOnlinePassword($data['MasterPassword']);
        $trading_acc->setRoPassword($data['InvestorPassword']);
        if(isset($data['Agent']) && ( !is_null($data['Agent']) && !empty($data['Agent']) )){
            $trading_acc->setAgentAccount($data['Agent']);
        }
        $trading_acc->setGroupName($data['Group']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($trading_acc);     
        $em->flush();
    }

    /**
     * 
     * @param type $login
     * @param RealApplicationTranslation $trans_obj
     * @param type $api
     */
    public function SyncRealAccount($login,RealApplicationTranslation $trans_obj = null,$api,  PortalUser $portal_usr)
    {  
        $data = array();  
        $name = '';
        if(!is_null($trans_obj->getFirstName())){
            $name = trim($trans_obj->getFirstName());
        }else {
            $name = trim($portal_usr->getFirstName());
        }
        if(!is_null($trans_obj->getSecondName())){
            $name = $name.' '.trim($trans_obj->getSecondName());
        }else{
            $name = $name.' '.trim($portal_usr->getSecondName());
        }
        
        if(!is_null($trans_obj->getThirdName())){
            $name = $name.' '.trim($trans_obj->getThirdName());
        }else{
            $name = $name.' '.trim($portal_usr->getThirdName());
        }
        if(!is_null($trans_obj->getLastName())){
            $name = $name.' '.trim($trans_obj->getLastName());
        }else{
            $name = $name.' '.trim($portal_usr->getLastName());
        }
        if(!empty($name)){
            $data['name'] = trim($name);
        }
      
        
        if(!is_null($trans_obj->getCity())){
            $data['city'] = trim($trans_obj->getCity());
        }
        if(!is_null($trans_obj->getStateProvince())){
            $data['state'] = trim($trans_obj->getStateProvince());
        }
        if(!empty($data)){
            $data['login'] = $login;
            $api->updateAccount($data); 
        }
    }
    
    public function notifyApproveSubAccount($portal_user) {
        $this->createNotification($portal_user,$this->getUser(),Notification::APPROVE_SUBACCOUNT_REAL);
        $email_params = array(
            '%full_name%' => $portal_user->getFullName()
        );
        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
        $message = $mailtemplate_helper->configureMail('approve_subaccount_real',
            $email_params,
            $portal_user->getUsername(),'en'
             );
        $mailtemplate_helper->send($message);
    }

}
