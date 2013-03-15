<?php defined('SYSPATH') or die('No direct script access.');

class Boldcron_Log {


    public static function write($data, $type='PAYORDER'){

        $directory = Kohana::config('boldcron.logs').date('Y').DIRECTORY_SEPARATOR;
        if ( ! is_dir($directory)){

            mkdir($directory, 0777);
            chmod($directory, 0777);
        }

        $directory .= date('m').DIRECTORY_SEPARATOR;
        if ( ! is_dir($directory)){
            mkdir($directory, 0777);
            chmod($directory, 0777);
        }

       
        $filename = $directory.$type.'-'.date('Y-m-d--h_i_s').'.txt';
        file_put_contents($filename, $data);


    }

}