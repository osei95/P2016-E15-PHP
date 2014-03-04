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
						$invitations = $user_model->getAllRelationsByUserId(array('user_id'=>$user->user_id));
						$f3->set('invitations', $invitations);
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
	}
?>