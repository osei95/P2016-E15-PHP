<?php

	class Chat_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'chat.html');
		}

		function main($f3){
			/* Récupération des informations de l'utilisateur */
			$user_model = new User_model();
			$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
			if($user){

				$user_infos = $user->cast();
				$f3->set('user', $user_infos);

				$relations = $user_model->getAllRelationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
				$f3->set('relations', $relations);

				$notifications = $user_model->getAllNotificationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
				$notifications_list=array();
				foreach($notifications as $n){
					$notifications_list[$n['notification_type']]=$n['notifications'];
				}
				$f3->set('notifications', $notifications_list);

			}else{
				$f3->reroute('/');
			}
		}

	}

?>