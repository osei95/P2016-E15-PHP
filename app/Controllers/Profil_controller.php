<?php 
	class Profil_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'profil.html');
		}

		function profil($f3){

			$username = ($f3->exists('PARAMS.username')?$f3->get('PARAMS.username'):$f3->get('SESSION.user.user_username'));

			/* Récupération des informations de l'utilisateur */
			$user_model = new User_model();
			$user = $user_model->getUserByUsername(array('username' => $username));
			if($user){
				$user_infos = $user->cast();
				$user_infos['body_weight'] = $user_infos['body_weight'] / 10;
				$user_infos['body_height'] = $user_infos['body_height'] / 100;
				/* Fonction permettant de calculer l'age */
				$date = new DateTime($user_infos['user_birthday']);
				$now = new DateTime();
				$interval = $now->diff($date);
				$user_infos['user_birthday'] = $interval->y;
				/* Convertit la ville en minuscule et ajoute une majuscule à la première lettre */
				$user_infos['user_city'] = strtolower($user_infos['user_city']);
				$user_infos['user_city'] = ucwords(strtolower($user_infos['user_city_name']));
				$f3->set('user', $user_infos);

				/* Récupération des photos des followers */
				$photos_model = new User_model();
				$photos = $photos_model->getAllRelationsByUserId(array('user_id' => $user->user_id,'state'=>1));
				$f3->set('usersPhoto',$photos);

				/* Récupération des news propres à l'utilisateur */
				$news_model = new News_model();
				$news = $news_model->getAllNewsFromUserId(array('user_id' => $user->user_id, 'news_date' => mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time()))));
				$f3->set('news', $news);

				/* Récupération des supports propres à l'utilisateur */
				$supports = $news_model->getAllSupportsByUserId(array('user_id' => $f3->get('SESSION.user.user_id')));
				$support_list=array('news' => array());
				if(is_array($supports)){
					foreach($supports as $s){
						array_push($support_list['news'], $s->news_id);
					}
				}

				$support_list['follow'] = ($user_model->isFollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $user->user_id))?true:false);
				$support_list['is_follow'] = ($user_model->isFollow(array('to' => $f3->get('SESSION.user.user_id'), 'from' => $user->user_id))?true:false);

				$f3->set('supports', $support_list);

				$notifications = $user_model->getAllNotificationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
				$notifications_list=array();
				foreach($notifications as $n){
					$notifications_list[$n['notification_type']]=$n['notifications'];
				}
				$f3->set('notifications', $notifications_list);
				
			}else{
				$this->tpl=array('sync'=>'404.html');
			}
		}

		function support($f3){
			$this->tpl['async']='action.json';
			$news_model = new News_model();
			$support = $news_model->getSupportByUserId(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			if(!$support){
				$action = $news_model->createSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
				$f3->set('action', array('name'=>'support'));
			}else{
				$action = $news_model->removeSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
				$f3->set('action', array('name'=>'unsupport'));
			}
			if(!$f3->get('AJAX'))	$f3->reroute('/');
		}

		function follow($f3){
			$this->tpl['async']='action.json';
			$user_model = new User_model();
			$follow = $user_model->isFollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
			if(!$follow){
				$action = $user_model->follow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
				$f3->set('action', array('name'=>'follow'));
			}else{
				$action = $user_model->unfollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
				$f3->set('action', array('name'=>'unfollow'));
			}
			if(!$f3->get('AJAX'))	$f3->reroute('/');
		}

		function session($f3){
			$this->tpl=array('sync'=>'session.json', 'async'=>'session.json');
		}

		function data($f3){
			$sport = array();
			$graphs = array(
				array('couleur'=>'#f3cd33', 'valeur'=>70, 'texte'=>3, 'restant'=>6),
				array('couleur'=>'#ea9143', 'valeur'=>67, 'texte'=>67),
				array('couleur'=>'#eb6759', 'valeur'=>4.1, 'texte'=>4.1)
			);
			$general = array('distance'=>0, 'calories'=>0, 'duration'=>0);
			$table = array();

			$jours = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
			$mois = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');

			$activity_model = new Activity_model();
			$activity = $activity_model->getAllActivitiesUser(array('user_id' => $f3->get('PARAMS.id_user'), 'time'=>mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time())), 'limit'=>15));
			
			$sumDistance = $activity_model->getSumDistanceUser(array('user_id' => $f3->get('PARAMS.id_user')));
			if(!is_array($sumDistance) || count($sumDistance)==0)	$totalDistance=0;
			else 						$totalDistance=$sumDistance[0]['distance']/1000;

			$level = testLevel($totalDistance);

			$graphs[0]['valeur'] = $level['value'];
			$graphs[0]['texte'] = $level['level'];
			$graphs[0]['restant'] = round($level['leftKm'], 1);

			/* Fonction qui calcule l'activité sur le site */
			$valueDate = time() - (15 * 86400);
			$activity_model = new Activity_model();
			$activityUser = $activity_model->getActivityUser(array('user_id' => $f3->get('PARAMS.id_user'),'limit'=>$valueDate));

			$valueDistanceuser[0]['distance'] = $valueDistanceuser[0]['distance'] / 1000;
			$graphs[2]['valeur'] = round($valueDistanceuser[0]['distance'], 1);
			$graphs[2]['texte'] = round($valueDistanceuser[0]['distance'], 1);

			/* Fonction qui calcule le cercle objectif */
			$user_model = new User_model();
			$valuePercentGoals = $user_model->getAllGoalsByUserId(array('user_id'=>$f3->get('PARAMS.id_user'),'datePresent'=>time()));
			if($valuePercentGoals[0]['goal'] != 0){
				$graphs[1]['valeur'] = round(($valuePercentGoals[0]['goalFail'] * 100)/$valuePercentGoals[0]['goal'],0);
				$graphs[1]['texte'] = round(($valuePercentGoals[0]['goalFail'] * 100)/$valuePercentGoals[0]['goal'],0);
			}
			else {
				$graphs[1]['valeur'] = 0;
				$graphs[1]['texte'] = 0;
			}

			$activity_tab = array();
			foreach($activity as $key => $value){
				$activity_tab[$key]=$value->cast();
			}

			$today = mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time()));

			/* On crée le tableau sans activité */
			for ($i=0; $i < 15; $i++) { 
				$sport[$i]=array('timestamp'=>$today-($i*86400), 'calories'=>0, 'km'=>0, 'duration'=>0);
				$sport[$i]['date']=date("d/m", $sport[$i]['timestamp']);
				$sport[$i]['fulldate']=ucfirst($jours[date("w",$sport[$i]['timestamp'])]).' '.date("d",$sport[$i]['timestamp']).' '.$mois[date("n",$sport[$i]['timestamp'])-1];
			}

			foreach($activity_tab as $a){
				if($a['date']<=$today && $a['date']>=$today-(14*86400)){
					$offset=ceil(($today-$a['date'])/86400-1);
					$sport[$offset]['calories']=($a['calories']>0?$a['calories']:0);
					$sport[$offset]['km']=round($a['distance']/1000, 1);
					$sport[$offset]['duration']=floor($a['duration']/3600).' heures '.floor(($a['duration']%3600)/60).' minutes';
					$general['distance']+=$sport[$offset]['km'];
					$general['calories']+=$sport[$offset]['calories'];
					$general['duration']+=$a['duration'];
				}
			}

			$general['temps'] = floor($general['duration']/3600).'h'.floor(($general['duration']%3600)/60);
			$table['general'] = $general;
			$table['graphs'] = $graphs;
			$table['sport'] = array_reverse($sport);

			echo(json_encode($table));
			exit;

		}
	}
?>