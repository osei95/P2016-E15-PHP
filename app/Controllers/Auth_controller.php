<?php

	class Auth_controller extends Controller{

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'registration.html');
		}

		function jawbone_auth($f3){
			$jawbone_controller = new Jawbone_controller();
			$jawbone_controller->auth($f3);
			$this->prepare_registration($f3);
		}

		function fitbit_auth($f3){
			$fitbit_controller = new Fitbit_controller();
			$fitbit_controller->auth($f3);
			$this->prepare_registration($f3);
		}

		function moves_auth($f3){
			$moves_controller = new Moves_controller();
			$moves_controller->auth($f3);
			$this->prepare_registration($f3);
		}

		function runkeeper_auth($f3){
			$runkeeper_controller = new Runkeeper_controller();
			$runkeeper_controller->auth($f3);
			$this->prepare_registration($f3);
		}

		function auth($f3){
			$this->tpl=array('sync'=>'home.html');
			$errors = array();
			if($f3->exists('POST.username') && $f3->exists('POST.password')){
				$user_model = new User_model();
				$user = $user_model->getUserByUsername(array('username' => $f3->get('POST.username')));
				if($user && $user->user_password == hash('md5', $f3->get('POST.password'), true)){
					$input_model = new Input_model();
					$input = $input_model->getInputByUserId(array('user_id' => $user->user_id));
					$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_username' => $user->user_username, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $input['user_input_oauth'], 'access_secret_token' => (($input['user_input_oauth_secret']!='NULL')?$input['user_input_oauth_secret']:''), 'refresh_token' => (($input['user_input_oauth_refresh_token']!='NULL')?$input['user_input_oauth_refresh_token']:'')));
				}else{
					$errors['auth'] = 'Erreur d\'authentification.';
				}
			}else{
				$errors['data'] = 'Une erreur s\'est produite lors de l\'envoi du formulaire. Veuillez recommencer.';
			}
			if(count($errors)>0){
				$f3->set('errors_auth', $errors);
			}else{
				$input = $input_model->getInputByUserId(array('user_id' => $user->user_id));
				if($input){
					$input_controller = new Input_controller();
					if($f3->get($input['input_shortname'])['oauth']==2){
						$auth_response = $input_controller->renewToken($f3, array('input_name' => $input['input_shortname'], 'user_has_input_id' => $input['user_input_id'], 'refresh_token' => $f3->get('SESSION.user.refresh_token')));
						if($auth_response){
							$f3->set('SESSION.user.refresh_token', $auth_response['refresh_token']);
							$f3->set('SESSION.user.access_token', $auth_response['access_token']);
						}
					}
					$input_api_controller = $input_controller->getInputAPIController($f3, array('input_shortname' => $input['input_shortname']));
					$input_api_controller->importActivity($f3, array('user_id' => $user['user_id'], 'input_shortname' => $input['input_shortname'], 'input_id' => $input['input_id'], 'user_has_input_id' => $input['user_input_id'], 'access_token' => $f3->get('SESSION.user.access_token'), 'access_token_secret' => $f3->get('SESSION.user.access_secret_token')));
				}
				$f3->reroute('/');
			}
		}

		function register($f3){
			$infos = array();
			$errors = array();

			$infos = $f3->get('POST');

			$base_server = '/var/www/clients/client10/web64/web/web/web';

			if($f3->exists('SESSION.registration_auth.username') && $f3->exists('SESSION.registration_auth.password') && $f3->exists('POST.firstname') && $f3->exists('POST.lastname') && $f3->exists('POST.email') && $f3->exists('POST.city_slug') && $f3->exists('POST.gender') && $f3->exists('POST.description') && $f3->exists('POST.birthday_day') && $f3->exists('POST.birthday_month') && $f3->exists('POST.birthday_year') && $f3->exists('POST.appearance') && $f3->exists('POST.temperament') && $f3->exists('POST.weight') && $f3->exists('POST.height')){
				$user_model = new User_model();
				$infos['username'] = $f3->get('SESSION.registration_auth.username');
				$infos['password'] = $f3->get('SESSION.registration_auth.password');
				if($user_model->getUserByUsername(array('username' => $infos['username']))!=null){
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
				}elseif($user_model->getUserByEmail(array('email' => $infos['email']))!=null){
					$errors['email'] = 'Cette adresse mail est déjà utilisée par un autre membre.';
				}

				/* Récupération de l'id de la ville selectionnée */
				$app_model = new App_model();
				$city=$app_model->getCityBySlug(array('city_slug'=>$infos['city_slug']));
				if(!isset($city->city_id)){
					$errors['city'] = 'Veuillez vérifier la saisie de votre ville.';
				}
				if($infos['gender']!=0 && $infos['gender']!=1){
					$errors['lastname'] = 'Veuillez indiquer votre sexe.';
				}
				if(intval($infos['birthday_day'])<1 || intval($infos['birthday_day'])>31 || intval($infos['birthday_month'])<1 || intval($infos['birthday_month'])>12 || intval($infos['birthday_year'])<date('Y')-120 || intval($infos['birthday_year'])>date('Y')-16){
					$errors['birthday'] = 'Veuillez vérifier la saisie de votre date de naissance.';
				}else{

					/* Vérification de l'âge */
					$today['mois'] = date('n');
					$today['jour'] = date('j');
					$today['annee'] = date('Y');
					$age = $today['annee'] - $infos['birthday_year'];
					if ($today['mois'] <= $infos['birthday_month']) {
						if ($infos['birthday_month'] == $today['mois']) {
							if ($infos['birthday_day'] > $today['jour'])	$age--;
						}else{
							$age--;
						}
					}

					if($age<18){
						$errors['birthday'] = 'Vous devez avoir 18 ans pour vous inscrire !';
					}else{
						$infos['birthday'] = intval($infos['birthday_year']).'-'.intval($infos['birthday_month']).'-'.intval($infos['birthday_day']);
					}
				}
				if(!isset($infos['sport']) || !is_array($infos['sport']) || count($infos['sport'])<1 || intval($infos['sport'][0])<1){
					$errors['sport'] = 'Veuillez indiquer le sport que vous pratiquez.';
				}
				if(intval($infos['appearance'])<1){
					$errors['appearance'] = 'Veuillez indiquer votre corpulence.';
				}
				if(intval($infos['temperament'])<1){
					$errors['temperament'] = 'Veuillez indiquer votre temperament.';
				}
				if(intval($infos['weight'])<30 || intval($infos['weight'])>300){
					$errors['weight'] = 'Veuillez indiquer votre poids.';
				}
				if(intval($infos['height'])<130 || intval($infos['height'])>210){
					$errors['height'] = 'Veuillez indiquer votre taille.';
				}
 
				if(isset($_FILES['profil_photo']) && $_FILES['profil_photo']['error']==0){
					
					$directory = '/medias/users/tempory/';

					/* Upload de la photo de profil */
					$f3->set('UPLOADS', $base_server.$directory);
					$files = \Web::instance()->receive(function($file){
						if(!in_array($file['type'], array('image/jpeg', 'image/png', 'image/gif', 'image/bmp'))){	// Filtrage selon le type mime
							$errors['image'] = 'Le fichier selectionné n\'est pas une photo valide.';
							return false;
						}
						if($file['size']>5*1024*1024){	// Limit size to 5 MB
							$errors['image'] = 'La photo ne doit pas faire plus de 5MB.';
							return false;
						}
			 	      	return true;
			    	},true,true);
			    	$complete_path = array_keys($files)[0];
			    	$relative_path = str_replace($base_server, '', $complete_path);
			    	if($files[$complete_path]==true){

			    		/* On retaille la photo au format 200x200 */
			    		$img = new Image(''.$relative_path);
			    		$img->resize(200, 200, true);
			    		$rezized_image_str = $img->dump();
			    		$rezized_image = imagecreatefromstring($rezized_image_str);

			    		/* Suppression de la précédente image si elle existe */
			    		if($f3->exists('SESSION.registration_form.photo_profil')){
			    			@unlink($base_server.$f3->get('SESSION.registration_form.photo_profil'));
			    		}

			    		/* Enregistrement de l'image retaillée */
			    		imagejpeg($rezized_image, $complete_path);

			    		$f3->set('SESSION.registration_form', array('photo_profil' => $relative_path));
			    	}
			    }else if(!isset($_FILES['profil_photo'])){
			    	$errors['data'] = 'Merci de choisir une photo de profil.';
			    }else if(!$f3->exists('SESSION.registration_form.photo_profil')){
			    	$errors['data'] = 'Une erreur s\'est produite lors du chargement de votre photo de profil. Veuillez recommencer.';
			    }else{
			    	// Si c'est un deuxième chargement et que la photo est en session
			    	$relative_path = $f3->get('SESSION.registration_form.photo_profil');
			    }
			}elseif($f3->exists('POST.username') && $f3->exists('POST.password') && $f3->exists('POST.input')){
				$user_model = new User_model();
				$this->tpl=array('sync'=>'home.html');
				$f3->set('SESSION.registration_auth', array('username' => $f3->get('POST.username'), 'password' => $f3->get('POST.password')));
				if(strlen($infos['username'])<6){
					$errors['username'] = 'Votre pseudonyme doit contenir 4 caractères au minimum.';
				}elseif($user_model->getUserByUsername(array('username' => $infos['username']))!=null){
					$errors['username'] = 'Ce pseudonyme est déjà utilisé par un autre membre.';
				}
				if(strlen($infos['password'])<6){
					$errors['password'] = 'Votre mot de passe doit contenir 6 caractères au minimum.';
				}
				if(count($errors)==0){
					switch($f3->get('POST.input')){
						case 'JAWBONE' :
							$this->jawbone_auth($f3);
							break;
						case 'FITBIT' :
							$this->fitbit_auth($f3);
							break;
						case 'MOVES' :
							$this->moves_auth($f3);
							break;
						case 'RUNKEEPER' :
							$this->runkeeper_auth($f3);
							break;
					}
				}
			}else{
				$errors['data'] = 'Merci de compléter tous les champs du formulaire d\'inscription.';
			}
			if(count($errors)==0){
				$infos['weight']=$infos['weight']*10;	// unité
				$infos['city']=$city->city_id;
				$infos['sport'] = $infos['sport'][0];
				$infos['password'] = hash('md5', $infos['password'], true);
				$user = $user_model->createUser($infos);
				$registration_infos = $f3->get('SESSION.registration');
				$input_model = new Input_model();
				$input = $input_model->createInput(array('user_id' => $user->user_id, 'input_key' => $registration_infos['input_id'], 'input_name' => $registration_infos['input_name'], 'oauth' => $registration_infos['access_token'], 'oauth_secret' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:''), 'oauth_refresh_token' => (isset($registration_infos['refresh_token'])?$registration_infos['refresh_token']:'')));
				$f3->clear('SESSION.registration');
				$f3->set('SESSION.user', array('user_id' => $user->user_id, 'user_email' => $user->user_email, 'user_username' => $user->user_username, 'user_firstname' => $user->user_firstname, 'user_lastname' => $user->user_lastname, 'user_key' => $user->user_key, 'user_gender' => $user->user_gender, 'user_description' => $user->user_description, 'access_token' => $registration_infos['access_token'], 'access_secret_token' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:''), 'refresh_token' => (isset($registration_infos['refresh_token'])?$registration_infos['refresh_token']:'')));
				$input_controller = new Input_controller();
				$input_api_controller = $input_controller->getInputAPIController($f3, array('input_shortname' => $registration_infos['input_name']));
				$body_model = new Body_model();
				$body_model->addBodyUser(array('user_id'=>$user->user_id, 'date'=>time(), 'weight'=>$infos['weight'], 'height'=>$infos['height']));
				if($registration_infos['input_name']=='FITBIT' || $registration_infos['input_name']=='RUNKEEPER' || $registration_infos['input_name']=='JAWBONE'){
					$input_api_controller->importBody($f3, array('user_id' => $user->user_id, 'input_shortname' => $registration_infos['input_name'], 'input_id' => $input->input_id, 'user_has_input_id' => $input->user_has_input_id, 'access_token' => $registration_infos['access_token'], 'access_token_secret' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:''), 'date' => 'all'));
				}
				$input_api_controller->importActivity($f3, array('user_id' =>$user->user_id, 'input_shortname' => $registration_infos['input_name'], 'input_id' => $input->input_id, 'user_has_input_id' => $input->user_has_input_id, 'access_token' => $registration_infos['access_token'], 'access_token_secret' => (isset($registration_infos['access_secret_token'])?$registration_infos['access_secret_token']:''), 'date' => 'all'));
				mkdir($base_server.'/medias/users/'.$user->user_id);
				rename($base_server.$relative_path, $base_server.'/medias/users/'.$user->user_id.'/profil.jpg');
				$f3->reroute('/profil');
			}else{
				$f3->set('user_infos', $infos);
				$f3->set('errors_register', $errors);
			}

			$this->prepare_registration($f3);

		}

		private function prepare_registration($f3){
			$body_model = new Body_model();
			$appareances = $body_model->getAllAppareances(array());
			$f3->set('appareances', $appareances);

			$temperaments = $body_model->getAllTemperaments(array());
			$f3->set('temperaments', $temperaments);

			$sports = $body_model->getAllSports(array());
			$f3->set('sports', $sports);
		}
		
	}

?>