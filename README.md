#Kohana Boldcron

**Sobre o Módulo**

Este módulo foi desenvolvido para uso no Framework [Kohana PHP](http://kohanaframework.org/) e foi testado sobre a versão 3.0.8. Foi desenvolvido de maneira independente da [Boldcron](http://www.boldcron.com.br) , por tanto a mesma não tem responsabilidade alguma sobre o mesmo.


**Contrato UOL-DIVEO / Boldcron**

Este módulo contém classes para o uso do webservice do serviço de pagamentos da [UOL DIVEO / Boldcron](http://www.boldcron.com.br), Para usar estes serviços você deve ter um contrato com a mesma.



##Arquivo de Configuração

	config/boldcron.php


#Webservice: payOrder
---

##Certificados

Os certificados para criptografia dos dados devem ser salvos em certificates/ com os nomes:

* criptografia_dados_prod.cer
* criptografia_dados_prod.crt
* criptografia_dados_prod.pem


##Enviar uma payOrder


Cria um objeto payorder (com debug ativo):


	$payorder_data = array('debug' => TRUE);
	
	$payorder = new Boldcron_Payorder($payorder_data);
	


Cria um objeto Bolcron_Person:	

	$person_data = array('name' => 'ltestenaldo teste',
                                'cpf_cnpj' => 17627038820,
                                'email' => 'mail@host.com',
                                'gender' => 'm',
                                'birthday' => '1982-11-24',
                                'phone' => '30353251',
                                'cellphone' => '88938059',
                                'address' => 'av lalala',
                                'address_number' => '10',
                                'address_complement' => 'complemento',
                                'address_burgh' => 'bairro',
                                'address_city' => 'maceio',
                                'address_state' => 'al',
                                'address_zip' => '57000000');


	$customer = new Boldcron_Person($person_data);
	
	
Cria um objeto de Bolcron_Payment:
	
	$payment_data = array('method' => 'cielows2p_visa',
                          'value' => '777',
                          'cc_number' => '4111117623328424',
                          'cc_cvv' => '123',
                          'cc_exp_month' => '05',
                          'cc_exp_year' => '2016',
                          'cc_holder' => 'ltestenaldo teste',
                          'cc_cpf_cnpj' => '17627038820');


     $payment = new Boldcron_Payment($payment_data);
     

 
Seta os dados da payOrder:
        

        $payorder->set('customer', $customer); // objeto Boldcron_Person
        $payorder->set('shipment', $customer); // objeto Boldcron_Person
        $payorder->set('billing', $customer); // objeto Boldcron_Person
        $payorder->set('value', 777);
        $payorder->set('item_code', '0098');
        $payorder->set('description', 'descricao');
        $payorder->set('bell_url', 'http://www.uol.com.br');
        $payorder->set('payment', $payment);
        $payorder->set('merch_ref', '09876'); //Referencia da payOrder na loja
        $payorder->set('customer_id', '12'); //identificadador do usuario
        

Envia a payorder:

        $xml = $payorder->send();

* $xml: Recebe o XML de retorno do Gateway (opcional)
	

Verificar se houve retorno do gateway  ( Boolean ):

	$boolean = $payorder->has_return());


Recuperar todos os dados de retorno ( Array ):
	
	$return = $payorder->data_return;
	
Recuperar dado de retorno especifico:

	$status = $payorder->get_return('status')
	
__Retorna__: NULL se o dado não existir

Possiveis dados:

* status: código de status na Boldcron
* msg: mensagem do status
* id: id do payorder na Boldcron
* boleto: URL do boleto se a forma de pagamento foi boleto bancário
	

Recuperar logs ( Array ):

    $logs = $payorder->get_log());
        
 
-----
 
##Enviar uma probe (sondar o status da payorder)
 
Definir os parametros da payorder e criar o objeto payOrder:
	
	$payorder_data = array('debug' => TRUE,
                          'merch_ref' => '12345678910',
                          'id' => '1536853',
                          );
    
    $payorder = new Boldcron_Payorder($payorder_data);
    

Enviar a sonda:

	$return = $payorder->probe();
	
* return: recebe o xml de retorno do gateway ( Opcional ):


verificar se houve retorno do gateway  ( Boolean ):

	$boolean = $payorder->has_return());


todos os dados de retorno ( Array ):
	
	$return = $payorder->data_return;
	
dado de retorno especifico:

	$status = $payorder->get_return('status')
	
* Retorna: NULL se o dado não existir

__Possiveis dados:__

* status: código de status na Boldcron
* msg: mensagem do status
* id: id do payorder na Boldcron
* boleto: URL do boleto se a forma de pagamento foi boleto bancário
	

