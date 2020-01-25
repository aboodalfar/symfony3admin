<?php

namespace Webit\ForexBoAreaBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
#use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Webit\ForexCoreBundle\Entity\RealProfileEdit;
use Webit\ForexBoAreaBundle\Form\BoCompliance as BoComplianceForms;
use Webit\ForexCoreBundle\Entity\Notification;
use Webit\ForexCoreBundle\Entity\RealProfile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * RealProfileEdit controller.
 *
 * @Route("/realprofileedit")
 */
class RealProfileEditController extends BaseController
{
    public function ApproveChangesAction($id)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        try{
            $status = $this->reflectChangesOnMetaTrader($object);
            if ($status) {
                if (!is_null($object->getUsername())) {
                    // send email to old email
                    $this->sendChangeApprovalEmail($object);
                }
                $this->copyChangesToUser($object);
                $this->sendChangeApprovalEmail($object);
                $this->createNotification($object->getPortalUser(), $this->getUser(),
                        Notification::NOTIFICATION_APPROVE_EDIT_PROFILE);
                $this->get('session')->getFlashBag()
                        ->add('sonata_flash_success', 'This changes has been approved successfully');
            }else {
                $this->addFlash('sonata_flash_error', 'This changes cannot be applied');
            }
   
        }catch(\Exception $ex){
            $this->addFlash('sonata_flash_error', 'This changes cannot be applied, error: '.$ex->getMessage());
        }
        
