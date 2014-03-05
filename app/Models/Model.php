<?php
   class Model{
    
      protected $dB;

      protected function __construct(){
         try{
            $f3=\Base::instance();
            $this->dB=new DB\SQL('mysql:host='.$f3->get('db_host').';port=3306;dbname='.$f3->get('db_name'),$f3->get('db_login'),$f3->get('db_password'));
         }catch (Exception $e) {
            error_log($e, 1, 'errors@erwan.co');
         }
      }

      protected function getMapper($table){
         return new \DB\SQL\Mapper($this->dB,$table);
      }

      protected function logdB(){
      	return $this->dB->log();
      }
    
   }
?>