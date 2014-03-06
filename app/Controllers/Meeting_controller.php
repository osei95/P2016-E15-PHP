<?php
	class Meeting_controller extends Controller{

		protected $tpl;
	  	protected $model;
	  	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'meetings.html');
		}

		public function main($f3){
			/* Récupération des informations de l'utilisateur */
			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
			if($user){

				$user_infos = $user->cast();
				$f3->set('user', $user_infos);

				$goals = $user_model->getAllGoalsToByUserId(array('id'=>$user->user_id));
				$goals_list = array();
				if(is_array($goals)){
					foreach($goals as $key => $g){
						$goals_list[$key] = $g;
					}
				}
				$f3->set('goals', $goals_list);

				// On supprime les notifications en rapport avec les rencontres
				$user_model->removeNotificationsByUserId(array('id' => $user->user_id, 'type'=>'relation'));

				$notifications = $user_model->getAllNotificationsByUserId(array('id' => $user->user_id));
				$notifications_list=array();
				foreach($notifications as $n){
					$notifications_list[$n['notification_type']]=$n['notifications'];
				}
				$f3->set('notifications', $notifications_list);
			}else{
				$this->tpl=array('sync'=>'404.html');
			}
		}

		public function meetings($f3){
			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
			if($user){

				$type = $f3->exists('POST.type')?$f3->get('POST.type'):'meetings';

				switch($type){
					case 'meetings':
						$this->tpl=array('sync'=>'meetings.json', 'async'=>'meetings.json');
						$meetings = $user_model->getAllRelationsByUserId(array('user_id'=>$user->user_id, 'state'=>1));
						$f3->set('meetings', $meetings);
						break;
					case 'invitations':
						$this->tpl=array('sync'=>'invitations.json', 'async'=>'invitations.json');

						/* On récupère les objectifs */
						$goals = $user_model->getAllGoalsFromByUserId(array('id'=>$user->user_id, 'deadline'=>time()));
						$goals_list = array();
						foreach($goals as $key => $g){
							$goals_list[$g->user_to_id] = $g->cast();
						}

						/* On récupère les invitations */
						$invitations = $user_model->getAllRelationsByUserId(array('user_id'=>$user->user_id));
						$invitations_list = array();
						foreach($invitations as $key => $i){
							$i['goal'] = (isset($goals_list[$i['request_from']])?array('state' => 'true', 'id'=> $goals_list[$i['request_from']]['goal_id']):array('state' => 'false', 'id'=> ''));
							$invitations_list[$key] = $i;
						}
						$f3->set('invitations', $invitations_list);
						break;
					case 'goals':
						$this->tpl=array('sync'=>'goals.json', 'async'=>'goals.json');
						$goals = $user_model->getAllGoalsToByUserId(array('id'=>$user->user_id));
						$goals_list = array();
						if(is_array($goals)){
							foreach($goals as $key => $g){
								$goals_list[$key] = $g;
							}
						}
						$f3->set('goals', $goals_list);
						break;
				}
			}
		}

		public function replyInvitation($f3){
			$json = array('action'=>false);
			if($f3->exists('POST.user_id') && $f3->exists('POST.reply')){
				$user_model = new User_model();
				$user_from = intval($f3->get('POST.user_id'));
				$user_to = $f3->get('SESSION.user.user_id');
				$retour = $user_model->updateRelation(array('from'=>$user_from, 'to'=>$user_to, 'state'=>intval($f3->get('POST.reply'))));
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