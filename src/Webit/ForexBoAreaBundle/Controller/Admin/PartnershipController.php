<?php

namespace Webit\ForexBoAreaBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Webit\ForexCoreBundle\Entity\Partnership;
use Webit\ForexCoreBundle\Form\PartnershipType;

/**
 * Partnership controller.
 *
 * @Route("/partnership")
 */
class PartnershipController extends Controller
{
    public function chartsStatsAction(){
        $start_date = date('Y-m-d H:i:s', time()-14*24*60*60);
        $end_date = date('Y-m-d H:i:s', time());
        
        $active_chart_data      = $this->getActiveChartData($start_date, $end_date);
        $country_chart_data     = $this->getCountryChartData($start_date, $end_date);        
        $referer_chart_data     = $this->getRefererChartData($start_date, $end_date);
        $createdat_chart_data   = $this->getCreatedChartData($start_date, $end_date);
        $partner_type_chart_data = $this->getPartnerTypeChartData($start_date, $end_date);
        
        return $this->render('WebitForexBoAreaBundle::Partnership/chartsAndStats.html.twig',array(            
            'referer_chart_data' => $referer_chart_data,
            'country_chart_data' => $country_chart_data,
            'active_chart_data'  => $active_chart_data,
            'createdat_chart_data' => $createdat_chart_data,
            'partner_type_chart_data' => $partner_type_chart_data,
        ));         
    }
    
    /**
     * getting chart data for "by country" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getCountryChartData($start_date, $end_date){        
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')
                    ->getChartsData('p.country', $start_date, $end_date, 100);
        
        arsort($query_data);
        
        $final_data = array('other'=>0);
        $countries = \Symfony\Component\Locale\Locale::getDisplayCountries('en');
        $i=0;
        foreach($query_data as $country_code => $counter){
            if($i<5){
                $final_data[$countries[$country_code]] = $counter;
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
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')
                    ->getChartsData('p.isActive', $start_date, $end_date);
        
        $ret = array();
        foreach(Partnership::$active_types as $status => $label){
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
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')
                    ->getChartsData('DATE(p.createdAt)', $start_date, $end_date);
        
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
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')
                    ->getChartsData('p.referer', $start_date, $end_date,10);
        
        return $query_data;
    }        
    
    /**
     * getting chart data for "by partner type" chart
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    protected function getPartnerTypeChartData($start_date, $end_date){
        $query_data = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Partnership')
                    ->getChartsData('p.type', $start_date, $end_date);
        
        $ret = array();
        foreach(Partnership::$partnership_type as $type => $label){
            if(isset($query_data[$type]) === true){
                $ret[$label] = $query_data[$type];
            }
        }        
        return $ret;
    }        
}
