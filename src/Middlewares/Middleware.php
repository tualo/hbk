<?php

namespace Tualo\Office\HBK\Middlewares;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\IMiddleware;

class Middleware implements IMiddleware{
    public static function register(){
        App::use('hbk',function(){
            try{
                App::javascript('hbk_loader', './hbk/loader.js',[],-10000);
            }catch(\Exception $e){
                App::set('maintanceMode','on');
                App::addError($e->getMessage());
            }
        },-100);
    }
}