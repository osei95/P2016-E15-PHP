<?php 
	class Profil_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'profil.html');
		}

		function profil($f3){
			/* Récupération des informations de l'utilisateur */
			$user_model = new User_model();
			$user = $user_model->getUserByUsername(array('username' => $f3->get('PARAMS.username')));
			if($user){
				$user_infos = $user->cast();
				$user_infos['body_weight'] = $user_infos['body_weight'] / 1000;
				$user_infos['body_height'] = $user_infos['body_height'] / 1000;
				$f3->set('user', $user_infos);

				/* Récupération des news propres à l'utilisateur */
				$news_model = new News_model();
				$news = $news_model->getAllNewsFromUserId(array('user_id' => $user->user_id));
				$f3->set('news', $news);
			}else{
				$this->tpl=array('sync'=>'404.html');
			}
		}
	}
?>