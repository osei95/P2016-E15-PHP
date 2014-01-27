<?php
	
	class Activity_model{

		function __construct(){

		}

		function getActivitybyShortName($f3, $params){
			$activity_mapper = new DB\SQL\Mapper($f3->get('dB'),'activity');
			$activity = $activity_mapper->load(array('activity_shortname=?', $params['activity_shortname']));
			return (!$activity?null:$activity);
		}

		function removeActivityUser($f3, $params){

			$user_has_activity_mapper = new DB\SQL\Mapper($f3->get('dB'), 'user_has_activity');
			$user_has_activity = $user_has_activity_mapper->load(array('user_id=? AND input_id=? AND date=? AND activity_id=?', $params['user_id'], $params['input_id'], $params['date'], $params['activity']->activity_id));
			if($user_has_activity!=false)	$user_has_activity->erase();

		}

		function addActivityUser($f3, $params){

			$user_has_activity = new DB\SQL\Mapper($f3->get('dB'), 'user_has_activity');
			$user_has_activity->user_id = $params['user_id'];
			$user_has_activity->input_id = $params['input_id'];
			$user_has_activity->date = $params['date'];
			$user_has_activity->activity_id = $params['activity']->activity_id;
			$user_has_activity->activity_input_id = $params['activity_input_id'];
			$user_has_activity->duration = $params['duration'];
			$user_has_activity->distance = $params['distance'];
			$user_has_activity->calories = $params['calories'];
			$user_has_activity->save();

		}
	}

?>