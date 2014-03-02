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

				$notifications_chat = $user_model->getAllNotificationsByUserIdAndType(array('id' => $f3->get('SESSION.user.user_id'), 'type' => 'message'));
				$notifications_chat_list=array();
				foreach($notifications_chat as $n){
					$notifications_chat_list[$n['notification_from']]=$n['notifications'];
				}
				$f3->set('notifications_chat', $notifications_chat_list);

				if($f3->exists('PARAMS.username')){

					$user_to = $user_model->getUserByUsername(array('username' => $f3->get('PARAMS.username')));
					if($user_to){
						$user_to_infos = $user_to->cast();

						$chat_model = new Chat_model();
						$messages = $chat_model->getUserMessages(array('user_from'=>$user->user_id, 'user_to'=>$user_to->user_id));

						$conversation_infos = array('messages'=>$messages, 'user_from'=>$user_infos, 'user_to'=>$user_to_infos);

						$f3->set('conversation', $conversation_infos);
					}else{
						$this->tpl=array('sync'=>'404.html');
					}
				}

			}else{
				$f3->reroute('/');
			}
		}

	}

?>