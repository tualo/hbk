<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\HBK\HlsHelper;

class Preview implements IRoute{

    public static function register(){
        BasicRoute::add('/hls/hybrid/preview',function($matches){
            try{
                $db = App::get('session')->getDB();
                App::contenttype('application/json');
                App::set('hlsJobDir',HLS_JOB_DIR);
                $taskID='123';
                $_SESSION['hbk'][$taskID]['mainlist'] = HlsHelper::omr($taskID);
                set_time_limit(0);   
                ini_set('mysql.connect_timeout','0');
                ini_set('max_execution_time', '0');
                App::result('data', $_SESSION['hbk'][$taskID]['mainlist'] );
                App::result('success',true);
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },array('get','post'),true);
        BasicRoute::add('/hls/preview/(?P<taskID>[\w\-\_]+)/(?P<itemID>[\w\-\_]+)/(?P<image>[\w\-\_\.]+)',function($matches){
            if (isset($_SESSION['hbk'][$matches['taskID']])){
                readfile( HlsHelper::subpath($matches['taskID'],$matches['itemID'] ).'/'.$matches['image']);
                exit();
            }
        },array('get','post'),true);
    }
}