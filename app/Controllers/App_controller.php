<?php

	class App_controller{

		function _construct(){

		}

		function home(){
			echo View::instance()->render('home.html');
		}
		
	}

?>