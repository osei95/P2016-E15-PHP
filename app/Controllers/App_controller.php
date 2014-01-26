<?php

	class App_controller{

		function _construct(){

		}

		function home(){
			
		}

		function chat($f3){
			$f3->set('from', ($f3->exists('PARAMS.name')?2:1));
			$f3->set('to', ($f3->exists('PARAMS.name')?1:2));
			echo View::instance()->render('chat.html');
		}
		
	}

?>