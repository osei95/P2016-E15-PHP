<?php

	class Moves_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		    $this->oauth_controller = new Oauth_controller();
		}

		function auth($f3){
			$vars = $f3->get('MOVES');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/moves');
				exit;
			}
			// L'API moves ne donne accès à aucune information personnelle
			//$moves_infos = $this->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId(array('input_id'=>$auth_response['user_id'], 'input_name'=>'MOVES'));
			if(!$user){
				$user_infos = array();
				$user_infos['firstname'] = null;
				$user_infos['lastname'] = null;
				$user_infos['gender'] = null;
				$user_infos['birthday_day'] = null;
				$user_infos['birthday_month'] = null;
				$user_infos['birthday_year'] = null;
				$user_infos['city'] = null;
				$user_infos['postcode'] = null;
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'refresh_token' => $auth_response['refresh_token'], 'input_name' => 'MOVES', 'input_id'=>$auth_response['user_id']));
			}else{
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $auth_response['access_token'], 'refresh_token' => $auth_response['refresh_token']));
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $auth_response['user_id'], 'oauth' => $auth_response['access_token'], 'oauth_refresh_token' => $auth_response['refresh_token']));
				$f3->reroute('/');
			}
		}

		function importActivity($f3, $params){
			$vars = $f3->get('MOVES');
			$today = date('Ymd');
			$activity_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $params['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['activities'].'/'.$today));
			$activity_model = new Activity_model();

			$activities = array();

			if(is_array($activity_infos)){	// Si on a une activité ce jour
				foreach($activity_infos as $items){
					$date = $items['date'];
					if($date==$today || (isset($params['date']) && $params['date']=='all') && is_array($items['summary'])){
						foreach($items['summary'] as $item){
							switch($item['activity']){
								case 'wlk':
									$type = 'walk';
									break;
								case 'run':
									$type = 'run';
									break;
								case 'cyc':
									$type = 'bike';
									break;
								default :
									$type = '';
							}


							if(!isset($activities[$type]))	$activities[$type] = array('calories' => 0, 'distance' => 0, 'duration' => 0);

							$activities[$type]['calories']=((isset($item['calories']))?$item['calories']:-1);
							$activities[$type]['distance']=$item['distance'];
							$activities[$type]['duration']=$item['duration'];

							foreach($activities as $type => $act){
								$activity = $activity_model->getActivityByShortName(array('activity_shortname' => $type));

								if($activity){

									$timestamp_date = strtotime($date);

									$current_activity = $activity_model->getActivityUserByDate(array('user_id' => $params['user_id'], 'date' => $timestamp_date, 'activity_id' => $activity->activity_id, 'input_id' => $params['input_id']));
									
									if($current_activity==false || $act['distance'] > $current_activity->distance || $act['calories'] > $current_activity->calories || $act['duration'] > $current_activity->duration){
										// On poste la nouvelle actualité si la distance parcourue est > à 1km

										if($current_activity==false){
											$current_activity = new stdClass();
											$current_activity->distance=0;
											$current_activity->calories=0;
											$current_activity->duration=0;
										}

										$new_distance = ($act['distance']-$current_activity->distance)/1000;
										$new_calories = $act['calories']-$current_activity->calories;
										$new_duration = $act['duration']-$current_activity->duration;

										if($new_distance>1){

											//$date_time_date = DateTime::createFromFormat('Ymd', $date);
											//$timestamp_date = $date_time_date->getTimestamp();

											$activity_model->removeActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity' => $activity));
											$activity_model->addActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity_id' => $activity->activity_id, 'activity_input_id' => $params['user_has_input_id'], 'duration' => $act['duration'], 'distance' => $act['distance'], 'calories' => $act['calories']));

											$news_model = new News_model();
											$news_model->createNews(array('from' => $params['user_id'], 'to' => 'friends', 'type' => 'activity_distance', 'content' => 'a parcouru '.number_format($new_distance,1).'km'.($new_distance>1?'s':'').' en '.number_format($new_distance,1).'km'.($new_distance>1?'s':'').' en '.(($new_duration>0)?gmdate('H',$new_duration).'h':'').gmdate('i',$new_duration).'minutes.', 'date' => time()));
										
											if($new_calories>1){
												$news_model->createNews(array('from' => $params['user_id'], 'to' => 'friends', 'type' => 'activity_calories', 'content' => ' a perdu '.number_format($new_calories,1).' calories'.($new_calories>1?'s':'').' en '.(($new_duration>0)?gmdate('H',$new_duration).'h':'').gmdate('i',$new_duration).'minutes.', 'date' => time()));
											}

										}
									}
								}
							}
						}
					}
				}
			}

		}
	}

?>