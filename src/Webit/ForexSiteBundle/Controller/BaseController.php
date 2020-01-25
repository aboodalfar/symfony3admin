<?php

namespace Webit\ForexSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class BaseController extends Controller {


    protected function pagination($query,$limit=10)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, $this->get('request')->query->get('page', 1), $limit
        );
        return $pagination;
    }

    }
