<?php defined('SYSPATH') or die('No direct script access.');

class Boldcron_Serie extends Boldcron_Base{

    private $_periodicities =  array('DIARIO',
                                    'SEMANAL',
                                    'QUINZENAL',
                                    'MENSAL',
                                    'BIMESTRAL',
                                    'TRIMESTRAL',
                                    'QUADRIMESTRAL');

    var $debug = FALSE;

    var $_log = array();

    var $data_return = array();

    var $value;

    var $id; //id na boldcron

    var $pay_day;

    var $periodicity;

    var $occurrences;

    var $occurrence_number = 1;

    //Boldcron_Payment object
    var $payment;

    // Boldcron_Base objects
    var $customer;

    var $billing;

    public function __construct(Array $data=array()){

        $this->set('payment', new Boldcron_Payment());
        $this->set('customer', new Boldcron_Person());
        $this->set('billing', new Boldcron_Person());

        parent::__construct($data);

    }


    public function get_periodicity(){

        $p = strtoupper( trim($this->periodicity) );

        if(!in_array($p, $this->_periodicities)) {
            $p = 'MENSAL';
        }

        return $p;

        
    }


    public function make_xml_send(){


        $total_value = (int)$this->occurrences * (int)$this->value;

        $periodicity = $this->get_periodicity();

        $cc_holder = Text::limit_chars($this->sanitize_text($this->payment->cc_holder), 39, '');

        $serie = array('name' => $this->sanitize_text($this->customer->name),
                       'cpf_cnpj' => $this->customer->cpf_cnpj,
                       'phone' => $this->customer->phone,
                       'email' => $this->customer->email,
                       'address' => $this->sanitize_text($this->customer->address),
                       'address_number' => $this->customer->address_number, 
                       'address_burgh' => $this->sanitize_text($this->customer->address_burgh), 
                       'address_city' => $this->sanitize_text($this->customer->address_city), 
                       'address_state' => strtoupper($this->customer->address_state), 
                       'address_zip' => $this->customer->address_zip, 
                       'phone' => $this->customer->phone, 
                       'cellphone' => $this->customer->cellphone, 
                       'birthday' => $this->customer->birthday, 
                       'id' => $this->id, 
                       'pay_day' => $this->pay_day, 
                       'billing_name' => $this->sanitize_text($this->billing->name),
                       'billing_cpf_cnpj' => $this->billing->cpf_cnpj,
                       'billing_phone' => $this->billing->phone,
                       'billing_email' => $this->billing->email,
                       'total_value' => $total_value,
                       'periodicity' => $this->get_periodicity(),
                       'occurrences' => (int)$this->occurrences,
                       'payment_method' => $this->payment->method,
                       'payment_type' => $this->payment->get_method_type(),
                       'payment_cc_brand' => strtoupper($this->payment->get_method_brand()),
                       'payment_cc_number' => $this->payment->cc_number,
                       'payment_cc_cvv' => $this->payment->cc_cvv,
                       'payment_cc_expire' => $this->payment->cc_exp_month.$this->payment->cc_exp_year,
                       'payment_cc_holder' => $cc_holder,
                       'payment_cc_cpf_cnpj' => $this->payment->cc_cpf_cnpj,
                       );


        $xml = $this->render_xml('serie/send.xml',  $serie);

        //se for boleto
        if( $this->payment->get_method_type() == 1 ){

             $xml = $this->remove_html_tags($xml, array('cartaoCredito'));


        }


        return $xml;
    }


    public function send(){

        $xml = $this->make_xml_send();

        if($this->debug){
            $this->_log['send__xml'] = $xml;
        }

        //envia ao gateway
        $method = 'RecorrenciaAssinatura';

        $xml_return = $this->_send_to_gateway($method, $xml);

        $this->_log['send_return__xml'] = $xml_return;


        //objeto para maniuplar o xml
        $obj_xml = simplexml_load_string($xml_return);

        $comm_status = (int)$obj_xml->Status[0];

         //se houve conexao
        if($comm_status == 0){

            return TRUE;
            
        }else{

            return FALSE;
        }


    }



