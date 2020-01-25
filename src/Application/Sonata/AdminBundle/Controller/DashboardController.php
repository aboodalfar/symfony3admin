<?php

namespace Application\Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Webit\ForexCoreBundle\Entity\RealProfile;
use Webit\ForexCoreBundle\Entity\Partnership;

class DashboardController extends Controller
{
    /**
     * getting counters for real users profile dashboard
     * @param Request $request
     * @return Response
     */
    public function usersCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfile')
                ->countByStatus(array(RealProfile::RECENT, RealProfile::PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfile')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));
    }
    
    /**
     * getting counters for demo user profile dashboard
     * @param Request $request
     * @return Response
     */
    public function demoCountAction(Request $request){        
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\DemoProfile')
                ->countByPeriod(time()-24*60*60);
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\DemoProfile')
                ->countByPeriod();
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));        
    }
    
    /**
     * getting counters for partnership requests profile dashboard
     * @param Request $request
     * @return Response
     */
    public function partnershipCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Partnership')
                ->countByStatus(array(Partnership::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Partnership')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));
    }
    
    /**
     * getting counters for edit information request in dashboard
     * @param Request $request
     * @return Response
     */    
    public function editRequestCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfileEdit')
                ->countByStatus(array(\Webit\ForexCoreBundle\Entity\RealProfileEdit::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\RealProfileEdit')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));        
    }
    
    /**
     * getting counters for wire transfer in dashboard
     * @param Request $request
     * @return Response
     */    
    public function WireTransferCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\WireTransfer')
                ->countByStatus(array(\Webit\ForexCoreBundle\Entity\WireTransfer::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\WireTransfer')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));        
    }
    
    /**
     * getting counters for deposits dashboard
     * @param Request $request
     * @return Response
     */    
    public function depositCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Deposit')
                ->countByStatus(array(\Webit\ForexCoreBundle\Entity\Deposit::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Deposit')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));        
    }
    
    /**
     * getting counters for withdrawal request in dashboard
     * @param Request $request
     * @return Response
     */    
    public function withdrawalRequestCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\WithdrawalRequest')
                ->countByStatus(array(\Webit\ForexCoreBundle\Entity\WithdrawalRequest::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\WithdrawalRequest')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));                
    }
    
    /**
     * getting counters for sub account request in dashboard
     * @param Request $request
     * @return Response
     */    
    public function subAccountRequestCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\SubAccount')
                ->countByStatus(array(\Webit\ForexCoreBundle\Entity\SubAccount::STATUS_PENDING));
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\SubAccount')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));       
    }
    
    /**
     * getting counters for callback request in dashboard
     * @param Request $request
     * @return Response
     */    
    public function callbackRequestCountAction(Request $request){
        $count_new = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Callback')
                ->countByStatus(false);
                
        $count_all = $this->getDoctrine()->getRepository('Webit\ForexCoreBundle\Entity\Callback')
                ->countByStatus(); 
        
        return new Response(json_encode(array('new'=>$count_new, 'all'=>$count_all)));       
    }
    
    public function settingsAction(Request $request){
        
        return $this->render('ApplicationSonataAdminBundle::Default/settings.html.twig');
    }
    
    /**
     * getting bank for callback request in dashboard
     * @param Request $request
     * @return Response
     */  
      public function wiretransferBankInfoAction(Request $request) {
        
        $portal_id = $request->get('portalid');
        $rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:BankingInformation");
        $banks = $rep->bankInformationByPortal($portal_id);
          return $this->render('WebitForexCoreBundle::wiretransfer/wiretransfer_bank.html.twig', array('banks' => $banks));
    }
    
    public function getWireTransferTradingAccountAction(Request $request){
        $portal_id = $request->get('portalid');
        $rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:TradingAccount");
        $tradingAccount = $rep->getTradingAccount($portal_id);
        return $this->render('WebitForexCoreBundle::wiretransfer/wiretransfer_tradingaccount.html.twig', array('tradingaccount' => $tradingAccount));
    }
    
        
    public function getDepositTradingAccountAction(Request $request){
        $portal_id = $request->get('portalid');
        $rep = $this->getDoctrine()->getRepository("WebitForexCoreBundle:TradingAccount");
        $tradingAccount = $rep->getTradingAccount($portal_id);
        return $this->render('WebitForexCoreBundle::deposit/deposit_tradingaccount.html.twig', array('tradingaccount' => $tradingAccount));
    }
    
}