<?php 
	class Update_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'update.html');
		}

		function update($f3){
			$infos = array();
			$errors = array();

			if($f3->exists('SESSION.user.user_username') && $f3->exists('POST.firstname') && $f3->exists('POST.lastname')) {
				$user_model = new User_model();
				$infos = $f3->get('POST');
				if(strlen($infos['firstname'])<2){
					$errors['firstname'] = 'Veuillez vérifier la saisie de votre prénom.';
				}
				if(strlen($infos['lastname'])<2){
					$errors['lastname'] = 'Veuillez vérifier la saisie de votre nom.';
				}
			}
			else{
				$errors['data'] = 'Une erreur s\'est produite lors de l\'envoi du formulaire. Veuillez recommencer.';
			}
			if(count($errors != 0)){
				$f3->set('errors_register', $errors);
			}
		}

		function home($f3){
			$f3->set('user',$f3->get('PARAMS.username'));
		}
	}
?>