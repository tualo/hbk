<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;


class CleanJobs implements IRoute{
    public static function register(){


        BasicRoute::add('/hbk/cleansjobs',function($matches){
            App::contenttype('application/json');
            try{
                $db = App::get('session')->getDB();
                $taskID='123';
        
                $tempdir = App::get('tempPath');
        
        
                set_time_limit(0);   
                ini_set('mysql.connect_timeout','0');
                ini_set('max_execution_time', '0');
                
                $items = $_SESSION['hbk'][$taskID]['mainlist'];
        
                foreach($items as $c){
                    unlink( $c['file'] );
                }
        
                $dir =  implode('/',[$tempdir,$taskID]);
        
                
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
        
                App::result('msg','Alle Daten wurden gelöscht');
                
                App::result('success',true);
        
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },array('get','post'),true);

    }
}