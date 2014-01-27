<?php

	class Activity_controller{

		private $oauth_controller;

		function __construct(){
			$this->oauth_controller = new Oauth_controller();
		}

		function import_activities($f3, $params){
			switch($params['input_shortname']){
				case 'JAWBONE':
					$this->jawbone_import_activities($f3, $params);
					break;
			}
		}

		function jawbone_import_activities($f3, $params){
			$vars = $f3->get('JAWBONE');
			$activity_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $f3->get('SESSION.user.access_token'), 'url' => $vars['endpoints']['base'].$vars['endpoints']['activities']));
			
			$today = date('Ymd');
			$activity_model = new Activity_model();

			$items = $activity_infos['data']['items'];
			foreach($items as $item){
				$date = $item['date'];
				if($date==$today){
					$type = $item['type'];
					$duration = $item['time_completed']-$item['time_created'];
					$distance = $item['details']['distance'];
					$calories = $item['details']['calories'];

					$activity = $activity_model->getActivitybyShortName($f3, array('activity_shortname' => $type));

					if($activity!=null){
						$activity_model->removeActivityUser($f3, array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $date, 'activity' => $activity));

						$activity_model->addActivityUser($f3, array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $date, 'activity' => $activity, 'activity_input_id' => $params['user_has_input_id'], 'duration' => $duration, 'distance' => $distance, 'calories' => $calories));
					}
				}
			}

		}
	}

?>