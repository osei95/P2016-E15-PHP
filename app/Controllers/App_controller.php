<?php

	class App_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'home.html');
		}

		function home($f3){
			if($f3->exists('SESSION.user')){
				$this->tpl=array('sync'=>'dashboard.html');

				/* Récupération des informations de l'utilisateur */
				$user_model = new User_model();
				$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
				if($user){

					$user_infos = $user->cast();
					$f3->set('user', $user_infos);

					/* Récupération des objectis */
					$goals = $user_model->getAllGoalsToByUserId(array('id' => $f3->get('SESSION.user.user_id')));
					$goals_infos = array();
					foreach($goals as $key => $g){
						$goals_infos[$key] = $g->cast();
					}
					$f3->set('goals', $goals_infos);

					/* Récupération des news */
					$news_model = new News_model();
					$news = $news_model->getAllNews(array('user_id' => $f3->get('SESSION.user.user_id'), 'news_date'=>mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time())), 'offset'=>0, 'limit'=>10));
					$f3->set('news', $news);

					/* Récupération des supports propres à l'utilisateur */
					$supports = $news_model->getAllSupportsByUserId(array('user_id' => $f3->get('SESSION.user.user_id')));
					$support_list=array('news' => array());
					if(is_array($supports)){
						foreach($supports as $s){
							array_push($support_list['news'], $s->news_id);
						}
					}
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
		}

		public function logout($f3){
			session_destroy();
			$f3->reroute('/');
		}

		function support($f3){
			$news_model = new News_model();
			$support = $news_model->getSupportByUserId(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			if(!$support){
				$news_model->createSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			}else{
				$news_model->removeSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			}
			$f3->reroute('/');
		}

		function news($f3){
			$this->tpl=array('sync'=>'news.json', 'async'=>'news.json');

			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
			if($user){

				$type = $f3->exists('POST.type')?$f3->get('POST.type'):'all_news';
				$offset = $f3->exists('POST.offset')?$f3->get('POST.offset'):0;
				$limit = $f3->exists('POST.limit')?$f3->get('POST.limit'):5;

				/* Récupération des news */
				$news_model = new News_model();
				if($type=='relations_news')			$news = $news_model->getAllRelationsNews(array('user_id' => $user->user_id, 'news_date'=>mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time())), 'offset'=>$offset, 'limit'=>$limit));
				elseif($type=='followings_news')	$news = $news_model->getAllFollowingsNews(array('user_id' => $user->user_id, 'news_date'=>mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time())), 'offset'=>$offset, 'limit'=>$limit));
				elseif($type=='all_news')			$news = $news_model->getAllNews(array('user_id' => $user->user_id, 'news_date'=>mktime(23, 59, 59, date('m',time()), date('d',time()), date('Y',time())), 'offset'=>$offset, 'limit'=>$limit));
				$f3->set('news', $news);

				/* Récupération des supports propres à l'utilisateur */
				$supports = $news_model->getAllSupportsByUserId(array('user_id' => $user->user_id));
				$support_list=array('news' => array());
				if(is_array($supports)){
					foreach($supports as $s){
						array_push($support_list['news'], $s->news_id);
					}
				}
				$f3->set('supports', $support_list);
				
			}
		}

		function user($f3){
			if($f3->exists('POST.id'))	$user_id = intval($f3->get('POST.id'));
			else 						$user_id = $f3->get('SESSION.user.user_id');
			
			$this->tpl=array('sync'=>'user.json', 'async'=>'user.json');
			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $user_id));
			if($user){
				$user_infos = $user->cast();
				$user_infos['body_weight'] = ceil($user_infos['body_weight']/10);
				$f3->set('user', $user_infos);
			}
		}

		function goal($f3){
			if($f3->exists('POST.id') && $f3->exists('POST.type')){

				$this->tpl=array('sync'=>'goal.json', 'async'=>'goal.json');

				if($f3->get('POST.type')=='to'){
					$user_from = $f3->get('SESSION.user.user_id');
					$user_to = intval($f3->get('POST.id'));
				}else{
					$user_to = $f3->get('SESSION.user.user_id');
					$user_from = intval($f3->get('POST.id'));
				}

				$user_model = new User_model();
				$user = $user_model->getUserById(array('id' => $user_to));
				if($user){
					$user_infos = $user->cast();
					$user_infos['body_weight'] = ceil($user_infos['body_weight']/10);
					$f3->set('user', $user_infos);
					$goal = $user_model->getGoalByUsersId(array('from' => $user_from, 'to'=>$user_to));
					if($goal){
						$goal_infos = $goal->cast();
						$f3->set('goal', $goal_infos);
					}
				}
			}
		}

		function addGoal($f3){
			if($f3->exists('POST.user_id') && $f3->exists('POST.distance') && $f3->exists('POST.duration')){
				$user_model = new User_model();
				$user_to = intval($f3->get('POST.user_id'));
				$user_from = intval($f3->get('SESSION.user.user_id'));
				// Le destinataire de l'objectif était celui ayant lancé l'invitation
				$invitation = $user_model->isInvitation(array('from'=>$user_to, 'to'=>$user_from, 'state'=>0));
				if($invitation){
					$distance = intval($f3->get('POST.distance'))*1000;	//conversion km -> m
					$unit = 'distance';
					$date = time();
					$deadline = time()+(intval($f3->get('POST.duration'))*86400);
					$user_model->addGoalUser(array('from'=>$user_from, 'to'=>$user_to, 'date'=>$date, 'deadline'=>$deadline, 'value'=>$distance, 'unit'=>$unit));
					/* Envoi d'une notification à l'utilisateur distant */
					$user_model->addNotification(array('from'=>$user_from, 'user_id'=>$user_to, 'content'=>'', 'type'=>'relation'));
				}
			}
			$f3->reroute('/meetings');
		}

		function mobile($f3){
			$this->tpl=array('sync'=>'mobile.html');
		}

		public function replyGoal($f3){
			$json = array('action'=>false);
			if($f3->exists('POST.user_id') && $f3->exists('POST.reply')){
				$user_model = new User_model();
				$user_from = intval($f3->get('POST.user_id'));
				$user_to = $f3->get('SESSION.user.user_id');
				$retour = $user_model->updateGoal(array('from'=>$user_from, 'to'=>$user_to, 'accepted'=>intval($f3->get('POST.reply'))));
				if($retour){
					/* Envoi d'une notification à l'utilisateur distant */
					$user_model->addNotification(array('from'=>$user_to, 'user_id'=>$user_from, 'content'=>'', 'type'=>'relation'));
					$json['action'] = true;
				}
			}
			echo(json_encode($json));
			exit;
		}
		
	}

?>