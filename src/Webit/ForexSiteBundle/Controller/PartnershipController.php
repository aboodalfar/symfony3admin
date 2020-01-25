<?php

namespace Webit\ForexSiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Webit\ForexCoreBundle\Entity\Partnership;
use Webit\ForexSiteBundle\Controller\BaseController;
use Webit\ForexSiteBundle\Form\PartnershipType;

class PartnershipController extends BaseController
{
    public function registrationAction(Request $request, $type)
    {
        $slug=Partnership::$slug[$type];
        $locale = $request->getLocale();
        $page= $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
             ->getContentBySlug($slug,$locale);
 
        $partnership = new \Webit\ForexCoreBundle\Entity\Partnership();
        $success = false;
        $form = $this->createForm(PartnershipType::class, $partnership);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->get($form->getName());
            $partnership->setPhoneNumber($data['phone_code'].$data['phoneNumber']);
            $this->saveInfoToDB($partnership,$data);
            //$this->sendPartnershipEmail($partnership);
            $success = true;
        }
        if($request->isXmlHttpRequest()){
            return $this->render('WebitForexSiteBundle::Partnerships/ib_form.html.twig', array(
            'form' => $form->createView(), 
            'success' => $success, 
            'slug'=>null,
            'page'=>$page
            ));
        }
 
        
        return $this->render('WebitForexSiteBundle::Partnerships/base.html.twig', array(
            'form' => $form->createView(), 
            'success' => $success, 
            'slug'=>null,
            'page'=>$page,
            'routeName'=>$request->get('_route'),
            '_locale'=>$locale
            ));
    }
    

    

    /**
     * send activation email to user after he registers to the Partnership form
     * @param Partnership $partnership
     * @param Request $request
     * */
    protected function sendActivationEmail($partnership, Request $request)
    {
        $activation_link = $request->getScheme() . "://" . $request->getHost() . $this->generateUrl('PartnershipActivation', array('md5_key' => $partnership->getMd5Key()));

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());
        $message = $message_helper->configureMail('partnership_activation_email', array(
            '%full_name%' => $partnership->getFirstName() . ' ' . $partnership->getLastName(),
            '%activation_link%' => $activation_link,
            '%partnership_type%' => Partnership::$partnership_type[$partnership->getType()],
                ), $partnership->getEmail()
        );
        $this->get('mailer')->send($message);
    }

    /**
     * Saving Introducing Broker partnership details into database
     * @param Partnership $partnership
     * @param smallint $type type of partnership IB/WL
     */
    public function saveInfoToDB($partnership)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($partnership);
        $em->flush();
    }

    /**
     * action used to mark partnersh user as activated, send success emails
     * @param Request $request
     * @param string $md5_key
     * @return RedirectResponse
     * */
    public function activatePartnershipAction(Request $request, $md5_key)
    {
        $user = $this->getUserByMd5Token($md5_key);

        $this->setUserAsActive($user);
        //$this->generateSavePdf($user);
        $this->sendSuccessRegPartnershipEmails($user);
        $this->sendPartnershipRegBoEmail($user);

        return $this->redirect($this->generateUrl('system_message', array('message_type' => 'success', 'message' => 'PARTNERSHIP_CREATED_SUCCESSFULLY_MSG')));
    }

    /**
     * find Partnership user by md5_key, if not found 404 exception is thrown
     * @param string $md5_key
     * @throws HTTPNotFoundException
     * @return Partnership
     * */
    protected function getUserByMd5Token($md5_key)
    {
        $user = $this->getDoctrine()->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')->findOneBy(array("md5_key" => $md5_key));
        if (!$user) {
            throw $this->createNotFoundException('404 Invalid User');
        }

        return $user;
    }

    /**
     * set Partnership user as active, generate random password and empty md5_key
     * called upon initial activation of the registered user
     * @param Partnership $user
     * */
    protected function setUserAsActive($user)
    {
        $user->setActive(Partnership::Active);
        //$user->setMd5Key('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }

    /**
     * send emails after sucessful registration activation
     * @param Partnership $user
     * */
    protected function sendSuccessRegPartnershipEmails(Partnership $user)
    {
        $emails = $this->container->getParameter('emails');
        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());

        $email_params = array(
            '%full_name%' => $user->getFirstName() . ' ' . $user->getLastName(),
        );

        $message = $message_helper->configureMail('partnership_user_created', $email_params, $emails['finance'], $this->getRequest()->getLocale());

        $this->get('mailer')->send($message);
    }

    /**
     * send email to Backoffice department upon registered partnership user activates his account for the first time
     * @param Partnership $user
     * */
    protected function sendPartnershipEmail(Partnership $user)
    {
        $emails = $this->container->getParameter('emails');
        $company_info = $this->container->getParameter('company_info');
        $mail_params = array(
            '%type%' => Partnership::$partnership_type[$user->getType()],
            '%full_name%' => $user->getFullName(),
            '%email%' => $user->getEmail(),
            '%phone%' => $user->getPhoneNumber(),
            '%country%' => $user->getCountryLabel(),
            '%trading_account%' => $user->getTradingAccountNumber(),
            '%brokers_worked_with%' => $user->getBrokersWorkedWith(),
        );

        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine(),$company_info);
        $message = $message_helper->configureMail('partnership_registration_bo', $mail_params, $emails['bo'], $this->getRequest()->getLocale());
        $this->get('mailer')->send($message);
    }

    /**
     * save generated pdf to partnership user
     * @param Partnership $partnership
     */
    protected function generateSavePdf(Partnership $partnership)
    {
        $pdf_path = $this->generatePdf($partnership);
        $partnership->setPdfDoc("/uploads/pdf_partnership/" . basename($pdf_path));

        $em = $this->getDoctrine()->getManager();
        $em->persist($partnership);
        $em->flush();
    }

    /**
     * generate PDF for the partnership user
     * @param Partnership
     * @return string $full_path
     */
    protected function generatePdf(Partnership $user)
    {
        $html = $this->renderView('WebitForexCoreBundle::Pdf/partnership_user.html.twig', array('user' => $user));

        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/pdf_partnership/';
        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        $file_name = md5(time() . $user->getId()) . '.pdf';

        $pdfGenerator = $this->get('knp_snappy.pdf');
        $pdfGenerator->getInternalGenerator()->setTimeout(10000);
        $pdfGenerator->generateFromHtml($html, $dir . $file_name, array(), true);

        $full_path = $dir . $file_name;

        return $full_path;
    }
    
}
