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
			}
		}
		
	}

?>