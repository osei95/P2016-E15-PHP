<?php

	class Auth_controller{

		function __construct(){}

		function jawbone_auth($f3){
			$jawbone_controller = new Jawbone_controller();
			$jawbone_controller->auth($f3);
		}

		function fitbit_auth($f3){
			$fitbit_controller = new Fitbit_controller();
			$fitbit_controller->auth($f3);
		}

		function moves_auth($f3){
			$moves_controller = new Moves_controller();
			$moves_controller->auth($f3);
		}

		function runkeeper_auth($f3){
			$runkeeper_controller = new Runkeeper_controller();
			$runkeeper_controller->auth($f3);
		}

		function register($f3){
			$infos = array();
			if($f3->exists('POST.username') && $f3->exists('POST.firstname') && $f3->exists('POST.lastname') && $f3->exists('POST.email') && $f3->exists('POST.gender') && $f3->exists('POST.description')){
				$user_model = new User_model();
				$errors = array();
				$infos = $f3->get('POST');
				if($user_model->getUserByUsername($f3, array('username' => $infos['username']))!=null){
					$errors['username'] = 'Ce pseudonyme est déjà utilisé par un autre membre.';
				}
				if(strlen($infos['firstname'])<2){
					$errors['firstname'] = 'Veuillez vérifier la saisie de votre prénom.';
				}
				if(strlen($infos['lastname'])<2){
					$errors['lastname'] = 'Veuillez vérifier la saisie de votre nom.';
				}
				if(!filter_var($infos['email'], FILTER_VALIDATE_EMAIL)){
					$errors['email'] = 'Veuillez vérifier la saisie de votre adresse mail.';
				}elseif($user_model->getUserByEmail($f3, array('email' => $infos['email']))!=null){
					$errors['email'] = 'Cette adresse mail est déjà utilisée par un autre membre.';
				}
				if($infos['gender']!=0 && $infos['gender']!=1){
					var_dump($infos['gender']);
					$errors['lastname'] = 'Veuillez indiquer votre sexe.';
				}

				if(count($errors)==0){
					$user = $user_model->newUser($f3, $infos);
					$registration_infos = $f3->get('SESSION.registration');
					$input_model = new Input_model();
					$input_model->newInput($f3, array('user_id' => $user->user_id, 'input_key' => $registration_infos['input_id'], 'input_name' => $registration_infos['input_name']));
					$f3->clear('SESSION.registration');
					$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $registration_infos['access_token'], 'access_secret_token' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:'')));
					$f3->reroute('/');
				}else{
					$f3->set('user_infos', $infos);
					$f3->set('errors', $errors);
					echo View::instance()->render('registration.html');
				}
			}
		}
		
	}

?>