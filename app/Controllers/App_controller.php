<?php

	class App_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'home.html');
		    $this->oauth_controller = new Oauth_controller();
		}

		function home($f3){
			if($f3->exists('SESSION.user')){
				$this->importDatas($f3);
				$f3->set('user', $f3->get('SESSION.user'));
				$this->tpl=array('sync'=>'dashboard.html');
			}
		}

		function importDatas($f3){
			$input_model = new Input_model();
			$user = $f3->get('SESSION.user');
			$input = $input_model->getInputByUserId(array('user_id' => $user['user_id']));
			if($input!=null){
				$activity_controller = new Activity_controller();
				$activity_controller->importActivity($f3, array('user_id' => $user['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'access_token' => $f3->get('SESSION.user.access_token'), 'access_token_secret' => $f3->get('SESSION.user.access_secret_token')));
			}
		}
		
	}

?>