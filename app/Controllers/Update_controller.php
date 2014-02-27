<?php 
	class Update_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'update.html');
		}

		function update($f3){
			/*$username = ($f3->exists('PARAMS.username')?$f3->get('PARAMS.username'):$f3->get('SESSION.user.user_username'));
			$user_model = new User_model();
			$user = $user_model->getUserByUsername(array('username' => $username));
			print_r($user);*/
			echo 'test';
		}

?>