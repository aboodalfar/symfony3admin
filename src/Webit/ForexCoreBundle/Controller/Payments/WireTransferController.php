<?php

namespace Webit\ForexCoreBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webit\ForexCoreBundle\Entity\PortalUser;
use Webit\ForexCoreBundle\Entity\WireTransfer;
use Symfony\Component\Intl\Intl;

class WireTransferController extends Controller {

    /**
     * @todo implement Listing Wire Transfer Transactions
     * @param Request $request
     * @return Response
     */
    public function ListingWireTransferAction(Request $request) {
        $user_id = $this->getUser()->getId();
        $portal_user = $this->getPortalUser($user_id);
        if ($portal_user->getAccountType() == PortalUser::DemoAccount) {
            return $this->redirect($this->generateUrl('UpgradetoReal'));
        } else {
            $success = $request->get('success');
            $rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:WireTransfer");
            $wire_transfer_info = $rep->getWireTransferInfo($user_id);
            $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/wiretransfer';
            $activeClass = 'wireTransfer';

            return $this->render('AppBundle::MemberArea/Payments/WireTransfer/ListingWireTransfer.html.twig', array('wire_transfer_info' => $wire_transfer_info, 'success' => $success, 'path' => $dir,'activeClass'=>$activeClass));
        }
    }

    /**
     * @todo implement wire transfer form and email the back office
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request) {
        $user_id = $this->getUser()->getId();
        $portal_user = $this->getPortalUser($user_id);
        if ($portal_user->getAccountType() == PortalUser::DemoAccount) {
            return $this->redirect($this->generateUrl('UpgradetoReal'));
        } else {

            $wire_transfer = new \Webit\ForexCoreBundle\Entity\WireTransfer();
            $success = false;
            $activeClass = 'wireTransfer';

            $rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:BankingInformation");
            $bank_obj = $rep->getBankNames($user_id);
            if (empty($bank_obj)) {
                return $this->redirect($this->generateUrl('ListingBanks'));
            }

            $form = $this->createForm(new \Webit\ForexCoreBundle\Form\Payments\WireTransferType(array('portal_user' => $portal_user)), $wire_transfer);
            $trading_accounts = $portal_user->getTradingAccounts()->toArray();


            if ($request->getMethod() == 'POST') {
                $form->submit($request);
                if ($form->isValid()) {
                    $this->uploadWireTransferDoc($form, $wire_transfer);
                    $this->saveWireTransferToDB($wire_transfer, $portal_user);
                    $this->wiretransfersendEmail($wire_transfer, $form->getData());
                    $success = true;

                    $dispacher = $this->container->get('event_dispatcher');
                    $dispacher->dispatch('user.log', new GenericEvent("Wire Transfer Request", array('type' => 'log',
                        'user' => $portal_user,
                        'body' => 'Requested Wire Transfer From Bank: ' . $wire_transfer->getBankingInformation()->getBankName() . ' Trading Account: ' . $wire_transfer->getTradingAccount() . ' With Amount: ' . $wire_transfer->getAmount()
                    )));
                    return $this->redirect($this->generateUrl('ListWireTransfer', array('success' => 'WIRE_TRANSFER_SUCCESS_MSG')));
                }
            }
            return $this->render('AppBundle::MemberArea/Payments/WireTransfer/index.html.twig', array('form' => $form->createView(), 'trading_accounts' => count($trading_accounts) == 0 ? null : true, 'activeClass' => $activeClass));
        }
    }

    /**
     * sending wiretransfer data and uploaded documents to back office
     * @param \Webit\ForexCoreBundle\Entity\WireTransfer $wire_transfer
     */
    public function wiretransfersendEmail(\Webit\ForexCoreBundle\Entity\WireTransfer $wire_transfer) {
        $emails = $this->container->getParameter('emails');
        $country = Intl::getRegionBundle()->getCountryName($wire_transfer->getBankingInformation()->getBankCountry());
        $email_params = array(
            '%full_name%' => $wire_transfer->getPortalUser()->getFirstName() . ' ' . $wire_transfer->getPortalUser()->getLastName(),
            '%email%' => $wire_transfer->getPortalUser()->getUsername(),
            '%currency%' => $wire_transfer->getTradingCurrency(),
            '%amount%' => $wire_transfer->getAmount(),
            '%bank_name%' => $wire_transfer->getBankingInformation()->getBankName(),
            '%bank_country%' => $country,
            '%branch_name%' => $wire_transfer->getBankingInformation()->getBranchName(),
            '%IBAN%' => $wire_transfer->getBankingInformation()->getIBAN(),
            '%swift_code%' => $wire_transfer->getBankingInformation()->getSwiftCode(),
            '%trading_account%' => $wire_transfer->getTradingAccount()->getLogin(),
        );

        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/wiretransfer/';
        $message_helper = new \Webit\MailtemplateBundle\Helper\MailHelper($this->get('templating'), $this->getDoctrine());

        $message = $message_helper->configureMail('wiretransfer', $email_params, array($emails['finance'], $emails['bo']), $wire_transfer->getPortalUser()->getCommunicationLanguage());
        $message->attach(\Swift_Attachment::fromPath($dir . $wire_transfer->getUploadTTCopy()));

        $this->get('mailer')->send($message);
    }

    /**
     * This method returns the portal user according to User Id
     * @param type $user_id
     * @return type
     */
    public function getPortalUser($user_id) {
        return $this->getDoctrine()->getRepository('WebitForexCoreBundle:PortalUser')->find($user_id);
    }

    /**
     * Retrieving bank information
     *
     * @param type $bank_info
     * @return array
     */
    public function getBankingOptions($bank_info) {
        $banking_options = array();
        foreach ($bank_info as $key => $val) {
            $banking_options[$val->getId()] = $val;
        }
        return $banking_options;
    }

    /**
     * Retrieving Trading Account Information
     *
     * @param type $trading_account
     * @return array
     */
    public function getTradingAccountOptions($trading_account) {
        $trading_account_options = array();
        foreach ($trading_account as $key1 => $val1) {
            $trading_account_options[$val1->getId()] = $val1;
        }
        return $trading_account_options;
    }

    /**
     * saving wiretransfer data into the database
     * @param type $wire_transfer
     * @param type $user_obj
     */
    public function saveWireTransferToDB($wire_transfer, $user_obj) {
        $em = $this->getDoctrine()->getManager();
        $wire_transfer->setPortalUser($user_obj);

        $em->persist($wire_transfer);
        $em->flush();
    }

    /**
     * upload TTCopy document to wire transfer and set its value to entity
     * @param \Symfony\Component\Form\Form $form
     * @param WireTransfer $wire_transfer
     */
    protected function uploadWireTransferDoc(\Symfony\Component\Form\Form $form, WireTransfer $wire_transfer) {
        $file = $form->get('uploadTTCopy')->getData();
        $name = time() . '_' . $file->getClientOriginalName();
        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/wiretransfer';
        $file->move($dir, $name);
        $wire_transfer->setUploadTTCopy($name);
    }

    public function bankWireInfoAction() {
        $user_id = $this->getUser()->getId();
        $portal_user = $this->getPortalUser($user_id);
        $company_id = $portal_user->getRealProfile()->getCompany();
        $company_name = $portal_user->getRealProfile()->getCompanyLabel();
        $company_bank_rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:CompanyBank");
        $bank_data = $company_bank_rep->getBanks($company_id);
        return $this->render('WebitForexCoreBundle::MembersArea/WireTransfer/BankWire.html.twig', array('data' => $bank_data, 'company_name' => $company_name));
    }

}
