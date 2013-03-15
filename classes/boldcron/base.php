<?php defined('SYSPATH') or die('No direct script access.');

abstract class Boldcron_Base{

    var $debug = FALSE;

    var $_log = array();

    var $data_return = array();

    public function __construct(Array $data=array()){

        if( sizeof($data) ){
            $this->values($data);
        }

    }

    public function values(Array $data=array()){

        if(sizeof($data)){

            return $this->_set_values($data);

        }else{

            return $this->_get_values();
        }
        

    }


    public function set($key, $value){

        $vars  = $this->_get_values();

        if( array_key_exists($key, $vars) ){
            $this->$key = $value;
        }

    }


    public function get($key){

        $vars  = $this->_get_values();

        if( array_key_exists($key, $vars) ){
            $result =  $this->$key;
        }else{
            $result = NULL;
        }

        return $result;
    }




    private function _get_values(){

        return get_object_vars($this);

    }

    private function _set_values(Array $data=array()){

        foreach($data as $key => $value){
            $this->set($key, $value);
        }
        
        return $this;
    } 


    public function get_xml_template($template){

        $xml_path = MODPATH.'/boldcron/xml/'.$template;

        return file_get_contents($xml_path);

    }


    public function render_xml($template, Array $data=array()){

        $content = $this->get_xml_template($template);

        foreach($data as $key => $value){

            $content = str_replace('##'.$key.'##', $value, $content);
        }

        return $content;

    }



    public function sanitize_text($str, $enc = "UTF-8"){
        /*
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);  
        */
        
        $accents = array(
                        'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
                        'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;/',
                        'C' => '/&Ccedil;/',
                        'c' => '/&ccedil;/',
                        'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
                        'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
                        'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
                        'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
                        'N' => '/&Ntilde;/',
                        'n' => '/&ntilde;/',
                        'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
                        'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;/',
                        'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
                        'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
                        'Y' => '/&Yacute;/',
                        'y' => '/&yacute;|&yuml;/',
                        'a.' => '/&ordf;/',
                        'o.' => '/&ordm;/');

           return preg_replace($accents,
                               array_keys($accents),
                               htmlentities($str,ENT_NOQUOTES, $enc));
                              
    }

    public function remove_html_tags($content, $tags=array()){
       
        
        $content = preg_replace("/\n/", "##NEWLINE##", $content);

        foreach ($tags as $tag){
            $patern = "/<".$tag.".*".$tag.">/";
            $content =  preg_replace($patern,"", $content);
        }

        $content = preg_replace("/##NEWLINE##/", "\n", $content);
        $content = preg_replace('/\n+/', "\n", $content);
        return $content;


    }


    public function remove_array_keys($array, $keys){
        foreach($keys as $k){
            if(isset($array[$k])){
                unset($array[$k]);
            }
        }
        return $array;
    }


    public function sanitize_xml($xml){
        return preg_replace('#&(?=[a-z_0-9]+=)#', '&amp;', $xml);
    }
    

    public function get_log(){
        return $this->_log;
    }


    public function get_return($key){

        if( isset($this->data_return[$key]) ){
            return $this->data_return[$key];
        }else{
            return NULL;
        }

    }



    public function has_return(){

        return (sizeof($this->data_return)) ? TRUE: FALSE;

    }




}