        return $this->render($this->admin->getTemplate('show'), array(
                    'action' => 'show',
                    'object' => $object,
                    'elements' => $this->admin->getShow(),
        ));
    }

    public function RejectChangesAction($id = null)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $request = $this->getRequest();
        $form = $this->createForm(new BoComplianceForms\RejectApplicationType());

        if ($request->getMethod() == "POST") {

            $form->submit($request);
            if ($form->isValid()) {

                $this->setEditAsRejected($object);
                $this->sendChangeRejectionEmail($object, $form);
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'This changes has been rejected');
                $param = array('%reason%'=>$form->get('reason')->getData());
                $this->createNotification($object->getPortalUser(),$this->getUser(),Notification::NOTIFICATION_REJECT_EDIT_PROFILE,$param);           
                return $this->render($this->admin->getTemplate('show'), array(
                            'action' => 'show',
                            'object' => $object,
                            'elements' => $this->admin->getShow(),
                ));
            }
        }

        return $this->render('WebitForexBoAreaBundle::sonata/PortalUsers/rejectProfileChanges.html.twig', array(
                    'action' => 'reject',
                    'object' => $object,
                    'form' => $form->createView(),
        ));
    }

      /**
     * copy data from "edit" object to the user profile
     * @param RealProfileEdit $object
     */
    protected function copyChangesToUser(RealProfileEdit $object) {

        /*@var $portal_user \Webit\ForexCoreBundle\Entity\PortalUser */
        $portal_user = $object->getPortalUser();
        $realProfile = $portal_user->getRealProfile();
        $personalID = null;
        if($realProfile->getIndividualOrCorporations() == RealProfile::TypeIndividual){
            if(!is_null($object->getCity())){
               $realProfile->setCityTown($object->getCity());
            }
            
            if(!is_null($object->getPersonalId())){
               $realProfile->setPassportNumber($object->getPersonalId());
            }
            if(!is_null($object->getPostalCode())){
               $realProfile->setPostalCode($object->getPostalCode());
            }
            if(!is_null($object->getCountry())){
                $portal_user->setCountry($object->getCountry());
            }
        }else {
            $realProfileCorporation = $realProfile->getRealProfileCorporation();
            if(!is_null($object->getCity())){
               $realProfileCorporation->setCompanyCity($object->getCity());
            }
            if(!is_null($object->getPostalCode())){
               $realProfileCorporation
                       ->setCompanyPostalZip($object->getPostalCode());
            }
            if(!is_null($object->getCountry())){
                $realProfileCorporation->setCompanyResidenceCountry($object->getCountry());
                $portal_user->setCountry($object->getCountry());
            }
            if(!is_null($object->getPersonalId())){
               $result = $realProfileCorporation->getBeneficialOwners2();
               $result->count() > 0 ?$result->first()->setIdNumber($object->getPersonalId()):'' ;
            }
        }
       
        if(!is_null($object->getMobileNumber())){
            $portal_user->setMobileNumber($object->getMobileNumber());
        }
        
        if(!is_null($object->getAlternativeEmail())){
            $portal_user->setAlternativeEmail($object->getAlternativeEmail());
        }
        if(!is_null($object->getUsername())){
            $portal_user->setUsername($object->getUsername());
        }
        
        if(!is_null($object->getDocumentId())){
            $realProfile->setDocumentId($object->getDocumentId());
        }
        if(!is_null($object->getDocumentId2())){
            $realProfile->setDocumentId2($object->getDocumentId2());
        }
        if(!is_null($object->getDocumentPor())){
            $realProfile->setDocumentPor($object->getDocumentPor());
        }
        if(!is_null($object->getDocumentPor2())){
            $realProfile->setDocumentPor2($object->getDocumentPor2());
        }
//        if(!is_null($object->getClientSignature())){
//            $realProfile->setClientSignature($object->getClientSignature());
//        }
        
        $object->setStatus(RealProfileEdit::STATUS_APPROVED);
        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->persist($portal_user);
        $em->flush();
    }

    protected function setEditAsRejected($object)
    {
        $object->setStatus(RealProfileEdit::STATUS_REJECTED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($object);
        $em->flush();
    }

    /**
     * send email to the user to notify him of successful apply of data
     * @param RealProfileEdit
     */
    protected function sendChangeApprovalEmail(RealProfileEdit $object)
    {
        $user = $object->getPortalUser();
        $email_params = array('%full_name%' => $user->getFirstName());
        
        $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
        $message = $mailtemplate_helper->configureMail('approve_profile_changes', 
                $email_params, 
                $user->getUsername()
                );
         $mailtemplate_helper->send($message);
    }


    /**
     * send email to the user to notify him of rejection of data change request
     * @param RealProfileEdit profile edit option
     * @param \Symfony\Component\Form\Form $form reason of rejection
     */
    protected function sendChangeRejectionEmail(RealProfileEdit $object, $form)
    {        
        //if($form->get('notify_client')->getData()){
            
            $user = $object->getPortalUser();
            $email_params = array('%full_name%' => $user->getFirstName(), '%reason%'=> $form->get('reason')->getData() );
            
            $mailtemplate_helper = $this->get('webit.mailtemplate.helper');
            
            $message = $mailtemplate_helper->configureMail('reject_profile_changes', 
                    $email_params, 
                    $user->getUsername()
                        );
            $mailtemplate_helper->send($message);
        //}
    }

     protected function getBranchsEmail($portalUserObj ,$emailType = '') {
        
            if ($portalUserObj->getRealProfile()) {
                /* @var $branch \Webit\ForexCoreBundle\Entity\BranchesModule\Branches */
                $branch = $portalUserObj->getRealProfile()->getBranches();
                if ($branch) {
                    if ($emailType) {
                        switch ($emailType):
                            case 'backoffice': return $branch->getEmailBackoffice();
                                break;
                            case 'compliance': return $branch->getEmailCompliance();
                                break;
                            case 'finance': return $branch->getEmailFinance();
                                break;
                        endswitch;
                    }
                }
         }

        return false;
    }

    /**
     * reflecting changes on account info to mt5 api
     * @param RealProfileEdit $object
     */
    protected function reflectChangesOnMetaTrader(RealProfileEdit $object){
        $status = true;
        $tradingAccountsNumbers = $object->getPortalUser()->getRealTradingAccounts();
        if(!empty($tradingAccountsNumbers)){
            $logger = $this->get('logger');
            $tradingAccountsArr = explode(',',$tradingAccountsNumbers);
            $apiFactory = $this->get('trading.api.factory');
            
            $api = $apiFactory->createAPI('mt5', 'real');
            $data = array();
            if(!is_null($object->getCountryLabel())){
                $data['Country'] = $object->getCountryLabel();
            }
            if(!is_null($object->getCity())){
                $data['City'] = $object->getCity();
            }
            if(!is_null($object->getMobileNumber())){
                $data['Phone'] = $object->getMobileNumber();
            }
            if(!is_null($object->getPostalCode())){
                $data['zipcode'] = $object->getPostalCode();
            }
            if(!is_null($object->getUsername())){
                $data['Email'] = $object->getUsername();
            }  
            if(!is_null($object->getPersonalId())){
                $data['id'] = $object->getPersonalId();
            }  
            
            if(!empty($data)){                
                foreach($tradingAccountsArr as $login){ 
                    $data['Login'] = trim($login);
                    $api->OpenConnection(); 
                    try{
                       $response =  $api->updateAccount($data); 
                       if(isset($response['status']) && $response['status'] == 'success'){
                           $status = true;
                       }
                    }
                    catch (\Exception $exc){
                        $status = false;
                        $logger->error('cannot updateAccount on Meta Trader:#'.$login.' .Error: '
                                . $exc->getMessage());
                    }
                    sleep(3);
                }
            }
        } 
        
        return  ( isset($status) && $status == true  ? true : false );
        
    }    
}
