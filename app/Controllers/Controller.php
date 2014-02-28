<?php

   class Controller{
  
      protected $tpl;
      protected $model;

      protected function __construct(){

         $f3=\Base::instance();

         if($f3->get('PATTERN')!='/' && strpos($f3->get('PATTERN'),'/register')===false && strpos($f3->get('PATTERN'),'/login')===false && strpos($f3->get('PATTERN'),'/cron')===false && strpos($f3->get('PATTERN'),'/search/cities')===false && !$f3->get('SESSION.user')){
            $f3->reroute('/');
         }

         $modelName=substr(get_class($this),0,strpos(get_class($this),'_')+1).'model';
         if(class_exists($modelName)){
            $this->model=new $modelName();
         } 
      }

      public function afterroute($f3){
         $mimeTypes=array('html'=>'text/html','json'=>'application/json');
         $tpl=($f3->get('AJAX') && isset($this->tpl['async']))?$this->tpl['async']:$this->tpl['sync'];
         $ext=substr($tpl,strrpos($tpl,'.')+1);
         $mime=$mimeTypes[$ext];
         echo View::instance()->render($tpl,$mime);
      } 
   }
   
?>