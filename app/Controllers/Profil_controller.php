<?php 
	class Profil_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'profil.html');
		}

		function profil($f3){
			$model = new Profil_model();
			$user = $model->getUserById($f3->get('PARAMS.count'));
			$f3->set('user', $user);
			$userWeight = $model->getWeight($f3->get('PARAMS.count'));
			$f3->set('userWeight',$userWeight);
		}
	}
?>