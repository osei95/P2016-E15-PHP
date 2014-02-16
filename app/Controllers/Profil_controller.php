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
			$table = $user->cast($user); //Convertit l'objet MySQL en tableau PHP
			$table['body_weight'] = $table['body_weight'] / 1000;
			$table['body_height'] = $table['body_height'] / 1000;
			$f3->set('user', $table);
		}
	}
?>