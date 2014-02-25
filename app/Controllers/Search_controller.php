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
			
			$notifications = $user_model->getAllNotificationsByUserId(array('id' => $f3->get('SESSION.user.user_id')));
			$notifications_list=array();
			foreach($notifications as $n){
				$notifications_list[$n['notification_type']]=$n['notifications'];
			}
			$f3->set('notifications', $notifications_list);
		}
		
	}

?>