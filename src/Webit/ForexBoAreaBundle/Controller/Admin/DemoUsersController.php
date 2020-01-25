<?php

namespace Webit\ForexBoAreaBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Symfony\Component\HttpFoundation\Request;
use Webit\ForexCoreBundle\Entity\DemoProfile;
use Webit\ForexCoreBundle\Helper\UtilsHelper;
use Webit\ForexCommonBundle\Exceptions as ForexExceptions;
use Webit\ForexBoAreaBundle\Form\BoCompliance as BoComplianceForms;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DemoUsersController extends Controller {

    public function createAction(Request $request = NULL) {
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

                $this->handleCreateMT4($object,$form);

                $this->sendMt4CreationEmail($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }


                $dispacher = $this->container->get('event_dispatcher');
                $dispacher->dispatch('UserCreateEvent', new GenericEvent("create user", array('type' => 'synch', 'object' => $object)));

                $this->addFlash('sonata_flash_success', 'Demo Entry created successfully');
                // redirect to edit mode
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

    public function chartsStatsAction() {
        $start_date = date('Y-m-d H:i:s', time() - 14 * 24 * 60 * 60);
        $end_date = date('Y-m-d H:i:s', time());

        $active_chart_data = $this->getActiveChartData($start_date, $end_date);
        
        $country_chart_data = $this->getCountryChartData($start_date, $end_date);
        
        $referer_chart_data = array(); //$this->getRefererChartData($start_date, $end_date);
        $createdat_chart_data = $this->getCreatedChartData($start_date, $end_date);

        return $this->render('WebitForexBoAreaBundle::DemoProfile/chartsAndStats.html.twig', array(
                    'referer_chart_data' => $referer_chart_data,
                    'country_chart_data' => $country_chart_data,
                    'active_chart_data' => $active_chart_data,
                    'createdat_chart_data' => $createdat_chart_data,
        ));
    }

    /**
     * getting chart data for "by country" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getCountryChartData($start_date, $end_date) {
        $query_data = $this->getDoctrine()
                ->getRepository('\Webit\ForexCoreBundle\Entity\DemoProfile')
                ->getChartsData('u.country', $start_date, $end_date, 100);

        arsort($query_data);

        $final_data = array('other' => 0);
        $countries = \Symfony\Component\Locale\Locale::getDisplayCountries('en');
        $i = 0;
        foreach ($query_data as $country_code => $counter) {
            if ($i < 5) {
                $final_data[$countries[$country_code]] = $counter;
            } else {
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
    protected function getActiveChartData($start_date, $end_date) {

        $query_data = $this->getDoctrine()
                ->getRepository('\Webit\ForexCoreBundle\Entity\DemoProfile')
                ->getChartsData('u.active', $start_date, $end_date);

        $ret = array();
        foreach (PortalUser::$active_types as $status => $label) {
            if (isset($query_data[$status]) === true) {
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
    protected function getCreatedChartData($start_date, $end_date) {
        $query_data = $this->getDoctrine()
                ->getRepository('\Webit\ForexCoreBundle\Entity\DemoProfile')
                ->getChartsData('DATE(u.created_at)', $start_date, $end_date);

        uksort($query_data, function($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        return $query_data;
    }

    /**
     * getting chart data for "by referer" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getRefererChartData($start_date, $end_date) {
        $query_data = $this->getDoctrine()
                ->getRepository('\Webit\ForexCoreBundle\Entity\DemoProfile')
                ->getChartsData('u.referer', $start_date, $end_date, 10);

        return $query_data;
    }

    //$this->handleCreateMT4($object);
    //$this->sendMt4CreationEmail($object);

    protected function handleCreateMT4(DemoProfile $demo_user ,$form=null) {
       
        $logger = $this->get('logger');
        $group = $this->getDemoGroup($demo_user);
        $InvestorPassword = UtilsHelper::generateRandomPassword(9);
        $MasterPassword = UtilsHelper::generateRandomPassword(9);
        
        $params = [
                "Login" => 0,
                "Name" => $demo_user->getFullName(),
                "Group" => $group,
                "Leverage" => $demo_user->getLeverage(),
                "Comment" => 'automatic demo account creation',
                "enabled" => 1,
                "enableReadOnly" => '0',
                "Country" => $demo_user->getCountryLabel(),
                "City" => $demo_user->getCity(),
                "Phone" => $demo_user->getMobileNumber(),
                "Email" => $demo_user->getUsername(),
                "Id" => $demo_user->getId(),
                "InvestorPassword" => $InvestorPassword,
                "MasterPassword" => $MasterPassword,
        ];

        $apiFactory = $this->get('trading.api.factory');
        
        $platformCode = ( !is_null($form)?$form['Platform']->getData()->getCode():$demo_user->getPlatform()->getCode() );
        try {
            /* @var $apiFactory \Webit\ForexCommonBundle\Helper\TradingAPIFactory */
            $api = $apiFactory->createAPI($platformCode, 'demo');
            $api->openConnection();
         
            $api->openAccount($params);
                        
            $trade_data = [
                'Login' => $params['login'], 
                'deposit' => $demo_user->getDeposit(),
                'OnlinePassword' =>$MasterPassword
            ];
            $this->updateDemoToDB($demo_user, $trade_data);
            $api->openConnection();
            $api->depositToAccount($params['login'],$demo_user->getDeposit());           
            
            return true;
        }
        catch (ForexExceptions\APIResponseException $ex) {
  
            $this->get('session')->getFlashBag()->set('mt4_error', $ex->getMessage());            
            $logger->error('MT5 account# for demo user:'. $demo_user .
                    ' cannot be created via API, reason:' . $ex->getMessage());
        }catch(ForexExceptions\SocketOpeningException $ex){
       
            $this->get('session')->getFlashBag()->set('mt4_error', $ex->getMessage());            
            $logger->error('MT5 account#' . $params['Login'] . ' cannot be created via API, reason:' . $ex->getMessage());            
        }     
            
    }
    
    public function getDemoGroup($user) {
        $trading_account = $user->getTradingAccountLabel();
     
        if($this->container->hasParameter('demo.'.$trading_account)){
            $trading_account = strtolower(str_replace(" ","_",$trading_account));
            $group = $this->container->getParameter('demo.'.$trading_account);
        }else{
            $group = DemoProfile::DEFAULT_GROUP_TRADING;
        }
        return $group;
    }

    protected function generateRandomPassword() {
        $length = 7;
        $random_letter = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, $length);

        return $random_letter;
    }

    /**
     * send mt4 information to client upon activation
     * @param DemoProfile $demo_usr
     */
    protected function sendMt4CreationEmail(DemoProfile $demo_usr) {
        $to = $demo_usr->getUsername();
        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
        $email_params = array(
            '%login%' => $demo_usr->getLogin(),
            '%password%' => $demo_usr->getOnlinePassword(),
            '%full_name%'=>$demo_usr->getFullName()
        );
        $message = $mailtemplate_helper->configureMail('mt5_demo_creation_success',
                $email_params, $to, 'en'
        );
        $mailtemplate_helper->send($message);
    }

    /**
     * handle update REST data into demo profile object
     * 
     * @param array $data
     * @return DemoProfile $user
     */
    protected function updateDemoToDB(DemoProfile $demo_user, $data) {
        
        $em = $this->getDoctrine()->getManager();
        $this->hydrateFromArray($demo_user, $data);
        $demo_user->setActive(true);

        $em->persist($demo_user);
        $em->flush();
        return $demo_user;
    }

    /**
     * hydrating object from array
     * I cannot find this utility in Doctrine2 documentation
     * @param Entity $entity
     * @param array $data
     */
    protected function hydrateFromArray(&$entity, $data) {
        foreach ($data as $property => $value) {
            $method = sprintf('set%s', ucwords($property));

            // use the method as a variable variable to set your value
            if (method_exists($entity, $method) == true) {
                $entity->$method($value);
            }
        }
    }
    
    public function openDemoTradingAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }
        
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }


        $form = $this->createForm(new \Webit\ForexBoAreaBundle\Form\NewDemoAccountType());
        
        if ($this->getRequest()->getMethod() == "POST") {
            $form->submit($this->getRequest());
            if ($form->isValid()) {
                $this->hydrateFromArray($object, $form->getViewData());
                $mt5_platform = $this->getDoctrine()
                ->getRepository("WebitForexCoreBundle:Platform")->findOneBy(['code'=>'mt5','isActive'=>true]);
                $object->setPlatform($mt5_platform);
                $this->handleCreateMT4($object);
                $this->sendMt4CreationEmail($object);
                $this->addFlash('sonata_flash_success', 'Trading Account Created successfully');
                // redirect to edit mode
                return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
                
            } else {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'Please check form errors');
            }
        }

        return $this->render('WebitForexBoAreaBundle::sonata/DemoProfile/createSubAccount.html.twig', array('form' => $form->createView(), 'object' => $object, 'action' => 'approve_application'));
    }
    
    public function demoResendTradingAction($id) {
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        
        $this->sendMt4CreationEmail($object);
        $this->addFlash('sonata_flash_success', 'Trading Account Resend successfully');
        
        return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
        
    }
    
      public function demoAddNoteAction($id)
    {
 
       $object = $this->admin->getObject($id);
       if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
   
        $form = $this->createForm(new \Webit\UserLogBundle\Form\UserLogType());

        if ($this->getRequest()->getMethod() == 'POST') {
            $form = $form->bind($this->getRequest());
            if ($form->isValid()) {
                $UserLog = new \Webit\UserLogBundle\Entity\UserLog();
                $UserLog->setBody($form->get('body')->getData());
                $UserLog->setSubject("Log Added manually");
                $UserLog->setFosUser($this->getUser());
                $UserLog->setDemoUser($object);
                $UserLog->setCreatedAt(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($UserLog);
                $em->flush();
      
                $this->addFlash('sonata_flash_success', 'New log has been added successfully');

                return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
            }
        }


        return $this->render("WebitForexBoAreaBundle::sonata/DemoProfile/add_new_log.html.twig", array('form' => $form->createView(), 'object' => $object, 'id' => $id));
    }


}
