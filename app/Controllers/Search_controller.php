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

		public function search($f3){
			$options = array();
			$options['gender'] = $f3->get('POST.sex')=='homme'?0:1;
			list($options['age_min'], $options['age_max']) = explode(';', (strpos($f3->get('POST.age'),';')!==false?$f3->get('POST.age'):'0;120'), 2);
			$options['city'] = $f3->get('POST.city');
			list($options['rayon_min'], $options['rayon_max']) = explode(';', (strpos($f3->get('POST.rayon'),';')!==false?$f3->get('POST.rayon'):'1;200'), 2);
			list($options['height_min'], $options['height_max']) = explode(';', (strpos($f3->get('POST.taille'),';')!==false?$f3->get('POST.taille'):'130;220'), 2);
			list($options['weight_min'], $options['weight_max']) = explode(';', (strpos($f3->get('POST.poids'),';')!==false?$f3->get('POST.poids'):'30;300'), 2);
			$options['appearance'] = $f3->get('POST.apparence');
			$options['temperament'] = $f3->get('POST.caractere');
			$options['sports'] = is_array($f3->get('POST.sport'))?$f3->get('POST.sport'):array();
			list($options['km_min'], $options['km_max']) = explode(';', (strpos($f3->get('POST.km'),';')!==false?$f3->get('POST.km'):'10;100'), 2);
			list($options['cal_min'], $options['cal_max']) = explode(';', (strpos($f3->get('POST.cal'),';')!==false?$f3->get('POST.cal'):'10;100'), 2);

			$f3->set('options', $options);

			/* Correspondance des unités : kg->dg & km->m */
			$options['weight_min'] = intval($options['weight_min'])*10;
			$options['weight_max'] = intval($options['weight_min'])*10;
			$options['rayon_min'] = intval($options['rayon_min'])*1000;
			$options['rayon_max'] = intval($options['rayon_max'])*1000;

			$user_model = new User_model();
			$results = $user_model->searchUsers($options);

			$f3->set('results', $results);

			$this->main($f3);
		}
		
	}

?>