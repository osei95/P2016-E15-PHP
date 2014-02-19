<?php

	class App_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'home.html');
		}

		function home($f3){
			if($f3->exists('SESSION.user')){
				$this->tpl=array('sync'=>'dashboard.html');

				/* Récupération des informations de l'utilisateur */
				$user_model = new User_model();
				$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
				if($user){

					$user_infos = $user->cast();
					$f3->set('user', $user_infos);
					
				}else{
					$this->tpl=array('sync'=>'404.html');
				}
			}
		}
		
	}

?>