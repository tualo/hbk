<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\HBK\HlsHelper;

class PdfPages implements IRoute{

    public static function register(){
        BasicRoute::add('/hls/hybrid/pdfpages',function($matches){
            $db = App::get('session')->getDB();
            App::contenttype('application/json');
            try{
                App::set('hlsJobDir',HLS_JOB_DIR);
                $taskID='123';
        
                set_time_limit(0);   
                ini_set('mysql.connect_timeout','0');
                ini_set('max_execution_time', '0');
                
                App::result('data', HlsHelper::pdf($taskID) );
        
                App::result('success',true);
        
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        
        },array('get','post'),true);

    }
}