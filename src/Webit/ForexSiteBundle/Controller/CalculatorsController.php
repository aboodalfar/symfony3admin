<?php

namespace Webit\ForexSiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webit\ForexCoreBundle\Form\Calculators as CalculatorForms;

class CalculatorsController extends Controller {

    /**
     * action showing all calculators on one page
     * @param Request $request
     * @param Response $response
     */
    public function indexAction(Request $request) {
        $currency_convertor_form = $this->createForm(new CalculatorForms\CurrencyConvertorType());
        $pip_form = $this->createForm(new CalculatorForms\PipCalcType());
        $risk_form = $this->createForm(new CalculatorForms\RiskCalculatorType());
        $pivot_point_form = $form = $this->createForm(new CalculatorForms\PivotPointType());

        return $this->render('AppBundle::ForexSite/Calculators/index.html.twig', array(
                    'currency_convertor_form' => $currency_convertor_form->createView(),
                    'pip_form' => $pip_form->createView(),
                    'risk_form' => $risk_form->createView(),
                    'pivot_point_form' => $pivot_point_form->createView(),
                    'success' => false,
        ));
    }

    public function currencyConvertorAction(Request $request) {
        $is_iframe = ($request->get('is_iframe') ? true : false);
        $form = $this->createForm(new CalculatorForms\CurrencyConvertorType());

        $sess = false;
        
        $success = false;
        $amount = $to = $from = $rate = $con = "";

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $amount = $form->get('amount')->getData();
                $from = $form->get('from')->getData();
                $to = $form->get('to')->getData();

                //$file = file_get_contents('http://download.finance.yahoo.com/d/quotes.txt?s=' . $from . $to . '=X&f=sl1c1abg', true);
                $file = file_get_contents('http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=' . $from . $to . '=X', true);
                $filedata = explode(",", $file);
                $rate = $filedata[1];
                $con = $rate * $amount;
                $success = true;
            }
        }

        $template_params = array('session' => $sess, 'amount' => $amount, 'to' => $to, 'from' => $from, 'rate' => $rate, 'value' => $con, 'success' => $success, 'form' => $form->createView(), 'is_iframe' => $is_iframe);
        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::ForexSite/Calculators/currency_convertor_result.html.twig', $template_params);
        }
        return $this->render('AppBundle::ForexSite/Calculators/currency_convertor.html.twig', $template_params);
    }

    public function pipCalcAction(Request $request) {
        $form = $this->createForm(new CalculatorForms\PipCalcType());

        $securityContext = $this->container->get('security.context');
        $sess = false;
        
        $success = false;
        $result = false;

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $trade_size = $form->get('trade_size')->getData();
                $currency_pair = $form->get('currency_pair')->getData();
                $currency = $form->get('currency')->getData();
                $leverage = $form->get('leverage')->getData();
                $arr = $this->PIPCalculator($currency_pair);
                $final_pip_value = $arr[0];
                $final_margin_value = $arr[1];
                $pipvalue = $trade_size * $final_pip_value * $currency;
                $marginvalue = $leverage * $final_margin_value * $currency * $trade_size;

                $result = array(
                    'pip_value' => $pipvalue,
                    'margin_value' => $marginvalue
                );

                $success = true;
            }
        }

        $template_params = array('session' => $sess, 'success' => $success, 'form' => $form->createView(), 'result' => $result);
        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::ForexSite/Calculators/pip_calc_result.html.twig', $template_params);
        }
        return $this->render('AppBundle::ForexSite/Calculators/pip_calc.html.twig', $template_params);
    }

    protected function PIPCalculator($currency) {
        $current_price = $currency->getPrice();
        $calc_pip_value = $currency->getPip();
        $calc_margin_value = $currency->getMargin();




        $calc_pip_value = trim($calc_pip_value, '"');
        $calc_margin_value = trim($calc_margin_value, '"');

        if (strpos($calc_pip_value, '/')) {
            $arr = explode('/', $calc_pip_value);
            $value = $arr[0];
            $curr = $arr[1];
            $curr = trim($curr, '%');
            $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('name' => $curr));
            $curr_price = $obj->getPrice();

            $final_pip_value = ($value / $curr_price);
        } elseif (strpos($calc_pip_value, '*')) {
            $arr = explode('*', $calc_pip_value);
            $value = $arr[0];
            $curr = $arr[1];
            $curr = trim($curr, '%');
            $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('name' => $curr));
            $curr_price = $obj->getPrice();
            $final_pip_value = ($value * $curr_price);
        } else {
            $final_pip_value = $calc_pip_value;
        }

        if (strpos($calc_margin_value, '/')) {
            $arr = explode('/', $calc_margin_value);
            $value = $arr[0];
            $curr = $arr[1];
            $curr = trim($curr, '%');
            if (strpos($curr, '1')) {
                $curr = trim($curr, '(');
                $curr = trim($curr, ')');
                $arr2 = explode('&', $curr);
                $curr = $arr2[1];
                $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('id' => $curr));
                $curr_price = $obj->getPrice();
                $final_margin_value = ($value / (1 / $curr_price));
            } else {
                $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('id' => $curr));
                $curr_price = $obj->getPrice();
                $final_margin_value = ($value / $curr_price);
            }
        } elseif (strpos($calc_margin_value, '*')) {
            $arr = explode('*', $calc_margin_value);
            $value = $arr[0];
            $curr = $arr[1];
            $curr = trim($curr, '%');
            if (strpos($curr, '1')) {
                $curr = trim($curr, '(');
                $curr = trim($curr, ')');
                $arr2 = explode('&', $curr);
                $curr = $arr2[1];
                $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('id' => $curr));
                $curr_price = $obj->getPrice();
                $final_margin_value = ($value * (1 / $curr_price));
            } else {

                $obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('name' => $curr));
                $curr_price = $obj->getPrice();

                $final_margin_value = ($value * $curr_price);
            }
        } else {
            $final_margin_value = $calc_margin_value;
        }

        return(array($final_pip_value, $final_margin_value));
    }

    public function riskCalcAction(Request $request) {
        $lang = $request->getLocale();

        $form = $this->createForm(new CalculatorForms\RiskCalculatorType());

        $securityContext = $this->container->get('security.context');
        $sess = false;
        

        $success = false;
        $result = false;
        $lot = "";
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {

                $curr = $form->get('currency')->getData();
                $curr_obj = $this->getDoctrine()->getRepository('WebitForexCoreBundle:Currency')->findOneBy(array('id' => $curr));
                if (strpos($curr_obj->getName(), 'USD') == 3) { //last three characters USD
                    $lot = ($form->get('risk')->getData() / 100 * $form->get('account')->getData()) / ($form->get('pip')->getData() * 10);
                } else {
                    $trade_size = 1;
                    $currency = 1.00; //value of USD
                    $leverage = 1; //'1:100';
                    $arr = $this->PIPCalculator($curr);
                    $final_pip_value = $arr[0];
                    $pip_val = $trade_size * $final_pip_value * $currency; //TODO: calculate pips val
                    $lot = ( ($form->get('risk')->getData() / 100) * $form->get('account')->getData()) / ($form->get('pip')->getData() * $pip_val);
                }
                $success = true;
                $result = ['lot' => $lot];
            }
        }

        $template_params = array('session' => $sess, 'success' => $success, 'form' => $form->createView(), 'result' => $result);
        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::ForexSite/Calculators/risk_calc_result.html.twig', $template_params);
        }
        return $this->render('AppBundle::ForexSite/Calculators/risk_calc.html.twig', $template_params);
    }

    public function pivotPointAction(Request $request) {
        $lang = $request->getLocale();

        $form = $this->createForm(new CalculatorForms\PivotPointType());

        $securityContext = $this->container->get('security.context');
        $sess = false;
        

        $success = false;
        $result = false;
        if ($request->getMethod() == 'POST') {
            $form->submit($request);


            if ($form->isValid()) {
                $high = $form->get('high')->getData();
                $low = $form->get('low')->getData();
                $close = $form->get('close')->getData();
                if ($low <= $high and $close <= $high and $close >= $low) {
                    $pivot_point = ($close + $high + $low) / 3;
                    $resistence[] = 2 * $pivot_point - $low;
                    $resistence[] = $pivot_point + ($high - $low);
                    $resistence[] = $high + 2 * ($pivot_point - $low);

                    $support[] = 2 * $pivot_point - $high;
                    $support[] = $pivot_point - ($high - $low);
                    $support[] = $low - 2 * ($high - $pivot_point);
                    $success = true;

                    $result = ['pivot_point' => $pivot_point, 'support' => $support, 'resistence' => $resistence];
                }
            }
        }



        $template_params = array('session' => $sess, 'success' => $success, 'form' => $form->createView(), 'result' => $result);
        if ($request->isXmlHttpRequest()) {
            return $this->render('AppBundle::ForexSite/Calculators/pivot_calc_result.html.twig', $template_params);
        }
        return $this->render('AppBundle::ForexSite/Calculators/pivot_calc.html.twig', $template_params);
    }

}
