<?php defined('SYSPATH') or die('No direct script access.');

class Boldcron_Payment extends Boldcron_Base{

    private  $_methods = array('cielows2p_visa' => array(
                                                        'brand' => 'visa',
                                                        'type' => 2,
                                                        ),
                                'cielows2p_mastercard' => array(
                                                        'brand' => 'master',
                                                        'type' => 2,
                                                        ),
                                'cielows2p_diners' => array(
                                                        'brand' => 'diners',
                                                        'type' => 2,
                                                        ),
                                'cielows2p_elo' => array(
                                                        'brand' => 'elo',
                                                        'type' => 2,
                                                        ),
                                'cielows2p_amex' => array(
                                                        'brand' => 'amex',
                                                        'type' => 2,
                                                        ),
                                'boleto_bb' => array(
                                                      'brand' => '',
                                                      'type' => 1,
                                                    ),
                                );

    var $method;

    var $cc_brand;

    var $cc_number;

    var $cc_cvv;

    var $cc_exp_month;

    var $cc_exp_year;

    var $cc_holder;

    var $cc_cpf_cnpj;

    var $value;


    private function _get_method_info($info){

        $method = $this->get('method');

        if( isset( $this->_methods[$method][$info] ) ){
            $result = $this->_methods[$method][$info];
        }else{
            $result = NULL;
        }

        return $result;

    }


    public function get_method_brand(){

       return $this->_get_method_info('brand');
    }

    public function get_method_type(){

       return $this->_get_method_info('type');
    }


    
   

}