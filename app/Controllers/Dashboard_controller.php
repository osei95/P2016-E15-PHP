<?php

	class Dashboard_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'dashboard.html');
		}

		public function show($f3){
			$news_model = new News_model();
			$f3->set('news', $news_model->getAllNewsToUserId($f3->get('SESSION.user.user_id')));
		}
		
	}

?>