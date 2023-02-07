<?php
namespace Tualo\Office\HBK;

use Tualo\Office\Basic\TualoApplication as App;

class HlsSpliter extends HlsHelper {

    public static function findAddress($file){
        exec('tesseract '.$file.' stdout',$tessdata);
        
        preg_match_all('/\d{5}\s[\w\d-.]+/',implode("\n",$file),$matches);
        if (count($matches)>0){
            if ( strpos( strtolower( implode("\n",$file) ),'detailansicht' ) === false ){
                return true;
            }
        }
        return false;
    }

    public static function cropAddress($filename){
        $x=177;
        $y=236;
        $w=1181;
        $h=709;
        exec("convert ${file} -crop ${w}x${h}+${x}+${y} ${file}.cropped.jpg",$result);
    }

    public static function loopFiles($path){
        $filelist=[];
        $glob_result = glob($path.'/'.'*.jpg');
        foreach($glob_result as $file){

        }
    }

    public static function countPages($file){
        exec(sprintf('gs -q -dNODISPLAY -c "(%s) (r) file runpdfbegin pdfpagecount = quit"', $file), $res, $ret);
        if(0 <> $ret) throw new \Exception( 'Error ' . $ret);
        return (int) $res[0];
    }

    public static function splitPDF2Jpeg($filename,$path){
        $params = [];
        $params[] = '-q';
        $params[] = '-dNOPAUSE';
        $params[] = '-dDOPDFMARKS=false';
        $params[] = '-dBATCH';
        $params[] = '-sDEVICE=jpeg';
        $params[] = '-r300';
        $params[] = '-sOutputFile="'.implode('/',[$path,'%05d.jpg']).'"';
        $params[] = '"'.$filename.'"';
        exec('gs '.implode(' ',$params),$jpeg);

        $params = [];
        $params[] = '-q';
        $params[] = '-dNOPAUSE';
        $params[] = '-dDOPDFMARKS=false';
        $params[] = '-dBATCH';
        $params[] = '-sDEVICE=pdfwrite';
        $params[] = '-r600';
        $params[] = '-sOutputFile="'.implode('/',[$path,'%05d.jpg']).'"';
        $params[] = '-dPDFFitPage';
        $params[] = '-dFIXEDMEDIA';
        $params[] = '-sPAPERSIZE=a4';
        $params[] = '-dAutoRotatePages=/None';
        $params[] = '"'.$filename.'"';
        exec('gs '.implode(' ',$params),$pdfwrite);

    }

    public static function doXML($filename,$pages){
        file_put_contents( $filename, "<?xml version=\"1.0\" encoding=\"utf-8\"?><JobTicket><Customer>x</Customer><CustomerNumber>1234</CustomerNumber><Mandator>29</Mandator><Firstname>X</Firstname><Lastname>X</Lastname><Street>X</Street><HouseNo>1</HouseNo> <City>X</City><Country></Country><Date>29.01.2021</Date><Time>08:50</Time><Tic_version>2.3.1</Tic_version><LogoFileName></LogoFileName><FileName>sample.pdf</FileName> <Portal_ID>P2-010</Portal_ID><Job_ID>11374</Job_ID><Page_cnt>".$pages."</Page_cnt><Total_weigth>10</Total_weigth><Total_cost>0.65</Total_cost><TicDP><TicPapierformat><value>DIN A4/80gr.</value></TicPapierformat><TicDruckmodus><value>Farbe</value></TicDruckmodus><TicLayout><value>Einseitig</value></TicLayout><TicKuvertgrösse><value>DIN C6/5 (22,9cm x 11,4cm)</value></TicKuvertgrösse><TicSendungsart><value>National</value></TicSendungsart></TicDP></JobTicket>");
    }
}