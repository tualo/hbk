<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\HBK\HlsHelper;

class PdfSplit implements IRoute{

    public static function register(){
        BasicRoute::add('/hbk/pdfsplit',function($matches){
            $db = App::get('session')->getDB();
            App::contenttype('application/json');
            try{

                $jobid = '867625432634';
                ini_set('upload_max_filesize','2GB');
                ini_set('post_max_size','2GB');
                
                $newfile = PDF_SPLIT_PATH.'/'.$jobid.'pdf';
                if (isset($_FILES['userfile'])){
                    $sfile = $_FILES['userfile']['tmp_name'];
                    $name = $_FILES['userfile']['name'];
                    $type = $_FILES['userfile']['type'];
                    $error = $_FILES['userfile']['error'];
                    if ($error == UPLOAD_ERR_OK){
                        move_uploaded_file($sfile, $newfile);
                    if (file_exists($newfile)){
                      $config = json_decode(file_get_contents($newfile),true);
                      unlink($newfile);
                    }
                  }
                }

                App::result('success',true);
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },array('get','post'),true);

    }
}