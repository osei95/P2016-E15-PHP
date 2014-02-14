<?php 
	class Profil_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'profil.html');
		}

		function profil($f3){
			$model = new User_model();
			$user = $model->getUserById($f3->get('PARAMS.username'));
			$user['body_weight'] = $user['body_weight'] / 1000;
			$user['body_height'] = $user['body_height'] / 1000;
			//echo $user['body_height'];
			$f3->set('user', $user);
		}
	}
?>