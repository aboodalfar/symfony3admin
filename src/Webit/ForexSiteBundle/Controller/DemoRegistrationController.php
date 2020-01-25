<?php namespace Webit\ForexSiteBundle\Controller;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Webit\ForexSiteBundle\Form\DemoRegistration;
use Webit\ForexCoreBundle\Entity\DemoProfile;
use Webit\ForexCoreBundle\Entity\Setting;


class DemoRegistrationController extends Controller{
    
     /**
     * handle normal (full) demo account registration
     * @todo implement
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function registrationAction(Request $request)
    {

        
        $success = false;
        $demoProfile = new \Webit\ForexCoreBundle\Entity\DemoProfile();
        $form = $this->createForm(DemoRegistration::class, $demoProfile);
        $page= $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
             ->getContentBySlug2('demo-account');
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->get($form->getName());
            $demoProfile->setMobileNumber($data['phone_code'].$data['mobile_number']);
            $demoProfile->setCommunicationLanguage($request->getLocale());
            $demoProfile->setActive(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($demoProfile);
            $em->flush();
            $success = true;
            $this->sendRegistrationsSuccessEmail($demoProfile,$request);
        }
        
        return $this->render('WebitForexSiteBundle::Registration/demo.html.twig', array(
            'form' => $form->createView(), 
            'success' => $success,
            'page'=>$page,
            '_locale'=>$request->getLocale()
                ));
    }
    
    
     protected function sendActivationEmail(DemoProfile $object,$request)
    {
        if (!$object->getActive()) {
            $email_params = array(
                '%full_name%' => ucfirst($object->getFirstName()) . ' ' . $object->getLastName(),
                '%activation_link%' => $request->getScheme() . "://" . $request->getHost() .
                $this->generateUrl("activate_demo", array('md5_key' => $object->getMd5Key())),
                '%username%'  => $object->getUsername()
            );

            $mail_helper = $this->get('webit.mailtemplate.helper');

            $message = $mail_helper
                    ->configureMail('demo_activation_email', $email_params, $object->getUsername(), 
                            $object->getCommunicationLanguage());
            $mail_helper->send($message);
        } 
    }
    
    protected function sendRegistrationsSuccessEmail(DemoProfile $object, $request) {
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
            '%full_name%' => ucfirst($object->getFirstName()) . ' ' . $object->getLastName(),
            '%links%' => $label
        );

        $mail_helper = $this->get('webit.mailtemplate.helper');

        $message = $mail_helper
                ->configureMail('demoRegistrationsSuccess', $email_params, 
                        $object->getUsername(), $object->getCommunicationLanguage());
        $mail_helper->send($message);
    }

    /**
     * action used to mark demo account as activated, create demo mt4 and send success emails
     * @param Request $request
     * @param string $md5_key
     * @return RedirectResponse
     * */
    public function activateDemoAction(Request $request, $md5_key)
    {
        $user = $this->getUserByMd5Token($md5_key);
        $plain_password = '';
        $user->setActive(true);
        $user->setMd5Key(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('system_message', 
                array('message_type' => 'success', 'message' => 'DEMO_CREATED_SUCCESSFULLY_MSG')));
    }
    
    /**
     * find portal user by md5_key, if not found 404 exception is thrown
     * @param string $md5_key
     * @throws HTTPNotFoundException
     * @return DemoProfile
     * */
    protected function getUserByMd5Token($md5_key)
    {
        $user = $this->getDoctrine()->getRepository("WebitForexCoreBundle:DemoProfile")
                ->findOneBy(array("md5_key" => $md5_key));
        if (!$user) {
            throw $this->createNotFoundException('404 Invalid User');
        }

        return $user;
    }
    
    
}