<?php
namespace Tualo\Office\HBK\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use phpseclib3\Net\SFTP;
use Tualo\Office\HBK\HlsHelper;

class GetFiles implements IRoute{
    public static function register(){


        BasicRoute::add('/hbk/getfiles',function($matches){
            $db = App::get('session')->getDB();
            App::contenttype('application/json');
            try{
                App::set('hlsJobDir',HLS_JOB_DIR);
                
                $server = $db->singleRow('select * from hbk_sftp_import limit 1 ',$matches);
                if ($server === false) throw new \Exception('No SFTP configured');
        
                $sftp_server = $server['server'];
                $sftp_port = 22;
                if (substr_count($server['server'],':')==1){
                    list($sftp_server,$sftp_port) = explode(':',$server['server']);
                }
                $sftp_username = $server['username'];
                $sftp_password = $server['password'];
                $sftp_path = $server['path'];
        
        
                $sftp = new SFTP($sftp_server,$sftp_port);
                if (!$sftp->login($sftp_username, $sftp_password)) {
                    throw new \Exception('Login Failed');
                }
                $sftp->enableDatePreservation();
                $sftp->setListOrder('mtime', SORT_ASC);
        
        
                $files = $sftp->nlist($sftp_path.'');
                foreach($files as $fname){
                    if (!in_array($fname,['.','..'])){
        
                        $localfname = basename($fname);
                        $r = $sftp->get( $sftp_path.'/'.$fname , App::get('tempPath') . '/' . $localfname  );
        
                        $zip = new \ZipArchive;
                        if ($zip->open(App::get('tempPath') . '/' . $localfname ) === TRUE) {
                            $zip->extractTo(App::get('hlsJobDir'));
                            $zip->close();
                        } else {
                        }
        
                        $sftp->delete( $sftp_path.'/'.$fname );
                    }
                }

                App::result('msg','Die Daten wurden abgerufen');
                App::result('success',true);
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },array('get','post'),true);

    }
}