Recuperar logs ( Array ):

    $logs = $payorder->get_log());
    
    

#Webservice: Recorrencia
---


###Criar um nova serie

Cria um customer com Boldcron_Person

	$customer_data = array('name' => 'ltestenaldo é isso ae teste',
                                'cpf_cnpj' => 17627038820,
                                'email' => 'mail@host.com',
                                'gender' => 'm',
                                'birthday' => '1982-11-24',
                                'phone' => 30353251,
                                'cellphone' => 88938059,
                                'address' => 'av lalala',
                                'address_number' => '10',
                                'address_complement' => 'complemento',
                                'address_burgh' => 'bairro',
                                'address_city' => 'maceio',
                                'address_state' => 'al',
                                'address_zip' => 57000000);


       $customer = new Boldcron_Person($customer_data);


Cria um billing com Boldcron_Person:

       $billing_data = array('name' => 'pagador',
                             'cpf_cnpj' => 98745377845,
                             'email' => 'maile@host.com',
                             'phone' => 3361112,
                             );

       $billing = new Boldcron_Person($billing_data);


Cria um objeto de pagamento com Boldcron_Payment:

       $payment_data = array('method' => 'boleto_bb',
                                        'value' => 777,
                                        'cc_number' => '4111117623328424',
                                        'cc_cvv' => 123,
                                        'cc_exp_month' => '05',
                                        'cc_exp_year' => 2016,
                                        'cc_holder' => 'ltestenaldo teste',
                                        'cc_cpf_cnpj' => 17627038820);
       

       $payment = new Boldcron_Payment($payment_data);

Cria uma serie com Boldcron_Serie:

       $serie_data = array('id' => 'serieTeste004',
                            'value' => 1500,
                            'pay_day' => 1,
                            'periodicity' => 'MENSAL',
                            'occurrences' => 30,
                            'payment' => $payment,
                            'customer' => $customer,
                            'billing' => $billing,
                            'debug' => TRUE);

       $serie = new Boldcron_Serie($serie_data);

 Envia a série ( Retorna um Boolean ):
 
      $send = $serie->send(); // boolean
      
      if( $send ){
            echo 'OK';
      }else{
            echo 'ERRO';
      }
      
      
###Cancelar uma série

Cria um serie com Boldcron_Serie:
	
	$serie_data = array('id' => '1-1-20130110083200-kitestil', 'debug' => TRUE);

    $serie = new Boldcron_Serie($serie_data);

Envia o cancelamento ( Retorna um Boolean ):
    
       $cancel = $serie->cancel();

       if( $cancel ){
            echo 'OK';
       }else{
            echo 'ERRO';
       }
       
###Cancelar um ocorrencia

Cria uma serie com Boldcron_Serie:


	$serie_data = array('id' => '1-1-20130110083234-kitestil', 
                            'occurrence_number' => 2,
                            'debug' => TRUE);

    $serie_data = array('id' => '1-1-20130110083234-kitestil', 
                            'debug' => TRUE);

    $serie = new Boldcron_Serie($serie_data);

Envia o cancelamento da ocorrencia (Retorna um Boolean ):


	$cancel = $serie->cancel_occurence();

     if( $cancel ){
            echo 'OK';
     }else{
            echo 'ERRO';
     }
     
 * Se nao for informado o número da ocorrencia "__occurrence_number__", será considerado como "__1__"
 

###Consultar uma série

Cria uma serie com Boldcron_Serie:

	$serie_data = array('id' => '1-1-20130110083234-kitestil', 'debug' => TRUE);

    $serie = new Boldcron_Serie($serie_data);

Envia a sonda (Retorna uma __String__ em caso de sucesso de comunicacao, __FALSE__ se houver erro de comunicação ):

       $probe = $serie->probe();

       //$probe = $serie->probe_occurrence();

       if( $probe ){
            echo $probe;
       }else{
            echo 'ERRO';
       }
       
__Possiveis retornos:__

* EM_ANDAMENTO

* 
 

###Consultar ocorrencia da série

Cria uma serie com Boldcron_Serie:

	$serie_data = array('id' => '1-1-20130110083234-kitestil', 
                            'occurrence_number' => 2,
                            'debug' => TRUE);

    $serie = new Boldcron_Serie($serie_data);

Envia a sonda (Retorna uma __String__ em caso de sucesso de comunicacao, __FALSE__ se houver erro de comunicação ):

       $probe = $serie->probe_occurrence();

       if( $probe ){
            echo $probe;
       }else{
            echo 'ERRO';
       }

__Possiveis retornos:__

* CANCELADA

* EM_ABERTO

* PAGA 

















		
		
		 
 
