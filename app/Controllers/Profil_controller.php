<?php 
	class Profil_controller extends Controller{

		protected $tpl;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'profil.html');
		}

		function profil($f3){

			$username = ($f3->exists('PARAMS.username')?$f3->get('PARAMS.username'):$f3->get('SESSION.user.user_username'));

			/* Récupération des informations de l'utilisateur */
			$user_model = new User_model();
			$user = $user_model->getUserByUsername(array('username' => $username));
			if($user){
				$user_infos = $user->cast();
				$user_infos['body_weight'] = $user_infos['body_weight'] / 1000;
				$user_infos['body_height'] = $user_infos['body_height'] / 1000;
				/* Fonction permettant de calculer l'age */
				$date = new DateTime($user_infos['user_birthday']);
				$now = new DateTime();
				$interval = $now->diff($date);
				$user_infos['user_birthday'] = $interval->y;
				/* Convertit la ville en minuscule et ajoute une majuscule à la première lettre */
				$user_infos['user_city'] = strtolower($table['user_city']);
				$user_infos['user_city'] = ucfirst($table['user_city']);
				$f3->set('user', $user_infos);

				/* Récupération des news propres à l'utilisateur */
				$news_model = new News_model();
				$news = $news_model->getAllNewsFromUserId(array('user_id' => $user->user_id));
				$f3->set('news', $news);

				/* Récupération des supports propres à l'utilisateur */
				$supports = $news_model->getAllSupportsByUserId(array('user_id' => $f3->get('SESSION.user.user_id')));
				$support_list=array('news' => array());
				if(is_array($supports)){
					foreach($supports as $s){
						array_push($support_list['news'], $s->news_id);
					}
				}

				$support_list['follow'] = ($user_model->isFollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $user->user_id))?true:false);
				$support_list['is_follow'] = ($user_model->isFollow(array('to' => $f3->get('SESSION.user.user_id'), 'from' => $user->user_id))?true:false);

				$f3->set('supports', $support_list);
				
			}else{
				$this->tpl=array('sync'=>'404.html');
			}
		}

		function support($f3){
			$news_model = new News_model();
			$support = $news_model->getSupportByUserId(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			if(!$support){
				$news_model->createSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			}else{
				$news_model->removeSupport(array('news_id' => $f3->get('PARAMS.id_news'), 'user_id' => $f3->get('SESSION.user.user_id')));
			}
			$f3->reroute('/');
		}

		function follow($f3){
			$user_model = new User_model();
			$follow = $user_model->isFollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
			if(!$follow){
				$user_model->follow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
			}else{
				$user_model->unfollow(array('from' => $f3->get('SESSION.user.user_id'), 'to' => $f3->get('PARAMS.id_user')));
			}
			$f3->reroute('/');
		}

		function session($f3){
			$this->tpl=array('sync'=>'session.json', 'async'=>'session.json');
		}
	}
?>