    public function make_cancel_xml(){

        $serie = array('id' => $this->id);

        $xml = $this->render_xml('serie/cancel.xml',  $serie);

        return $xml;

    }


    public function cancel(){

        $xml = $this->make_cancel_xml();

        if($this->debug){
            $this->_log['cancel__xml'] = $xml;
        }

        //envia ao gateway
        $method = 'RecorrenciaCancelarSerie';

        $xml_return = $this->_send_to_gateway($method, $xml);

        $this->_log['cancel__xml'] = $xml_return;

         //objeto para maniuplar o xml
        $obj_xml = simplexml_load_string($xml_return);

        $comm_status = (int)$obj_xml->Status[0];

        if($comm_status == 0){

            return TRUE;
            
        }else{

            return FALSE;
        }

    }



    public function make_cancel_ocurrence_xml(){

        $serie = array('id' => $this->id,
                        'occurrence_number' => $this->occurrence_number);

        $xml = $this->render_xml('serie/cancel_occurrence.xml',  $serie);

        return $xml;

    }


    public function cancel_occurence(){

        $xml = $this->make_cancel_ocurrence_xml();

        if($this->debug){
            $this->_log['cancel_occurrence__xml'] = $xml;
        }


        //envia ao gateway
        $method = 'RecorrenciaCancelarOcorrencia';

        $xml_return = $this->_send_to_gateway($method, $xml);

        $this->_log['cancel_occurence__xml'] = $xml_return;

         //objeto para maniuplar o xml
        $obj_xml = simplexml_load_string($xml_return);

        $comm_status = (int)$obj_xml->Status[0];

        if($comm_status == 0){

            return TRUE;
            
        }else{

            return FALSE;
        }


    }




    public function make_xml_probe($subject=0){

        //subject 0: serie,  1: ocorrencia

        $serie = array('id' => $this->id,
                        'subject' => $subject,
                        'occurrence_number' => $this->occurrence_number);

        $xml = $this->render_xml('serie/probe.xml',  $serie);


        if(!$subject){

            $xml = $this->remove_html_tags($xml, array('codOcorrencia'));

        }

        return $xml;


    }


    public function _probe($subject=0){

        $xml = $this->make_xml_probe($subject);

        if($this->debug){
            $this->_log['probe__xml'] = $xml;
        }

        $method = 'RecorrenciaConsultarStatus';

        $xml_return = $this->_send_to_gateway($method, $xml);

        $this->_log['probe_'.$subject.'__xml'] = $xml_return;

         //objeto para maniuplar o xml
        $obj_xml = simplexml_load_string($xml_return);

        $comm_status = (int)$obj_xml->Status[0];

        if($comm_status == 0){

            $message = (string)$obj_xml->Message[0];

            
            return $message;
            
        }else{

            return FALSE;
        }


    }


    public function probe(){
        return $this->_probe(0);
    }


    public function probe_occurrence(){
        return $this->_probe(1);
    }




    public function _send_to_gateway($method, $xml){

        $client = new SoapClient(Kohana::config('boldcron.series.wsdl'),  
                                array("trace" => 1, "exception" => 0));

        $params = array("versao" => '1.0.0', 
                "acao" => $method,
                "codigoLoja" => Kohana::config('boldcron.series.merchant'),
                "login" => Kohana::config('boldcron.series.user'),
                "senha" => Kohana::config('boldcron.series.password'),
                "data" => $xml,
                );


        $result = $client->doService($params);

        $xml_return = $this->sanitize_xml( $result->doServiceReturn ); 


        //logs
        Boldcron_Log::write($xml_return, 'SERIE-'.$this->id.'-OUTPUT');

        if( Kohana::config('boldcron.debug') ){

            Boldcron_Log::write($xml, 'SERIE-'.$this->id.'-INPUT');

        }

        return $xml_return;


    }
    

}