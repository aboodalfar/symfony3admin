<?php

namespace Webit\ForexSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * this controller contains the components on the homepage to be rendered;
 * like: images slider, latest news... etc.
 * */
class HomepageController extends Controller
{
    /**
     * Controller to render all of homepage components
     * @param Request
     * @return Response
     * */
    public function indexAction(Request $request)
    {
        return $this->render('WebitForexSiteBundle::Homepage/index.html.twig');
    }
    
    public function menusAction(Request $request) {
        $menuItemRepo = $this->getDoctrine()->getRepository('WebitCMSBundle:Menu');
        $results = $menuItemRepo->getItemsByMenuId($request->getLocale());
        if($request->query->get('menu_id') && isset($results[$request->query->get('menu_id')])){
            // to show in inner pages
            $this->get('session')->set('menus',$results[$request->query->get('menu_id')]);
        }else{
            $this->get('session')->set('menus',array());
        }
        
        
       // $this->get('twig')->addGlobal('menus', $results);
  
        return $this->render('WebitForexSiteBundle::Homepage/menus.html.twig',
                array('results' => $results,'_locale'=>$request->getLocale()));
    }
    
   

    /**
     * Controller to render Slider images on the homepage, based on language
     * @param Request
     * @return Response
     * */
    public function sliderAction(Request $request)
    {
        $locale = $request->getLocale();
        $items = $this->getDoctrine()->getRepository('WebitCMSBundle:Slider')
                ->getSliderImages($locale);

        if (!$items) { //fallback to english by default
            //return new Response('slide not implementeds');
            $items = $this->getDoctrine()->getRepository('WebitCMSBundle:Slider')
                ->getSliderImages('en');
        }
        if(!$items):
            return new Response("<p style='text-align:center'>slider not implemented</p>");
        endif;

        return $this->render('WebitForexSiteBundle::Homepage/slider.html.twig', array('items' => $items));
    }
    
    
 
    
    public function allBlockAction(Request $request) {
        $locale = $request->getLocale();
        $contentRepos = $this->getDoctrine()->getRepository('WebitCMSBundle:Content');
        $results = $contentRepos->getContentQueryPyCategory('Technology Execution',$locale,'about-baxia');
        $technologiesExecution = array();
        $about_baxia = null;
        foreach ($results as $key => $content) {
            if($content->getSlug() == 'about-baxia'){
                $about_baxia = $content;
                continue;
            }
            if($content->getContentCategory() && $content->getContentCategory()->getSlug() == 'technology-execution'){
                $technologiesExecution[] = $content;
            }
        }   
       // sort($technologiesExecution);
        $spreads  = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Spread')
                ->findBy(array(), array('order' => 'ASC'));

        
        $PaymentsGateway  = $this->getDoctrine()->getRepository('WebitForexCoreBundle:PaymentGateway')
                ->findBy(array('isActive'=>true));

        return $this->render('WebitForexSiteBundle::Homepage/all_blocks.html.twig', 
                array('about_baxia' => $about_baxia,'_locale'=>$locale,
                    'spreads'=>$spreads,'technologiesExecution'=>$technologiesExecution,'PaymentsGateway'=>$PaymentsGateway));
    }
    
    
    private function functionName($param) {
        $finalResults = array();
        foreach ($objs as $key => $value) {
          // $label = [];
            if (is_null($value['parenItem'])) {
                // level 1
                $label[$value['item_id']][$value['lang']] = $value['label'];
                
                
                $description[$value['item_id']][$value['lang']] = $value['description'];
                
                
                
                if(isset($label[$value['item_id']])&&
                   isset($label[$value['item_id']]['label'])&&
                   isset($label[$value['item_id']]['label'] [$locale])){
                    
                    $label = $label[$value['item_id']]['label'] [$locale]; 
                }
                elseif(isset($label[$value['item_id']])&&
                        isset($label[$value['item_id']] ['label'])&&
                        isset($label[$value['item_id']] ['label']['en'])){
                    
                    $label = $label[$value['item_id']] ['label']['en']; 
                }else{
                    $label = '';
                }
                
                if(isset($description[$value['item_id']])&&
                        isset($description[$value['item_id']]['description'])&&
                        isset($description[$value['item_id']]['description'] [$locale])
                        
                        ){
                    $description = $label[$value['item_id']] ['description'][$locale]; 
                }
                elseif(isset($label[$value['item_id']]['description'] ['en'])){
                    $description = $label[$value['item_id']] ['description']['en']; 
                }else{
                    $description = '';
                }
                
                if(!empty($level2['contentId'])){
                     $url = $this->generateUrl(
                        'showPage',
                        array('id' => $level2['contentId'],'slug'=>$level2['content_slug'])
                    );
                }elseif(!empty($level2['link'])){
                    $url = $level2['link'];
                }
                elseif(!empty($level2['route'])){
                     $url = $this->generateUrl(
                        $level2['route']
                    );
                }else{
                    $url = 'javascript:void(0)';
                }
                
                $finalResults[$value['item_id']] = array(
                    'url'=>$url,
                    'label' => $label, 'description' => $description
                );
            } elseif (!is_null($value['parenItem'])) {

                if (isset($finalResults[$value['parenItem']])) {
                    //level 2
                    $label2[$value['parenItem']][$value['lang']] = $value['label'];
                    
                    $finalResults[$value['parenItem']]['level2'][$value['item_id']] = 
                ['id' => $value['item_id'], 'label' => $label2[$value['parenItem']],'link' => $value['link'], 'route' => $value['route'], 'contentId' => $value['contentId'], 'content_slug' => $value['content_slug']];

                    //level 3


                    foreach ($objs as $value2) {
                        if ($value2['parenItem'] == $value['item_id']) {
                            $label3[$value['parenItem']][$value2['lang']] = $value2['label'];
                            $finalResults[$value['parenItem']]['level2'][$value['item_id']]['level3'][$value2['item_id']] = ['label' => $label3[$value['parenItem']], 'link' => $value2['link'], 'route' => $value2['route'], 'contentId' => $value2['contentId'], 'content_slug' => $value2['content_slug']];
                        }
                    }
                }
            }
        }
    }

}
