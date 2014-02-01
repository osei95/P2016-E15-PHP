<?php

   class Controller{
  
      protected $tpl;
      protected $model;

      protected function __construct(){
         $modelName=substr(get_class($this),0,strpos(get_class($this),'_')+1).'model';
         if(class_exists($modelName)){
            $this->model=new $modelName();
         } 
      }

      public function afterroute($f3){
         if($f3->get('AJAX')){
            echo View::instance()->render($this->tpl['async']);
         }else{
            echo View::instance()->render($this->tpl['sync']);
         } 
      }
   }
   
?>