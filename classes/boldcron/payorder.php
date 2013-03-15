<?php defined('SYSPATH') or die('No direct script access.');

class Boldcron_Payorder extends Boldcron_Base{

    var $debug = FALSE;

    var $_log = array();


    //estado inical da payorder
    var $data_return = array('status' => 4,
                             'msg' => 'NAO EFETIVADO (inicial)',
                             'id' => -1);

    var $value;

    var $item_code;

    var $description;

    var $bell_url;

    var $merch_ref;

    var $id; //id na boldcron

    var $customer_id;

    var $customer_ip;

    //Boldcron_Payment object
    var $payment;

    // Boldcron_Base objects
    var $customer;

    var $shipment;

    var $billing;

    public function __construct(Array $data=array()){

        $this->set('payment', new Boldcron_Payment());
        $this->set('customer', new Boldcron_Person());
        $this->set('shipment', new Boldcron_Person());
        $this->set('billing', new Boldcron_Person());
        $this->set('customer_ip', '');

        parent::__construct($data);

    }
    

    public function encrypt($data){

        $cert_path = MODPATH.'/boldcron/certificates/criptografia_dados_prod.pem';

        $pub_key = file_get_contents($cert_path);

        openssl_public_encrypt($data, $encrypted, $pub_key);

        return base64_encode($encrypted);
    }


    public function make_xml_customer_info(Boldcron_Person $person){

        $name = Text::limit_chars($this->sanitize_text($person->name), 28, '');
        $cpf_cnpj = Text::limit_chars($person->cpf_cnpj, 48, '');
        $address =  Text::limit_chars($this->sanitize_text($person->address), 98, '');
        $number = Text::limit_chars($this->sanitize_text($person->address_number), 23, '');
        $complement = Text::limit_chars($this->sanitize_text($person->address_complement), 23, '');
        $burgh = Text::limit_chars($this->sanitize_text($person->address_burgh), 23, '');
        $birthday = str_replace('-', '', $person->birthday);
        $gender = strtoupper($person->gender);
        $city = Text::limit_chars($this->sanitize_text($person->address_city), 23, '');
        $state = Text::limit_chars($this->sanitize_text($person->address_state), 2, '');
        $zip = Text::limit_chars($person->address_zip, 8, '');

        //name
        $name_parts = explode(' ', $name);

        $first_name = isset($name_parts[0]) ? $name_parts[0] : $name;
        $middle_name = isset($name_parts[1]) ? $name_parts[1] : '';
        $last_name = isset($name_parts[2]) ? $name_parts[2] : '';

        //phones
        $phone_area = substr($person->phone, 0, 2);
        $cellphone_area = substr($person->cellphone, 0, 2);

        $data = array('name' => $first_name,
                      'middle_name' => $middle_name,
                      'last_name' => $last_name,
                      'cpf_cnpj' => $person->cpf_cnpj,
                      'gender' => $gender,
                      'email' => $person->email,
                      'birthday' => $birthday,
                      'phone' => $person->phone,
                      'cellphone' => $person->cellphone,
                      'phone_area_code' => $phone_area,
                      'cellphone_area_code' => $cellphone_area,
                      'address' => $address,
                      'address_number' => $number,
                      'address_complement' => $complement,
                      'address_burgh' => $burgh,
                      'address_city' => $city,
                      'address_state' => $this->sanitize_text($person->address_state),
                      'address_zip' => $zip,
                      );

      

        $xml = $this->render_xml('payorder/customer.xml', $data);

        if( !$person->phone ){
            $xml = $this->remove_html_tags($xml, array('phone_home'));
        }

        if( !$person->cellphone ){
            $xml = $this->remove_html_tags($xml, array('phone_mobile'));
        }


        return $xml;

    }

    public function make_xml_payment(Boldcron_Payment $payment){

        $cc = $this->encrypt($payment->cc_number);
        $cvv = $this->encrypt($payment->cc_cvv);
        $value = (string)preg_replace("/\D/","", $payment->value);
        $holder = Text::limit_chars($this->sanitize_text($payment->cc_holder), 38, '');
        $exp_month = str_pad($payment->cc_exp_month, 2, "0", STR_PAD_LEFT);
        $exp_year = str_pad($payment->cc_exp_year, 4, "0", STR_PAD_LEFT);

        $brand = $payment->get_method_brand();
        if($brand == 'master'){
          $brand = 'mastercard';
        }



        $data = array('payment_method' => $payment->method,
                      'payment_value' => $value,
                      'payment_cc_brand' => $brand,
                      'payment_cc_number' => $cc,
                      'payment_cc_cvv' => $cvv,
                      'payment_cc_exp_month' => $exp_month,
                      'payment_cc_exp_year' => $exp_year,
                      'payment_cc_holder' => $holder,
                      );

        $xml = $this->render_xml('payorder/payment.xml', $data);

        //se for boleto remove as tags de cartao
        if( (int)$payment->get_method_type() == 1){

            $xml = $this->remove_html_tags($xml, array('payment_cc_brand',
                                                       'payment_cc_number',
                                                       'payment_cc_cvv',
                                                       'payment_cc_exp_month',
                                                       'payment_cc_exp_year',
                                                       'payment_cc_holder'));

        }

        return $xml;
        

    }

    /* SEND */

