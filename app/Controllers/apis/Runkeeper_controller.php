<?php

	class Runkeeper_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		    $this->oauth_controller = new Oauth_controller();
		}

		function auth($f3){
			$vars = $f3->get('RUNKEEPER');
			$auth_response = $this->oauth_controller->oauth_2_0_auth($f3, $vars);
			if(isset($auth_response['error'])){
				$f3->reroute('/login/runkeeper');
				exit;
			}
			$general_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['user']));
			$runkeeper_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $auth_response['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['profile']));
			$user_model = new User_model();
			$user = $user_model->getUserByInputId(array('input_id' => $general_infos['userID'], 'input_name'=>'RUNKEEPER'));
			if(!$user){
				$name = ((isset($runkeeper_infos['name']) && valid($runkeeper_infos['name'],array('','NA',false,null)))?explode(' ', $runkeeper_infos['name'], 2):null);
				$user_infos = array();
				$user_infos['firstname'] = (($name==null || (is_array($name) && count($name)==0))?null:$name[0]);
				$user_infos['lastname'] = (($name==null || (is_array($name) && count($name)<2))?null:$name[1]);
				$user_infos['gender'] = (isset($runkeeper_infos['gender']) && valid($runkeeper_infos['gender'],array('','NA',false,null))?(($runkeeper_infos['gender']=='M')?0:1):null);
				$user_infos['email'] = null;
				$user_infos['description'] = null;
				$f3->set('user_infos', $user_infos);
				$f3->set('SESSION.registration', array('access_token' => $auth_response['access_token'], 'input_name' => 'RUNKEEPER', 'input_id' => $general_infos['userID']));
			}else{
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $auth_response['access_token']));
				$input_model = new Input_model();
				$input_model->updateOauth(array('user_has_input_id' => $general_infos['userID'], 'oauth' => $auth_response['access_token']));
				$f3->reroute('/');
			}
		}

		function importActivity($f3, $params){
			$vars = $f3->get('RUNKEEPER');
			$activity_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $params['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['activities'], 'accept' => 'application/vnd.com.runkeeper.BackgroundActivitySet+json'));

			$today = date('Ymd');

			$activities = array();

			if(isset($activity_infos['items']) && is_array($activity_infos['items'])){	// Si on a une activité ce jour
				foreach($activity_infos['items'] as $activity){
					$exploded_date = explode(' ',str_replace(',', '', $activity['start_time']));
					$date_format = DateTime::createFromFormat('d M Y', $exploded_date[1].' '.$exploded_date[2].' '.$exploded_date[3]);
					$date = $date_format->format('Ymd');
					if($date==$today){
						if($activity['type']=='Running' || $activity['type']=='Cycling' || $activity['type']=='Mountain Biking' || $activity['type']=='Walking'){
							
							switch($activity['type']){
								case 'Walking':
									$type = 'walk';
									break;
								case 'Running':
									$type = 'run';
									break;
								case 'Cycling':
								case 'Mountain Biking':
									$type = 'bike';
									break;
								default :
									$type = '';
							}

							if(!isset($activities[$type]))	$activities[$type] = array('calories' => 0, 'distance' => 0, 'duration' => 0);

							$activities[$type]['calories']+=$activity['total_calories'];
							$activities[$type]['distance']+=$activity['total_distance'];
							$activities[$type]['duration']+=$activity['duration'];
						}
					}
				}
			}

			$activity_model = new Activity_model();

			foreach($activities as $type => $act){
				$activity = $activity_model->getActivitybyShortName(array('activity_shortname' => $type));

				if($activity){
					$activity_model->removeActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $date, 'activity' => $activity));

					$activity_model->addActivityUser(array('user_id' => $params['user_id'], 'input_id' => $params['input_id'], 'date' => $date, 'activity_id' => $activity->activity_id, 'activity_input_id' => $params['user_has_input_id'], 'duration' => $act['duration'], 'distance' => $act['distance'], 'calories' => $act['calories']));
				}
			}

		}

		function importBody($f3, $params){
			$vars = $f3->get('RUNKEEPER');
			$body_infos = $this->oauth_controller->oauth_2_0_request(array('access_token' => $params['access_token'], 'url' => $vars['endpoints']['base'].$vars['endpoints']['body'], 'accept' => 'application/vnd.com.runkeeper.NewWeightSet+json'));
			$today = date('Ymd');

			if(isset($body_infos['items']) && is_array($body_infos['items'])){
				foreach($body_infos['items'] as $info){
					$exploded_date = explode(' ',str_replace(',', '', $info['timestamp']));
					$date_format = DateTime::createFromFormat('d M Y', $exploded_date[1].' '.$exploded_date[2].' '.$exploded_date[3]);
					$date = $date_format->format('Ymd');
					if($date==$today && intval($info['weight'])>0){
						$body_model = new Body_model();
						$date = date('Ymd');
						$body_model->addBodyUser(array('user_id' => $params['user_id'], 'date' => $date, 'weight' => intval($info['weight'])));
					}
				}
			}
		}
	}

?>