<?php

	class App_controller{

		function _construct(){

		}

		function home($f3){
			if($f3->exists('SESSION.user')){
				$this->import_datas($f3);
				//$f3->set('user', $f3->get('SESSION.user'));
				//echo View::instance()->render('dashboard.html');
			}else{
				echo View::instance()->render('home.html');
			}
		}

		function import_datas($f3){
			$input_model = new Input_model();
			$user = $f3->get('SESSION.user');
			$inputs = $input_model->getInputByUserId($f3, array('user_id' => $user['user_id']));
			if($inputs!=null){
				foreach($inputs as $input){
					$activity_controller = new Activity_controller();
					$activity_controller->import_activity($f3, array('user_id' => $user['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_has_input_id']));
				}
			}
		}
		
	}

?>