    public function make_xml_send(){

        //customers info

        $customer = $this->make_xml_customer_info($this->customer);

        $billing = $this->make_xml_customer_info($this->billing);

        $shipment = $this->make_xml_customer_info($this->shipment);

        $payment = $this->make_xml_payment($this->payment);

        //payorder
        $payorder = array('merch_ref' => $this->merch_ref,
                         'order_subtotal' => (int)$this->value,
                         'order_total' => (int)$this->value,
                         'order_item_code' => $this->item_code,
                         'order_item_description' => $this->description,
                         'order_item_unit_value' => (int)$this->value,
                         'behavior_data_url_post_bell' => $this->bell_url,
                         'payment_data' => $payment,
                         'customer_id' => $this->customer_id,
                         'customer_ip' => $this->customer_ip,
                         'customer_info' => $customer,
                         'shipment_info' => $shipment,
                         'billing_info' => $billing
                         );


        $xml = $this->render_xml('payorder/send.xml',  $payorder );

        if($this->debug){
            $this->_log['make_xml_send__xml'] = $xml;
        }

        return $xml;

    }


    public function send(){
        
        $xml = $this->make_xml_send();

        if($this->debug){
            $this->_log['send__xml'] = $xml;
        }

        //envia ao gateway
        $method = 'payOrder';

        $xml_return = $this->_send_to_gateway($method, $xml);

         
        /*
        //RETORNO De test
        $p = MODPATH.'boldcron/docs/returns/payorder-boleto_bb.xml';
        $xml_return = file_get_contents($p);
        */

        $this->_log['send_return__xml'] = $xml_return;

        //trata o retorno xml
        $obj_xml = simplexml_load_string($xml_return);

        $bpag_data = (array)$obj_xml->bpag_data;

        $this->_process_bpag_data($bpag_data);
        
        return $xml_return;

        
    }


    /* PROBE */

    public function make_xml_probe(){

        $probe = array('merch_ref' => $this->merch_ref,
                        'id' => $this->id);

        $xml = $this->render_xml('payorder/probe.xml',  $probe);

        if(!$this->id){

             $xml = $this->remove_html_tags($xml, array('id'));

        }

        return $xml;


    }


    public function probe(){

        $xml = $this->make_xml_probe();

        if($this->debug){
            $this->_log['probe__xml'] = $xml;
        }


        //envia ao gateway
        $method = 'probe';

        $xml_return = $this->_send_to_gateway($method, $xml);

        //log do retorno
        $this->_log['probe_return__xml'] = $xml_return;

        //trata o xml de retorno
        $obj_xml = simplexml_load_string($xml_return);
        
        $comm_status = (int)$obj_xml->status[0];

        //se houve conexao
        if($comm_status == 0){

            $bpag_data = (array)$obj_xml->order_data->order->bpag_data;

            $this->_process_bpag_data($bpag_data);

        }

        return $xml_return;
        
        
    }


    /* CANCEL */

     public function make_xml_cancel(){

        $probe = array('merch_ref' => $this->merch_ref,
                        'id' => $this->id);

        $xml = $this->render_xml('payorder/cancel.xml',  $probe);

        if(!$this->id){

             $xml = $this->remove_html_tags($xml, array('id'));

        }

        return $xml;


    }


    public function cancel(){

      $xml = $this->make_xml_cancel();

      if($this->debug){
        $this->_log['cancel__xml'] = $xml;
      }


      //envia ao gateway
      $method = 'cancel';

      $xml_return = $this->_send_to_gateway($method, $xml);

      //log do retorno
      $this->_log['cancel_return__xml'] = $xml_return;

      //trata o xml de retorno
      $obj_xml = simplexml_load_string($xml_return);
      
      $comm_status = (int)$obj_xml->status[0];

      //se houve conexao
      if($comm_status == 0){

          $bpag_data = (array)$obj_xml->bpag_data;

          $this->_process_bpag_data($bpag_data);

      }

      return $xml_return;



    }



   public function _process_bpag_data(Array $bpag_data){

      
        //se for boleto
            if($this->payment->get_method_type() == 1){
                if( isset($bpag_data['url']) ){
                    $bpag_data['boleto'] = $bpag_data['url'];            
                }
            }
            
            //remove dados desnecessarios
            $remove = array('url');
            $bpag_data = $this->remove_array_keys($bpag_data, $remove);

            $this->data_return = Arr::merge($this->data_return, $bpag_data);

    }


    public function _send_to_gateway($method, $xml){



        $client = new SoapClient(Kohana::config('boldcron.payorders.wsdl'),
                                array("trace" => 1, "exception" => 0));
        
        $params = array("version" => '1.1.0', 
                        "action" => $method,
                        "merchant" => Kohana::config('boldcron.payorders.merchant'),
                        "user" =>  Kohana::config('boldcron.payorders.user'),
                        "password" => Kohana::config('boldcron.payorders.password'),
                        "data" => $xml,
                        );

        
        $result = $client->doService($params);
        
        $xml_return = $this->sanitize_xml( $result->doServiceReturn );
        
        
        //logs
        Boldcron_Log::write($xml_return, 'PAYORDER-'.$this->merch_ref.'-OUTPUT');  

        if( Kohana::config('boldcron.debug') ){

            Boldcron_Log::write($xml, 'PAYORDER-'.$this->merch_ref.'-INPUT');

        }
        

        
        return $xml_return;

    }

    


}