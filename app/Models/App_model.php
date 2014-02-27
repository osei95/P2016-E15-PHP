<?php
	
	class App_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user_infos');
		}

		function getCitiesByName($params){
			$mapper=$this->getMapper('cities_list');
			return $this->dB->exec('SELECT city_name, city_slug FROM cities_list WHERE city_name LIKE :city_name ORDER BY CHAR_LENGTH(city_name)', array(':city_name'=>$params['name'].'%'));
		}
	}
?>