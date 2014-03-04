<?php

	class Jawbone_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		    $this->oauth_controller = new Oauth_controller();
		}

		function auth($f3){
			$vars = $f3->get('JAWBONE');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/jawbone');
				exit;
			}
			$jawbone_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId(array('input_id'=>$jawbone_infos['meta']['user_xid'], 'input_name'=>'JAWBONE'));
			if(!$user){
				$user_infos = array();
				$user_infos['firstname'] = (isset($jawbone_infos['data']['first']) && valid($jawbone_infos['data']['first'],array('','NA',false,null))?$jawbone_infos['data']['first']:null);
				$user_infos['lastname'] = (isset($jawbone_infos['data']['last']) && valid($jawbone_infos['data']['last'],array('','NA',false,null))?$jawbone_infos['data']['last']:null);
				$user_infos['gender'] = (isset($jawbone_infos['data']['gender']) && valid($jawbone_infos['data']['gender'],array('','NA',false,null))?(($jawbone_infos['data']['gender']=='MALE')?0:1):null);
				$user_infos['birthday_day'] = null;
				$user_infos['birthday_month'] = null;
				$user_infos['birthday_year'] = null;
				$user_infos['city'] = null;
				$user_infos['postcode'] = null;
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'refresh_token' => $auth_response['refresh_token'], 'input_name' => 'JAWBONE', 'input_id'=>$jawbone_infos['meta']['user_xid']));
			}else{
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $auth_response['access_token'], 'refresh_token' => $auth_response['refresh_token']));
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $jawbone_infos['meta']['user_xid'], 'oauth' => $auth_response['access_token'], 'oauth_refresh_token' => $auth_response['refresh_token']));
				$f3->reroute('/');
			}
		}

		function importActivity($f3, $params){
			$vars = $f3->get('JAWBONE');
			$activity_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $params['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['activities']));
			$today = date('Ymd');
			$activity_model = new Activity_model();

			if(isset($activity_infos['data']['items']) && is_array($activity_infos['data']['items'])){	// Si on a une activité ce jour

				$items = $activity_infos['data']['items'];

				foreach($items as $item){
					$date = $item['date'];
					if($date==$today || (isset($params['date']) && $params['date']=='all')){
						$type = $item['type'];
						$duration = $item['details']['active_time'];
						$distance = $item['details']['distance'];
						$calories = $item['details']['calories'];

						$activity = $activity_model->getActivityByShortName(array('activity_shortname' => $type));

						if($activity){

							$timestamp_date = strtotime($date);

							$current_activity = $activity_model->getActivityUserByDate(array('user_id' => $params['user_id'], 'date' => $timestamp_date, 'activity_id' => $activity->activity_id, 'input_id' => $params['input_id']));
							
							if($current_activity==false || $distance > $current_activity->distance || $calories > $current_activity->calories || $duration > $current_activity->duration){
								// On poste la nouvelle actualité si la distance parcourue est > à 1km

								if($current_activity==false){
									$current_activity = new stdClass();
									$current_activity->distance=0;
									$current_activity->calories=0;
									$current_activity->duration=0;
								}

								$new_distance = ($distance-$current_activity->distance)/1000;
								$new_calories = $calories-$current_activity->calories;
								$new_duration = $duration-$current_activity->duration;

								if($new_distance>1){

									//$date_time_date = DateTime::createFromFormat('Ymd', $date);
									//$timestamp_date = $date_time_date->getTimestamp();

									$activity_model->removeActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity' => $activity));
									$activity_model->addActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity_id' => $activity->activity_id, 'activity_input_id' => $params['user_has_input_id'], 'duration' => $duration, 'distance' => $distance, 'calories' => $calories));

									$news_model = new News_model();
									$news_model->createNews(array('from' => $params['user_id'], 'to' => 'friends', 'type' => 'activity_distance', 'content' => 'a parcouru '.number_format($new_distance,1).'km'.($new_distance>1?'s':'').' en '.(($new_duration>0)?gmdate('H',$new_duration).'h':'').gmdate('i',$new_duration).'minutes.', 'date' => time()));
								
									if($new_calories>1){
										$news_model->createNews(array('from' => $params['user_id'], 'to' => 'friends', 'type' => 'activity_calories', 'content' => ' a perdu '.number_format($new_calories,1).' calorie'.($new_calories>1?'s':'').' en '.(($new_duration>0)?gmdate('H',$new_duration).'h':'').gmdate('i',$new_duration).'minutes.', 'date' => time()));
									}

								}
							}
						}
					}
				}
			}
		}

		function importBody($f3, $params){
			$vars = $f3->get('JAWBONE');
			$activity_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $params['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['body']));
			$today = date('Ymd');

			if(isset($activity_infos['data']['items']) && is_array($activity_infos['data']['items'])){

				$items = $activity_infos['data']['items'];

				foreach($items as $item){
					$date = $item['date'];
					if(($date==$today || (isset($params['date']) && $params['date']=='all')) && intval($item['weight'])>0){
						$body_model = new Body_model();
						$body_model->addBodyUser(array('user_id' => $params['user_id'], 'date' => $today, 'weight' => intval($item['weight']*1000), 'height' => (isset($item['height'])?null:intval($item['height']*1000))));
					}
				}
			}
		}
	}

?>