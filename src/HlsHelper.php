<?php
namespace Tualo\Office\HBK;

use Tualo\Office\Basic\TualoApplication as App;

class HlsHelper {
       
    public static function mainlist($data=null){
        $fname=App::get('tempDir').'/mainlist.json';
        if (!is_null($data)){
            file_put_contents($fname,json_encode($data));
        }
        if (!file_exists($fname)) return [];
        return json_decode(file_get_contents($fname),true);
    }
    public static function glob($taskID){
        $data = [];
        $glob_result = glob(App::get('hlsJobDir').'/'.'*.xml');
        foreach($glob_result as $file){
            
            if (count($data)<250){
                $xml = simplexml_load_file($file); 
                $customer = (string)$xml->Customer[0];
                $pagecnt = (int)$xml->Page_cnt;
                $color = (string)$xml->TicDP->TicDruckmodus->value;
                $envelope = (string)$xml->TicDP->xpath('TicKuvertgrösse')[0]->value;
                $layout = (string)$xml->TicDP->TicLayout->value;

                $dataitem=[
                    'id'            => uniqid(),
                    'file'          => $file,
                    'group'         => $envelope.' / '.$color,
                    'customer'      => $customer,
                    'color'         => $color,
                    'envelope'      => $envelope,
                    'processed'     => false,
                    'pages'         => $pagecnt,
                    'shortname'     => basename($file),
                    'pdfname'       => preg_replace('/.xml$/','.pdf',$file),
                    'fonts'         => []
                ];

                exec('pdffonts '.preg_replace('/.xml$/','.pdf',$file),$fonts);
                $dataitem['fontcheck']=$fonts;
                foreach($fonts as $font){
                    $columns = explode(' ',preg_replace( '/\s\s+/',' ',$font));
                    if ( (count($columns) > 0) && ($columns[0] != 'name') && ( strpos($columns[0],'--') != 0) ){

                        $dataitem['fonts'][]=[
                            'name'=> $columns[0],
                            'type'=> $columns[1], 
                            'encoding'=> $columns[2], 
                            'emb'=> $columns[3], 
                            'sub'=> $columns[4], 
                            'uni'=> $columns[5], 
                            'object'=> $columns[6], 
                            'id'=> $columns[7]
                        ];
                    }
                }

                $dataitem['singlepages']=self::singlePages($taskID,$dataitem);

            
                $data[]=$dataitem;
            }
        }
        return $data;
    }

    public static function subPath($taskID,$itemId){
        $tempdir = App::get('tempPath');
        return implode('/',[$tempdir,$taskID,$itemId]);
    }

    public static function singlePages($taskID,$item){

        $pdfPages=[];
        $jpegPages=[];
        
        if (!file_exists( self::subPath($taskID,$item['id']) )) mkdir( self::subPath($taskID,$item['id']) ,0777,true );

        $params = [];
        $params[] = '-q';
        $params[] = '-dNOPAUSE';
        $params[] = '-dDOPDFMARKS=false';
        $params[] = '-dBATCH';
        $params[] = '-sDEVICE=pdfwrite';
        $params[] = '-r600';
        $params[] = '-sOutputFile="'.implode('/',[self::subPath($taskID,$item['id']),'%05d.pdf']).'"';
        $params[] = '-dPDFFitPage';
        $params[] = '-dFIXEDMEDIA';
        $params[] = '-sPAPERSIZE=a4';
        $params[] = '-dAutoRotatePages=/None';
        $params[] = '"'.$item['pdfname'].'"';
        
        exec('gs '.implode(' ',$params),$pdfwrite);

        $glob_result = glob(implode('/',[self::subPath($taskID,$item['id']),'*.pdf']));
        foreach($glob_result as $file){
            $pdfPages[]=$file;
        }

        $params = [];
        $params[] = '-q';
        $params[] = '-dNOPAUSE';
        $params[] = '-dDOPDFMARKS=false';
        $params[] = '-dBATCH';
        $params[] = '-sDEVICE=jpeg';
        $params[] = '-r300';
        $params[] = '-sOutputFile="'.implode('/',[self::subPath($taskID,$item['id']),'%05d.jpg']).'"';
        $params[] = '"'.$item['pdfname'].'"';

        exec('gs '.implode(' ',$params),$jpeg);

        $glob_result = glob(implode('/',[self::subPath($taskID,$item['id']),'*.jpg']));
        foreach($glob_result as $file){
            $jpegPages[]=$file;
        }

        return [
            'cmd'=>'gs '.implode(' ',$params),
            'subPath'=>self::subPath($taskID,$item['id']),
            'pdf' => $pdfPages,
            'images' => $jpegPages
        ];
    }


