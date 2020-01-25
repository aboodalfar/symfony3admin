<?php

/* 
 * 
 * Please fill out the data in Text only
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webit\ForexSiteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsTextOnlyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ((!is_null($value) || !empty($value)) && !preg_match("/^[a-zA-Z' -]+$/", $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}