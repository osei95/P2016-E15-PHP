<?php

	class Fitbit_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		    $this->oauth_controller = new Oauth_controller();
		}

		function auth($f3){
			$vars = $f3->get('FITBIT');
			$auth_response = $this->oauth_controller->oauth_1_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/fitbit');
				exit;
			}
			$fitbit_infos = $this->oauth_controller->oauth_1_0_request(array('conskey' => $vars['conskey'], 'conssec' => $vars['conssec'], 'oauth_token' => $auth_response['oauth_token'], 'oauth_token_secret' => $auth_response['oauth_token_secret'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));		
			$user_model = new User_model();
			$user = $user_model->getUserByInputId(array('input_id'=>$auth_response['encoded_user_id'], 'input_name'=>'FITBIT'));
			if(!$user){	
				if(isset($fitbit_infos['user']['dateOfBirth']) && valid($fitbit_infos['user']['dateOfBirth'],array('','NA',false,null))){
					$exploded_birthday = explode('-', $fitbit_infos['user']['dateOfBirth']);
					$birthday = array('day' => $exploded_birthday[1], 'month' => $exploded_birthday[2], 'year' => $exploded_birthday[0]);
				}else{
					$birthday = null;
				}

				$name = ((isset($fitbit_infos['user']['fullName']) && valid($fitbit_infos['user']['fullName'],array('','NA',false,null)))?explode(' ', $fitbit_infos['user']['fullName'], 2):null);
				$user_infos = array();
				$user_infos['firstname'] = (($name==null || (is_array($name) && count($name)==0))?null:$name[0]);
				$user_infos['lastname'] = (($name==null || (is_array($name) && count($name)<2))?null:$name[1]);
				$user_infos['gender'] = (isset($fitbit_infos['user']['gender']) && valid($fitbit_infos['user']['gender'],array('','NA'))?(($fitbit_infos['user']['gender']=='MALE')?0:1):null);
				$user_infos['birthday'] = $birthday;
				$user_infos['city'] = null;
				$user_infos['postcode'] = null;
				$user_infos['email'] = null;
				$user_infos['description'] = (isset($fitbit_infos['user']['aboutMe']) && valid($fitbit_infos['user']['aboutMe'],array('','NA'))?$fitbit_infos['user']['aboutMe']:null);
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('access_token' => $auth_response['oauth_token'], 'access_secret_token' => $auth_response['oauth_token_secret'], 'input_name' => 'FITBIT', 'input_id'=>$auth_response['encoded_user_id']));	
			}else{
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $auth_response['oauth_token'], 'access_secret_token' => $auth_response['oauth_token_secret']));
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $auth_response['encoded_user_id'], 'oauth' => $auth_response['oauth_token'], 'oauth_secret' => $auth_response['oauth_token_secret']));
				$f3->reroute('/');
			}
		}

		function importActivity($f3, $params){
			$vars = $f3->get('FITBIT');
			$date_request = date('Y-m-d');
			$activity_infos = $this->oauth_controller->oauth_1_0_request(array('conskey' => $vars['conskey'], 'conssec' => $vars['conssec'], 'oauth_token' => $params['access_token'], 'oauth_token_secret' => $params['access_token_secret'], 'url' => $vars['endpoints']['base'].str_replace('{date}', $date_request, $vars['endpoints']['activities'])));					
			
			if(isset($activity_infos['summary']) && is_array($activity_infos['summary'])){	// Si on a une activité ce jour

				$duration = $activity_infos['summary']['fairlyActiveMinutes']+$activity_infos['summary']['veryActiveMinutes'];
				foreach($activity_infos['summary']['distances'] as $distance){
					if($distance['activity']=='total'){
						$distance = $distance['distance'];
						break;
					}	
				}
				$calories = $activity_infos['summary']['activityCalories'];
				$date = date('Ymd');

				$activity_model = new Activity_model();
				$activity = $activity_model->getActivityByShortName(array('activity_shortname' => 'move'));

				if($activity){

					$current_activity = $activity_model->getActivityUserByDate(array('user_id' => $params['user_id'], 'date' => $date, 'activity_id' => $activity->activity_id, 'input_id' => $params['input_id']));
					
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
							$timestamp_date = strtotime($date);

							$activity_model->removeActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity' => $activity));
							$activity_model->addActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $timestamp_date, 'activity_id' => $activity->activity_id, 'activity_input_id' => $params['user_has_input_id'], 'duration' => $duration, 'distance' => $distance, 'calories' => $calories));

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

		function importBody($f3, $params){
			$vars = $f3->get('FITBIT');
			$date_request = date('Y-m-d');
			$body_infos = $this->oauth_controller->oauth_1_0_request(array('conskey' => $vars['conskey'], 'conssec' => $vars['conssec'], 'oauth_token' => $params['access_token'], 'oauth_token_secret' => $params['access_token_secret'], 'url' => $vars['endpoints']['base'].str_replace('{date}', $date_request, $vars['endpoints']['body'])));					

			if(isset($body_infos['body']) && isset($body_infos['body']['weight']) && intval($body_infos['body']['weight'])>0){
				$body_model = new Body_model();
				$date = date('Ymd');
				$body_model->addBodyUser(array('user_id' => $params['user_id'], 'date' => $date, 'weight' => intval($body_infos['body']['weight']*1000), 'height' => (isset($item['height'])?null:intval($item['height']))));
			}
		}
	}

?>