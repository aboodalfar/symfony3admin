<?php

namespace Webit\ForexCoreBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Webit\ForexCoreBundle\Helper\MT4API;
use \Webit\ForexCoreBundle\Entity\TradingAccount;

class TradingAccountController extends Controller
{
    public function editAction($id = NULL, \Symfony\Component\HttpFoundation\Request $request = NULL)
    {
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
                $this->addFlash('sonata_flash_success', 'flash_edit_success');

                $em = $this->getDoctrine()->getManager();
                $api_info = $this->container->getParameter('mt4_api');
                if($object->getAccountType()==TradingAccount::real_account){
                    $server_info = $api_info['real'];
                }else{
                    $server_info = $api_info['demo'];
                }

                try{
                    $api = new MT4API($em, $this->getUser()->getId());
                    $api->OpenConnection($server_info['server'], $server_info['port']);
                    $api->SyncTradingccount($object);
                }catch(\Exception $ex){
                    $logger = $this->get('logger');
                    $logger->error('cannot synch '.$object->getAccountTypeLabel().' account, error: '.$ex->getMessage());
                }

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

    public function refreshAccountAction($id)
    {
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

        $em = $this->getDoctrine()->getManager();
        $api_info = $this->container->getParameter('mt4_api');

        if($object->getAccountType() == TradingAccount::real_account){
            $server_info = $api_info['real'];
        }else{
            $server_info = $api_info['demo'];
        }

        try{
            $api = new MT4API($em, $this->getUser()->getId());
            $api->OpenConnection($server_info['server'], $server_info['port']);
            $data = $api->RefreshAccount($object->getLogin());
        }catch(\Exception $ex){
            $this->get('logger')->error('error refreshing the account, reason: '.$ex->getMessage());
        }

        $in_hand_field = array('comment', 'leverage', 'agent_account','group');
        if ($data != -1) {

            foreach ($data as $fields) {

                $rerange_fields = explode("=", $fields);

                //TODO: clean-up the code...
                if (in_array(@$rerange_fields[0], $in_hand_field)) {
                    switch (@$rerange_fields[0]) {
                        case 'comment':
                            $object->setComment(@$rerange_fields[1]);
                            break;
                        case 'agent_account':
                            $object->setAgentAccount(@$rerange_fields[1]);
                            break;
                        case 'leverage':
                            $object->setLeverage(@$rerange_fields[1]);
                            break;
                        case 'group':
                            $object->setGroup(@$rerange_fields[1]);
                            break;

                        default:
                            break;
                    }
                }
                $em->persist($object);
                $em->flush();
            }
        }
        $this->addFlash('sonata_flash_success', 'Refresh Successfully');
        // redirect to edit mode
        return $this->redirectTo($object);
    }

}
