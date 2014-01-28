<?php

	class Activity_controller{

		function __construct(){}

		function import_activity($f3, $params){
			switch($params['input_shortname']){
				case 'JAWBONE':
					$jawbone_controller = new Jawbone_controller();
					$jawbone_controller->import_activity($f3, $params);
					break;
				case 'MOVES':
					$moves_controller = new Moves_controller();
					$moves_controller->import_activity($f3, $params);
					break;
				case 'RUNKEEPER':
					$moves_controller = new Runkeeper_controller();
					$moves_controller->import_activity($f3, $params);
					break;
				case 'FITBIT':
					$moves_controller = new Fitbit_controller();
					$moves_controller->import_activity($f3, $params);
					break;
			}
		}
	}

?>