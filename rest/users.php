<?php

namespace REST;

class users extends \REST\api{
  
private $dB;

  function __construct($f3){
    $this->dB=new \DB\SQL('mysql:host='.$f3->get('db_host').';port=3306;dbname='.$f3->get('db_name'),$f3->get('db_login'),$f3->get('db_password'));
  }
  
  function get($f3){
    $mapper=new \DB\SQL\Mapper($this->dB,'user_infos');
    if($f3->exists('PARAMS.user_id')){
      $f3->set('user',$mapper->load(array('user_id=?', intval($f3->get('PARAMS.user_id'))),array('order'=>'user_id')));
      $this->tpl='users.json';
    }else{
      $f3->set('users',$mapper->find(array(),array('order'=>'user_id')));
      $this->tpl='users.json';
    }
  }
  
  function post($f3){
    $f3->error(403);
  }
  
  function put($f3){

  }
  
  function delete($f3){

  }
  
  
  
}
?>