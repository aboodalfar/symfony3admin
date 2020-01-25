<?php namespace Webit\ForexBoAreaBundle\Controller\Admin;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Symfony\Component\HttpFoundation\Request;
use Webit\ForexCoreBundle\Entity\Setting;

class SettingController extends BaseController{
    
    
    /**
     * Create action.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function configSettingAction(Request $request = null)
    {

        $results = $this->getDoctrine()
                    ->getRepository('\Webit\ForexCoreBundle\Entity\Setting')
                    ->getByKeys(array_keys(Setting::all));

        if ($request->isMethod('post')) {
            $data = $_POST;
            $em = $this->getDoctrine()->getManager();
            foreach ($data as $key => $val) {
         
                if(array_key_exists($key, $results)){
                    $setting= $results[$key];
                    $setting->setValue($val);
                }else{
                     $setting = new Setting();
                     $setting->setKey($key);
                     $setting->setValue($val);
                }
                $em->persist($setting);
                  $em->flush();
            }
            
            $this->addFlash('sonata_flash_success', 'save successfully');
            return new \Symfony\Component\HttpFoundation\RedirectResponse
            ($this->admin->generateUrl('configSetting'));

        }
        return $this->render('WebitForexBoAreaBundle::Setting/custom_edit.html.twig', array(
            'action'  => 'create',
            'results' => $results,
          //  'object' => $object,
        ), null, $request);
    }
    public function listAction(Request $request = null)
    {
        return new \Symfony\Component\HttpFoundation\RedirectResponse
            ($this->admin->generateUrl('configSetting'));
        
    }
    
    
}