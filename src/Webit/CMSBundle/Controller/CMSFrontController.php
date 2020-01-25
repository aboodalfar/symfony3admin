<?php

namespace Webit\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Webit\CMSBundle\Entity\Content;

class CMSFrontController extends Controller
{
    public function showAction(Request $request,$id)
    {
        $locale = $request->get('_locale');
        if (!$locale) {
            $locale = 'en';
        }

        /** @var Content $page * */
        $pages = $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
                ->findPage($id,$locale);

        if(!$pages){
            $pages = $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
                ->findPage($id,'en');
        }

        if (!$pages) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Page not found...');
        }

     
        $page = current($pages);
        return $this->render('WebitCMSBundle::CMSFront/showPage.html.twig', array('page' => $page,
            'contents' => $pages,'is_page'=>true,'_locale'=>$locale));
    }

    public function drawMenuAction(Request $request,$menu_name, $ul_class = '', $li_class = '', $level=1, $parent_id = null)
    {
        $menu = $this->getDoctrine()->getRepository('WebitCMSBundle:Menu')->findOneBy(array('name' => $menu_name));
        if (!$menu) {
            return new Response("'$menu_name' menu not implemented");
        }

        $locale = $request->getLocale();
        if (!$locale) {
            $locale = 'en';
        }

        $items = $this->getDoctrine()->getRepository('WebitCMSBundle:MenuItem')->getItemsByMenuId($menu->getId(), $locale, $parent_id);

        return $this->render('WebitCMSBundle::CMSFront/menu.html.twig', array('menu_name'=>$menu_name,'items' => $items, 'ul_class' => $ul_class, 'li_class' => $li_class, 'level'=>$level));
    }

    public function sideMenuAction(Request $request,$menu_id)
    {        
        $locale = $request->getLocale();
        $menus = $this->get('session')->get('menus');
        $isThereLevel3 = false;
        if(isset($menus['level2'])){
            foreach ($menus['level2'] as $key => $value) {
                if(isset($value['level3'])){
                    $isThereLevel3 = true;
                    break;
                }
            }
        }
        if(!$isThereLevel3){
            return $this->render('WebitCMSBundle::CMSFront/sideMenu2.html.twig', 
                array('_locale'=>$locale,'level' 
                    => $menus));
        }
        
        return $this->render('WebitCMSBundle::CMSFront/sideMenu.html.twig', 
                array('_locale'=>$locale,'result' 
                    => (isset($menus['level2'])?$menus['level2']:array())));
    }

    public function faqListingAction(Request $request)
    {
        $locale = $request->getLocale();
        $page = $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
                ->getContentBySlug('faq',$locale);
        
        if(!$page){
            $page = $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
                ->getContentBySlug('faq','en');
        }
        
        $questions = $this->getDoctrine()->getRepository('\Webit\CMSBundle\Entity\FaqQuestion')
                ->getActiveQuestions($locale);
  
        return $this->render('WebitCMSBundle::CMSFront/faqPage.html.twig',
                array('routeName'=>$request->get('_route')
                ,'questions' => $questions,'page'=>$page,'_locale'=>$locale));
    }

    public function glossaryListingAction(Request $request)
    {
        $glossary = $this->getDoctrine()->getRepository('WebitCMSBundle::Glossary')->findAll();
        $glossary_coll = array();
        foreach ($glossary as $word) {
            $word_letter = mb_substr($word->getWord(), 0, 1);
            $glossary_coll[$word_letter] = $word;
        }
        return $this->render('WebitCMSBundle::CMSFront/glossaryListing.html.twig', array('glossary_coll' => $glossary_coll));
    }

    public function glossaryShowAction(Request $request, $id, $slug)
    {
        $lang = $request->getLocale();
        $letters = $this->getDoctrine()->getRepository('WebitCMSBundle:Glossary')->getLetters($lang);

        if ($lang == 'ar') {
            $alpha = array(
                0 => "ا", 1 => "ب", 2 => 'ث', 3 => "ت", 4 => "ج", 5 => "ح", 6 => "خ", 7 => "د"
                , 8 => "ذ", 9 => "ر", 10 => "ز", 11 => "س", 12 => "ش", 13 => "ص", 14 => "ض", 15 => "ط", 16 => "ظ"
                , 17 => "ع", 18 => "غ", 19 => "ف", 20 => "ق", 21 => "ك", 22 => "ل", 23 => "م", 24 => "ن", 25 => "ه", 26 => "و", 27 => "ي");
        } else {
            $alpha = range('A', 'Z');
        }
        $glossary = $this->getDoctrine()->getRepository('WebitCMSBundle:Glossary')->find($id);
        if (!$glossary) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->render('WebitCMSBundle::CMSFront/glossary_show.html.twig', array('alpha' => $alpha, 'term' => $glossary, 'letters' => $letters));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     */
    public function glossaryAction(Request $request)
    {
        $lang = $request->getLocale();
        $letters = $this->getDoctrine()->getRepository('WebitCMSBundle:Glossary')->getLetters($lang);
        $result = $this->getDoctrine()->getRepository('WebitCMSBundle:Glossary')->getGlossary($lang);

        if ($lang == 'ar') {
            $alpha = array(
                0 => "ا", 1 => "ب", 2 => "ت", 3 => 'ث', 4 => "ج", 5 => "ح", 6 => "خ", 7 => "د"
                , 8 => "ذ", 9 => "ر", 10 => "ز", 11 => "س", 12 => "ش", 13 => "ص", 14 => "ض", 15 => "ط", 16 => "ظ"
                , 17 => "ع", 18 => "غ", 19 => "ف", 20 => "ق", 21 => "ك", 22 => "ل", 23 => "م", 24 => "ن", 25 => "ه", 26 => "و", 27 => "ي");
        } else {
            $alpha = range('A', 'Z');
        }

        return $this->render('WebitCMSBundle::CMSFront/glossary.html.twig', array('alpha' => $alpha, 'glossary' => $result, 'letters' => $letters));
    }

    /**
     * action to display list of pages by category
     * @param Request $request
     * @return Response
     */
    public function categoryPagesAction(Request $request)
    {
        $id = $request->get('id');
        $lang = $request->getLocale();
        $pages = $this->getDoctrine()->getRepository('WebitCMSBundle:Content')
                ->getLatestByCategoryId($id,$lang);
    
        $mainPage = array();$sideMenuItemId = null;
        if($pages){
            $mainPageIndex = array_search(null, array_column($pages, 'sideMenuItemId'));
            if($mainPageIndex !== false){
                $mainPage = $pages[$mainPageIndex];
                foreach (array_keys($pages) as $key => $value) {
                    if($key != $mainPageIndex){
                        $sideMenuItemId = $pages[$key]['sideMenuItemId'];
                        break;
                    }
                }
                unset($pages[$mainPageIndex]);
            }else{
                $sideMenuItemId = current($pages)['sideMenuItemId'];
            }
        }
     
           // dump($mainPage);die;
       // dump($mainPage);die;
  

        return $this->render('WebitForexSiteBundle::Extends/category_page.html.twig',array('pages'=>$pages,'mainPage'=>$mainPage,'sideMenuItemId'=>$sideMenuItemId));
    }
    
    public function uploadImageAction(Request $request) {
       $file =  $request->files->get('upload');
       if($file && $file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile){
            $upload_path = $this->get('kernel')->getRootDir() . '/../web/uploads/content/';
            if (!(file_exists($upload_path) && is_dir($upload_path))) {
                mkdir($upload_path);
            }
            $file_name = rand(0, 1000) . md5($file->getClientOriginalName()).'.'.
                     $file->getClientOriginalExtension();
            $file->move($upload_path, $file_name);
            $result = array('uploaded' => 1,'fileName'=>'dsa','url'=>$upload_path.$file_name);
            echo json_encode($result);
            exit();
            
            
            echo $upload_path.$file_name;
     exit();
            
            return $upload_path.$file_name;
           
       }
        
    }
    
    
    /**
     * action to search CMS pages in the site
     * @param Request $request
     * @param Response
     */
    public function searchAction(Request $request) {
        $lang = $request->getLocale();
        $pagination = array();
        if ($search_q = $request->request->get('search')) {
            $query = $this->getDoctrine()
                    ->getRepository('\Webit\CMSBundle\Entity\Content')
                    ->getContentSearchQuery($search_q,$lang);

            $pagination=  $this->get('knp_paginator')->paginate(
                    $query, /* query NOT result */
                    $request->query->getInt('page', 1)/*page number*/,
                    15/*limit per page*/
            );
          
            
        }

        return $this->render('WebitForexSiteBundle::Default/search.html.twig', array(
            'data' => $pagination,
            'search_q'=>$search_q,
            '_locale'=>$lang
            ));
    }
}
