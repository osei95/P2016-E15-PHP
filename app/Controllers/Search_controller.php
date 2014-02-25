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
			
		}
		
	}

?>