<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\HLS\HlsHelper;

class PdfSplit implements IRoute{

    public static function register(){
        BasicRoute::add('/hbk/pdfsplit',function($matches){
            $db = App::get('session')->getDB();
            App::contenttype('application/json');
            try{
                $taskID='123';
                $tempdir = App::get('tempPath');
                $dir =  implode('/',[$tempdir,$taskID]);
                set_time_limit(0);   
                ini_set('mysql.connect_timeout','0');
                ini_set('max_execution_time', '0');
                $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($dir);
                set_time_limit(60*60);
                if (!file_exists($dir)) mkdir( $dir ,0777,true );
                App::result('success',true);
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },array('get','post'),true);

    }
}