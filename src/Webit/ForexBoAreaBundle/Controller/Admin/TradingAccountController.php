<?php

namespace Webit\ForexBoAreaBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;

use \Webit\ForexCoreBundle\Entity\TradingAccount;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use Webit\ForexCommonBundle\Exceptions as ForexExceptions;

class TradingAccountController extends Controller {

    public function editAction($id = null, Request $request = null) {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
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

                $this->synchAccountDBToAPI($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                // redirect to edit mode
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

        return $this->render($this->admin->getTemplate($templateKey), array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
        ));
    }

    
    public function refreshAccountAction($id) {
        // the key used to lookup the template
        $templateKey = 'show';

        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $this->refreshAccountFromAPI($object);        
        // redirect to edit mode
        return new RedirectResponse($this->admin->generateObjectUrl('show', $object));
    }

    /**
     * synching trading account information from DB to API/System
     * @param TradingAccount $object
     * @throws \Exception
     */
    protected function synchAccountDBToAPI(TradingAccount $object) {
        try {
            /*@var $apiFactory \Webit\ForexCommonBundle\Helper\TradingAPIFactory */
            $apiFactory = $this->get('trading.api.factory');            
	    $platformCode = $object->getPlatform()?$object->getPlatform()->getCode():'MT5';
            $api = $apiFactory->createAPI($platformCode, 'real');
            
            $accountParams = [
                'login'     => $object->getLogin(),
                'leverage'  => $object->getLeverage(),
                'name'      => $object->getPortalUser()->getFullName(),
                'email'     => $object->getPortalUser()->getUsername(),
                //other parameters goes here
            ];
            
            $api->openConnection();                        
            $api->updateAccount($accountParams);
        } catch (ForexExceptions\MT4APIResponseException $ex) {            
            $this->get('logger')->critical('cannot synch ' . $object->getAccountTypeLabel() . ' account, error: ' . $ex->getMessage());
            $this->addFlash('sonata_flash_error', 'cannot synch to API, reason: '.$ex->getMessage() );
            return false;
        } catch (ForexExceptions\SocketOpeningException $ex) {            
            $this->get('logger')->critical('cannot synch ' . $object->getAccountTypeLabel() . ' account, error: ' . $ex->getMessage());
            $this->addFlash('sonata_flash_error', 'cannot synch to API, cannot open connection to API ' );
            return false;
        }
        
        $this->addFlash('sonata_flash_success', 'Object updated successfully and synched with trading server');
    }

    /**
     * refresh trading account information from API to DB/System
     * @param TradingAccount $object
     * @throws \Exception
     */
    protected function refreshAccountFromAPI(TradingAccount $object){
        $em = $this->getDoctrine()->getManager();

        if ($object->getAccountType() == TradingAccount::ACCOUNT_TYPE_REAL) {
            $accType = 'real';
        } else {
            $accType = 'demo';
        }

        try {
            /*@var $apiFactory \Webit\ForexCommonBundle\Helper\TradingAPIFactory */
            $apiFactory = $this->get('trading.api.factory');            
            $api = $apiFactory->createAPI($object->getPlatform()->getCode(), $accType);
            
            $socket_opened = $api->openConnection();
            if($socket_opened === false){
                throw new \Exception('Cannot open socket for API');
            }            
            $data = $api->getAccountInfo($object->getLogin());
        } catch (\Exception $ex) {
            $this->get('logger')->critical('error refreshing the account, reason: ' . $ex->getMessage().'line#'.$ex->getLine().' '.$ex->getFile());
            $this->addFlash('sonata_flash_error', 'Cannot perform refresh account info, reason: '.$ex->getMessage());
            return false;
        }

        if($data && count($data)>1){ //check if response in valid
            $object->setLeverage($data['leverage']);
            $object->setGroupName($data['group']);
            //TODO: complete
            $em->persist($object);
            $em->flush();
            $this->addFlash('sonata_flash_success', 'account data refreshed successfully from trading server');
        }else{
            $this->addFlash('sonata_flash_error', 'Cannot refresh account; invalid response, please check API logs');
        }
        
        
    }
}
