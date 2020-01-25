<?php

namespace Webit\ForexBoAreaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WebitForexBoAreaBundle:Default:index.html.twig', array('name' => $name));
    }
}
