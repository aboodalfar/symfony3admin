<?php

namespace Webit\ForexSiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexSiteBundle\Controller\BaseController;

/**
 * this class contains all common components to be rendered (in the layouts)
 *
 * */
class DefaultController extends BaseController {


    public function systemMessageAction(Request $request, $message_type, $message) {
        return $this->render('WebitForexSiteBundle::Default/system_message.html.twig',
                array('message_type' => $message_type, 'message' => $message));
    }

 

 /**
     * switch website language mirror
     * @param Request $request
     * @return RedirectResponse
     * */
    public function switchLangAction(Request $request) {
        $referer = $request->headers->get('referer');
        if (empty($referer) === true) {
            $referer = $this->generateUrl('homepage');
        }
        $old_lang = $request->getLocale();
        $new_lang = $request->query->get('lang', 'en');        
        if($old_lang != 'en' && $old_lang != 'ar'){
            $request->setLocale('en');
            $new_url = '/en/'; //handling special case for non-existing lang            
        }else{
            $new_url = str_replace("/$old_lang/", "/$new_lang/", $referer);
            $request->setLocale($new_lang);
        }
        return $this->redirect($new_url);
    }


    
    }