    public static function omr($taskID){
        $list=[];
        $tempdir = App::get('tempPath');
        $sequence=0;
        $n=0;
        $items = self::mainlist(); //$_SESSION['hbk'][$taskID]['mainlist'];

        foreach($items as $item){
            $baseitem = $item;
            $p=0;
            foreach($item['singlepages']['images'] as $f){
                $baseitem['num'] = $n++;
                $baseitem['id'] = $baseitem['num'];
                $baseitem['image'] = $f;
                $baseitem['highrespdf'] = preg_replace('/.jpg$/','.pdf',$f);
                $baseitem['preview'] = implode('/',['.','hls','preview',$taskID, $item['id'],basename($f)]);
                $baseitem['newletter'] = ($p==0);
                $baseitem['lastpage']  = false;
                $baseitem['sequence']=$sequence;
                $baseitem['printpage']=true;
                $baseitem['omr']='---';
                $baseitem['pagenum']=$p;
                
                $list[]=$baseitem;
                $p+=1;
                $sequence+=1;
                // item.layout="Einseitig"
            }

        }

        $sequenceNum = 0;
        $loopindex   = 0;
        $last_file   = "";
        $list = array_reverse($list);

        foreach($list as &$item){
            if ($item['file']!=$last_file) $item['lastpage']=true;
            $last_file = $item['file'];
        }


        $list = array_reverse($list);

        foreach($list as &$item){
            /*
            if typeof me.sequencesStore[item.color+'|'+item.envelope]=='undefined'
                me.sequencesStore[item.color+'|'+item.envelope]=0
            sequenceNum=me.sequencesStore[item.color+'|'+item.envelope]
            */
            if ($item['pagenum'] % 2 == 0){
                //$seq = decbin($sequenceNum)
                $seq = '1xp1';
                if ($item['lastpage']==true){
                    $seq = str_replace('x','1',$seq);
                }else{
                    $seq = str_replace('x','0',$seq);
                }

                if (substr_count($seq,'1')%2==1){
                    $seq = str_replace('p','1',$seq);
                }else{
                    $seq = str_replace('p','0',$seq);
                }
                $item['omr'] = $seq;
            }
        }


        return $list;
    }


    public static function pdf($taskID){

        $items = self::mainlist(); //$_SESSION['hbk'][$taskID]['mainlist'];

        $data = [
            'sw_dlang'=>[
                'count'=>0,
                'env'=>'C6/DIN Lang',
                'col'=>'Schwarz/ Weiß',
                'data'=>[]
            ],
            'farbe_dlang'=>[
                'count'=>0,
                'env'=>'C6/DIN Lang',
                'col'=>'Farbdruck',
                'data'=>[]
            ],

            'sw_c4'=>[
                'count'=>0,
                'env'=>'C4',
                'col'=>'Schwarz/ Weiß',
                'data'=>[]
            ],
            'farbe_c4'=>[
                'count'=>0,
                'env'=>'C4',
                'col'=>'Farbdruck',
                'data'=>[]
            ]
        ];


        
        foreach($items as $record){

            if ($record['color']=='Schwarz/Weiß'){
                if (in_array($record['envelope'],['DIN C6/5 (22,9cm x 11,4cm)','DIN C6 (22,9cm x 11,4cm)'])){
                    $data['sw_dlang']['data'][]=$record;
                }else{
                    $data['sw_c4']['data'][]=$record;
                }
            }else{
                if (in_array($record['envelope'],['DIN C6/5 (22,9cm x 11,4cm)','DIN C6 (22,9cm x 11,4cm)'])){
                    $data['farbe_dlang']['data'][]=$record;
                }else{
                    $data['farbe_c4']['data'][]=$record;
                }
            }

        }

        $sw = array_merge($data['sw_dlang']['data'],$data['sw_c4']['data']);
        $cl = array_merge($data['farbe_dlang']['data'],$data['farbe_c4']['data']);

        $result=[];


        $tempdir = App::get('tempPath');
        $sOutputFile = implode('/',[$tempdir,$taskID]);

        if (count($sw)>0){
            $params = [];
            $params[] =  '-q';
            $params[] =  '-dNOPAUSE';
            $params[] =  '-dBATCH';
            $params[] =  '-sDEVICE=pdfwrite';
    
            $params[] =  '-sOutputFile='.$sOutputFile.'-sw.pdf';
            $params[] =  '-dPDFFitPage';

            foreach($sw as $p){
                if ($p['omr']!='---'){
                    $params[] =  '-f "'.dirname(__DIR__).'/images'.'/'.$p['omr'].'.ps'.'"';
                }else{
                    $params[] =  '-f "'.dirname(__DIR__).'/images/0000000.ps'.'"';
                }
                $params[] =  '-f "'.$p['highrespdf'].'"';
            }
            exec('gs '.implode(' ',$params),$gsresult);
            $result[]=[
                'name'=> './temp/'.basename(App::get("tempPath")).'/'.basename($sOutputFile.'-sw.pdf')
            ];
        }

        if (count($cl)>0){
            $params = [];
            $params[] =  '-q';
            $params[] =  '-dNOPAUSE';
            $params[] =  '-dBATCH';
            $params[] =  '-sDEVICE=pdfwrite';
    
            $params[] =  '-sOutputFile='.$sOutputFile.'-cl.pdf';
            $params[] =  '-dPDFFitPage';

            foreach($cl as $p){
                if ($p['omr']!='---'){
                    $params[] =  '-f "'.dirname(__DIR__).'/images'.'/'.$p['omr'].'.ps'.'"';
                }else{
                    $params[] =  '-f "'.dirname(__DIR__).'/images/0000000.ps'.'"';
                }
                $params[] =  '-f "'.$p['highrespdf'].'"';
            }
            exec('gs '.implode(' ',$params),$gsresult);
            $result[]=[
                'name'=> './temp/'.basename(App::get("tempPath")).'/'.basename($sOutputFile.'-cl.pdf')
            ];
        }

        return $result;
    }

}
