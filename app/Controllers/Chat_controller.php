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

				/* Si on doit charger une conversation */
				if($f3->exists('PARAMS.username')){
					$user_to = $user_model->getUserByUsername(array('username' => $f3->get('PARAMS.username')));
				}else{
					$chat_model = new Chat_model();
					$last_message = $chat_model->getLastMessage(array('user_id'=>$f3->get('SESSION.user.user_id')));
					if(is_array($last_message) && count($last_message)>0){
						$user_last_message = ($last_message[0]->message_to==$f3->get('SESSION.user.user_id')?$last_message[0]->message_from:$last_message[0]->message_to);
						$user_to = $user_model->getUserById(array('id' => $user_last_message));
					}
				}

				if($user_to){

					/* On vérifie que les 2 personnes sont en relation */
					$relation = $user_model->isRelation(array('from'=>$user->user_id, 'to'=>$user_to->user_id));
					if($relation){

						$user_to_infos = $user_to->cast();

						$chat_model = new Chat_model();
						$messages = $chat_model->getUserMessages(array('user_from'=>$user->user_id, 'user_to'=>$user_to->user_id));

						$conversation_infos = array('messages'=>$messages, 'user_from'=>$user_infos, 'user_to'=>$user_to_infos);

						$f3->set('conversation', $conversation_infos);
					}else{
						$this->tpl=array('sync'=>'404.html');
					}
				}else{
					$this->tpl=array('sync'=>'404.html');
				}

				/* Récupération de toutes les relations */
				$relations = $user_model->getAllRelationsByUserId(array('user_id' => $f3->get('SESSION.user.user_id'), 'state'=>1));
				$f3->set('relations', $relations);

				/* Récupération de toutes les notifications */
				$notifications = $user_model->getAllNotificationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
				$notifications_list=array();
				if(is_array($notifications)){
					foreach($notifications as $n){
						$notifications_list[$n['notification_type']]=$n['notifications'];
					}
				}
				$f3->set('notifications', $notifications_list);

				/* Récupération de toutes les notifications de chat */
				$notifications_chat = $user_model->getAllNotificationsByUserIdAndType(array('id' => $f3->get('SESSION.user.user_id'), 'type' => 'message'));
				$notifications_chat_list=array();
				foreach($notifications_chat as $n){
					$notifications_chat_list[$n['notification_from']]=$n['notifications'];
				}
				$f3->set('notifications_chat', $notifications_chat_list);

			}else{
				$f3->reroute('/');
			}
		}

	}

?>