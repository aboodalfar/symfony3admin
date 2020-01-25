<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webit\ForexSiteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsTextOnly extends Constraint
{
    //public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
    public $message = 'Text only';
}