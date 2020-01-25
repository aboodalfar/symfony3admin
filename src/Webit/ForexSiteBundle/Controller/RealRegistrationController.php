<?php

namespace Webit\ForexSiteBundle\Controller;

use Webit\ForexSiteBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexSiteBundle\Form as forms;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webit\ForexCoreBundle\Entity\Setting;

class RealRegistrationController extends BaseController
{
    /**
     * handle real account registration step 1
     * @param Request $request
     * @return Response|RedirectResponse $response
     **/
    public function showRegisterFormAction(Request $request,$step=1)
    {  
        //$request->getSession()->set('portal_id',null);
        if($step > 1 && 0){
            $portalUser = $this->getPortalUserFromSession();
            $userStep = $portalUser->getRegStep();
            if($userStep < $step ) {
                return $this->redirect($this->generateUrl('realRegistration',array('step'=>$userStep)));
            }
        }
        $page= $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
             ->getContentBySlug2(['real-account']);

        return $this->render('WebitForexSiteBundle::Registration/real.html.twig', array(
            'step' => (int)$step,
            'page'=>$page,
            '_locale'=>$request->getLocale()
            ));
        
    }
    
    public function step1Action(Request $request,$step=1) {
        $success = false;
        $portalUser = $this->getPortalUserFromSession();
        $form = $this->createForm(forms\RealStep1Registration::class,$portalUser, array(
            'userStep' => $portalUser->getRegStep()
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $success=true;
            $data = $request->get($form->getName());
            $portalUser->setMobileNumber($data['phone_code'].$data['mobile_number']);
            $portalUser->setCommunicationLanguage($request->getLocale());
            $updateStep = $this->updateUserStep($portalUser->getRegStep(),1);
            $portalUser->setRegStep($updateStep);
            $portalUser->setAccountType(PortalUser::RealAccount);
            $portalUser  =  $this->saveReal1ToDB($portalUser);
            $this->sendActivationEmail($portalUser);
            $this->get('session')->set('portal_id',$portalUser->getId());
            return $this->forward('WebitForexSiteBundle:RealRegistration:step2');
        }
        return $this->render('WebitForexSiteBundle::Registration/step1Form.html.twig', array(
            'form' => $form->createView(),
            'success'=>$success
        ));
    }
    public function step2Action(Request $request) {
        $success = false;
        $portalUser = $this->getPortalUserFromSession();
        $realUser = $this->getRealUserFromObject();
        $form = $this->createForm(forms\RealStep2Registration::class,$realUser,
                ['userStep' => $portalUser->getRegStep()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $success=true;
            $updateStep = $this->updateUserStep($portalUser->getRegStep(),2);
            $portalUser->setRegStep($updateStep);
            $realUser->setPortalUser($portalUser);
            $realUser  =  $this->saveReal1ToDB($realUser,$portalUser);
            return $this->forward('WebitForexSiteBundle:RealRegistration:step3');
        }
        return $this->render('WebitForexSiteBundle::Registration/step2Form.html.twig', array(
            'form' => $form->createView(),
            'success'=>$success
        ));  
    }
    
    public function step3Action(Request $request) {
        $success = false;
        $portalUser = $this->getPortalUserFromSession();
        $form = $this->createForm(forms\RealStep3Registration::class,$portalUser,[
            'answer_q'=>$this->get('session')->get('answer_q'),
            'userStep'=>$portalUser->getRegStep()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $success=true;
            $updateStep = $this->updateUserStep($portalUser->getRegStep(),3);
            $portalUser->setRegStep($updateStep);
            $this->get('session')->set('answer_q',$form->get('security_question_answer')->getData());
            $this->saveReal1ToDB($portalUser);
            return $this->forward('WebitForexSiteBundle:RealRegistration:step4');
        }
        return $this->render('WebitForexSiteBundle::Registration/step3Form.html.twig', array(
            'form' => $form->createView(),
            'success'=>$success
        ));
    }
    
    public function step4Action(Request $request) {
        $success = false;
        $portalUser = $this->getPortalUserFromSession();
        $realUser = $portalUser->getRealProfile();

        $form = $this->createForm(forms\RealStep4Registration::class,$realUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $success=true;
            $updateStep = $this->updateUserStep($portalUser->getRegStep(),4);
            $portalUser->setRegStep($updateStep);
            $this->saveReal1ToDB($realUser,$portalUser);
            return $this->forward('WebitForexSiteBundle:RealRegistration:step5');
        }
        return $this->render('WebitForexSiteBundle::Registration/step4Form.html.twig', array(
            'form' => $form->createView(),
            'success'=>$success
        ));
    }
    
    
    public function step5Action(Request $request) {
        $success = false;
        $portalUser = $this->getPortalUserFromSession();
        $realUser = $portalUser->getRealProfile();

        $form = $this->createForm(forms\RealStep5Registration::class,$realUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $success=true;
            $updateStep = $this->updateUserStep($portalUser->getRegStep(),5);
            $portalUser->setRegStep($updateStep);
            $this->saveReal1ToDB($realUser,$portalUser);
            try{
               $this->handleEndRealProcess($portalUser); 
               $this->get('session')->remove('portal_id');
            }
            catch (\Exception $exc){
                $logger = $this->get('logger');
                $logger->critical('step5 real has error:'. $exc->getMessage());
            }
            
        }
        return $this->render('WebitForexSiteBundle::Registration/step5Form.html.twig', array(
            'form' => $form->createView(),
            'success'=>$success
        ));
    }
    
     /**
     * send activation email and send login ,pass member area to user after he registers to the website
     * Moved to RestRegController.php 
     * @param PortalUser $portal_usr
     * @param Request $request
     * */

     protected function sendActivationEmail(PortalUser $object)
    {
        if (!$object->getActive()) {
            $email_params = array(
                '%full_name%' => ucfirst($object->getFullName()),
                '%activation_link%' => $this->generateUrl("activate_real",
                        array('md5_key' => $object->getMd5Key()),UrlGeneratorInterface::ABSOLUTE_URL)
            );

            $mail_helper = $this->get('webit.mailtemplate.helper');

            $message = $mail_helper
                    ->configureMail('activation_email', $email_params, $object->getUsername(), 
                            $object->getCommunicationLanguage());
            $mail_helper->send($message);
        } 
    }

    /**
     * handle end of real account registration and activation; includes:
     * create members area access, create mt4 account, send notification to bo and to user... etc.
     * @param PortalUser $user
     */
    protected function handleEndRealProcess(PortalUser $user)
    {
       
        $this->generateSavePdf($user);
        $this->sendEmailToBo($user);
        $this->sendRegistrationsSuccessEmail($user);
        
    }

    /**
     * setting random password to user profile
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $user
     * @return string
     */
    protected function setGeneratePassword(PortalUser $user)
    {
        $password = \Webit\ForexCoreBundle\Helper\UtilsHelper::generateRandomPassword(7);
        $user->setPassword($password);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $password;
    }

   


    /**
     * action to handle real activation process
     * @param Request $request
     * @return RedirectResponse
     **/
    public function activateAccountAction(Request $request, $md5_key)
    {
        /*if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();
        }*/
        $user = $this->getUserByMd5Token($md5_key);
        if($user){
            $this->setUserAsActive($user, $plain_password);
            return new Response(
                 '<p>Thank You</p>'
             );
        }else{
            return new Response(
                '<p>The Link expired</p>'
            );
        }

        

       
    }

   

    /**
     * find portal user by md5_key, if not found 404 exception is thrown
     * @param string $md5_key
     * @throws HTTPNotFoundException
     * @return PortalUser
     * */
    protected function getUserByMd5Token($md5_key)
    {
        $user = $this->getDoctrine()->getRepository("WebitForexCoreBundle:PortalUser")->findOneBy(array("md5_key" => $md5_key));
        if (!$user) {
            throw $this->createNotFoundException('404 Invalid User');
        }

        return $user;
    }



    /**
     * set portal user as active, generate random password and empty md5_key
     * called upon initial activation of the registered user
     * @param PortalUser $user
     * @param string $plain_password passed by reference in order to get its non-encrypted value
     * @return string $password
     * */
    protected function setUserAsActive(PortalUser $user, &$plain_password)
    {
        $user->setActive(PortalUser::Active);
//        $plain_password = $password = \Webit\ForexCoreBundle\Helper\UtilsHelper::generateRandomPassword(7);;
//        $user->setPassword($password);
        $user->setMd5Key('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $password;
    }


    /**
     * save real registration data on step1 into database
     */
    protected function saveReal1ToDB($object,$object2=null)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        if(!is_null($object2)){
            $em->persist($object2);
        }
        $em->flush();
        return $object;
    }

  
    protected function sendRegistrationsSuccessEmail(PortalUser $portal_usr) {
        
       $platformLinks = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Setting')
                ->getByKeys(array_keys(Setting::platforms_links), 'array');

        $label = '';
        foreach ($platformLinks as $key => $platformLink) {
            if (!is_null($platformLink['value']) && !empty($platformLink['value'])) {
                $val = $platformLink['value'];
                $label.=  str_replace('_', ' ', $platformLink['key']) . " : <a href='$val'>" .$val.'</a>' . '<br/>'. '<br/>';
            }
            unset($platformLink);
        }
        $email_params = array(
            '%full_name%' => $portal_usr->getFullName(),
            '%links%' => $label
        );

        $mail_helper = $this->get('webit.mailtemplate.helper');

        $message = $mail_helper
                ->configureMail('demoRegistrationsSuccess', $email_params, 
                        $portal_usr->getUsername(), $portal_usr->getCommunicationLanguage());
        $mail_helper->send($message);
    }
    protected function sendEmailToBo(PortalUser $portal_usr) {
        $emails = $this->container->getParameter('emails');
     
        $boEmail = $emails['back_office'];

        $pdf_path = $this->get('kernel')->getRootDir() .
                '/../web' . $portal_usr->getPdfDoc();
        $message_helper = $this->get('webit.mailtemplate.helper');

         $message = $message_helper
                    ->configureMail('newuser_registered_to_bo', [], $boEmail,
                           'en');
        $message->attach(\Swift_Attachment::fromPath($pdf_path));
        $message_helper->send($message);
    }

    /**
     * save generated pdf to the user record page
     * @param PortalUser $portal_usr
     */
    protected function generateSavePdf(PortalUser $portal_usr)
    {
        $pdf_path = $this->generatePdf($portal_usr);
        $portal_usr->setPdfDoc("/uploads/userDocuments/" . basename($pdf_path));

        $em = $this->getDoctrine()->getManager();
        $em->persist($portal_usr);
        $em->flush();
    }

    /**
     * generate PDF for the real user
     * @param PortalUser
     * @return string $full_path
     */
    protected function generatePdf(PortalUser $user)
    {
        $html = $this->renderView('WebitForexSiteBundle::Registration/pdf.html.twig',
                array('portalUser' => $user));

        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/userDocuments/';
        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        $file_name = md5(time() . $user->getId()) . '.pdf';

        $pdfGenerator = $this->get('knp_snappy.pdf');
        //$pdfGenerator->getInternalGenerator()->setTimeout(10000);
        $pdfGenerator->generateFromHtml($html, $dir . $file_name, array(), true);

        $full_path = $dir . $file_name;
        return $full_path;
    }


    /**
     * creating new mt4 demo account for user via connecting to MT4 API
     * @param \Webit\ForexCoreBundle\Entity\PortalUser $portal_user
     * @return TradingAccount|NULL $trading_acc
     */
    protected function createMt4Demo(PortalUser $portal_user)
    {
        $api_info   = $this->container->getParameter('mt4_api');
        $api_server = $api_info['demo']['host'];
        $api_port   = $api_info['demo']['port'];

        $params = array(
            'login'   => 0, //automatic generation
            'comment' => 'automatic demo account creation',
            'group'   => 'demoforexZB', 
            'name'    => $portal_user->getFullName(),
            'leverage'=> '100',
            'email'   => $portal_user->getUsername(),
            'country' => $portal_user->getCountryLabel(),
            'city'    => $portal_user->getCity(),
            'phone_number' => $portal_user->getMobileNumber(),
            'address' => 'address',
            'postal_code' => '0000',
            'id'      => rand(1,1000),
            'currency' => 'USD',
            'passport_id' => $portal_user->getId(),
        );

        $logger = $this->get('logger');

        try {
            $api_helper = new \Webit\ForexCoreBundle\Helper\MT4API($this->getDoctrine()->getManager(), $portal_user->getId());
            $api_helper->OpenConnection($api_server, $api_port);
            $api_helper->OpenAccount($params, false);

            $params['account_type'] = TradingAccount::demo_account;
            $trading_acc = $this->getDoctrine()->getRepository('\Webit\ForexCoreBundle\Entity\TradingAccount')->createNewAccount($portal_user, null, $params);
            $api_helper->depositDemoUser($trading_acc->getLogin(),5000);

            return $trading_acc;

        } catch (\Exception $ex) {
            $logger->error('cannot create mt4 demo account, error: ' . $ex->getMessage());
            $this->sendAPIErrorNotification($portal_user, $ex);
        }
    }

    /**
     * send notification email in case of MT4 API error to technical and backoffice department
     * @param PortalUser $portal_user
     * @param \Exception $ex
     */
    protected function sendAPIErrorNotification(PortalUser $portal_user, \Exception $ex)
    {
        $emails = $this->container->getParameter('emails');
        $mail_params = array(
            '%full_name%' => $portal_user->getFullName(),
            '%email%' => $portal_user->getUsername(),
            '%user_type%' => $portal_user->getAccountTypeLabel(),
            '%error%' => $ex->getMessage(),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('mt4_api_error', $mail_params, $emails['dev_notification'], 'en'); //allway send in English format
        $this->get('mailer')->send($message);

        //$message = $message_helper->configureMail('mt4_api_error', $mail_params, $emails['bo'], 'en');
        //$this->get('mailer')->send($message);
    }


    private function getPortalUserFromSession() {
        $id = $this->get('session')->get('portal_id');
        if($id){
            $portal_user = $this->getDoctrine()
                ->getRepository('Webit\ForexCoreBundle\Entity\PortalUser')->find($id);
        }else{
            $portal_user = new PortalUser();
        }
        return  $portal_user;
    }
    
    private function getRealUserFromObject() {
        $portalUser = $this->getPortalUserFromSession();
        if($portalUser && $portalUser->getRealProfile()){
            $real_user = $portalUser->getRealProfile();
        }else{
            $real_user = new RealProfile();
        }
        return  $real_user;
    }
  //  3 2
     protected function updateUserStep($userStep,$currentStep) {
        $stepNumberByType = 5;
        if($userStep > $currentStep){
            return $userStep;
        }
        elseif($stepNumberByType > $userStep){
            return $userStep+1;
        }
    }
    
    public function showRegisterForm2Action(Request $request)
    {  
       $form = $this->createForm(forms\Landing\OneType::class);
       return $this->render('WebitForexSiteBundle::Registration/real2.html.twig',array(
           'form' => $form->createView()));
    }
    public function showLoginPageAction(Request $request)
    {  
       $form = $this->createForm(forms\LoginType::class);
       return $this->render('WebitForexSiteBundle::Registration/login.html.twig',array(
           'form' => $form->createView()));
    }
}
