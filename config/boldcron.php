<?php defined('SYSPATH') or die('No direct script access.');
return array
(
    
    'debug' => FALSE,
    'logs' => APPPATH.'logs/boldcron/',
    'payorders' => array('merchant' => 'MERCHANT',
                        'user' => 'USER',
                        'password' => 'PASSWORD',
                        'wsdl' => 'https://certificacao.bpag.uol.com.br/bpag2/services/BPagWS?wsdl',
                        'bell' => 'http://TESTS.YOUDOMAIN.COM/BELL.php',
                        ),
    'series' => array('merchant' => 'MERCHANT',
                     'user' => 'USER',
                     'password' => 'PASSWORD',
                     'wsdl' => 'https://bpag.uol.com.br/bpagBO/services/BPagBackOfficeWS?wsdl',
                     ),
);
    
