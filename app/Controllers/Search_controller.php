<?php

	class Search_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'search.html');
		}

		public function main($f3){

			if($f3->exists('POST.search')){
				$f3->set('SESSION.search',$f3->get('POST'));
				$this->search($f3, array('options'=>$f3->get('POST'), 'page'=>1));
			}else if($f3->exists('PARAMS.page') && $f3->exists('SESSION.search')){
				$this->search($f3, array('options'=>$f3->get('SESSION.search'), 'page'=>intval($f3->get('PARAMS.page'))));
			}

			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
			if($user){

				$user_infos = $user->cast();
				$f3->set('user', $user_infos);
			
				$notifications = $user_model->getAllNotificationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
				$notifications_list=array();
				foreach($notifications as $n){
					$notifications_list[$n['notification_type']]=$n['notifications'];
				}
				$f3->set('notifications', $notifications_list);

				$body_model = new Body_model();
				$appareances = $body_model->getAllAppareances(array());
				$f3->set('appareances', $appareances);

				$temperaments = $body_model->getAllTemperaments(array());
				$f3->set('temperaments', $temperaments);

				$sports = $body_model->getAllSports(array());
				$f3->set('sports', $sports);

			}else{
				$this->tpl=array('sync'=>'404.html');
			}
		}

		private function search($f3, $params){

			$options['gender'] = $params['options']['sex']=='homme'?0:1;
			list($options['age_min'], $options['age_max']) = explode(';', (strpos($params['options']['age'],';')!==false?$params['options']['age']:'0;120'), 2);
			$options['city'] = $params['options']['city'];
			list($options['rayon_min'], $options['rayon_max']) = explode(';', (strpos($params['options']['rayon'],';')!==false?$params['options']['rayon']:'1;200'), 2);
			list($options['height_min'], $options['height_max']) = explode(';', (strpos($params['options']['taille'],';')!==false?$params['options']['taille']:'130;220'), 2);
			list($options['weight_min'], $options['weight_max']) = explode(';', (strpos($params['options']['poids'],';')!==false?$params['options']['poids']:'30;300'), 2);
			if($params['options']['appearance']!='all')	$options['appearance'] = $params['options']['appearance'];
			if($params['options']['caractere']!='all')	$options['temperament'] = $params['options']['caractere'];
			$options['sports'] = is_array($f3->get('POST.sport'))?$f3->get('POST.sport'):array();
			list($options['km_min'], $options['km_max']) = explode(';', (strpos($params['options']['km'],';')!==false?$params['options']['km']:'10;100'), 2);
			list($options['cal_min'], $options['cal_max']) = explode(';', (strpos($params['options']['cal'],';')!==false?$params['options']['cal']:'10;100'), 2);

			$options['offset'] = ($params['page']-1)*12;
			$options['row_count'] = 12;

			$f3->set('options', $options);

			/* Correspondance des unités : kg->dg & km->m */
			$options['weight_min'] = intval($options['weight_min'])*10;
			$options['weight_max'] = intval($options['weight_min'])*10;
			$options['rayon_min'] = intval($options['rayon_min'])*1000;
			$options['rayon_max'] = intval($options['rayon_max'])*1000;

			$user_model = new User_model();
			$results = $user_model->searchUsers($options);

			$options['type']='count';
			$count = $user_model->searchUsers($options);

			$f3->set('count_results', (is_array($count) && count($count)>0?$count[0]['count']:0));
			$f3->set('current_page', $params['page']);
			$f3->set('results', $results);
		}
		
	}

?>