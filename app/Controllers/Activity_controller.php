<?php

	class Activity_controller extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('activity');
		} 

		function importActivity($f3, $params){
			switch($params['input_shortname']){
				case 'JAWBONE':
					$jawbone_controller = new Jawbone_controller();
					$jawbone_controller->importActivity($f3, $params);
					break;
				case 'MOVES':
					$moves_controller = new Moves_controller();
					$moves_controller->importActivity($f3, $params);
					break;
				case 'RUNKEEPER':
					$moves_controller = new Runkeeper_controller();
					$moves_controller->importActivity($f3, $params);
					break;
				case 'FITBIT':
					$moves_controller = new Fitbit_controller();
					$moves_controller->importActivity($f3, $params);
					break;
			}
		}
	}

?>