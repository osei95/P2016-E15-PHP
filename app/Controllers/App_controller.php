<?php

	class App_controller extends Controller{

		protected $tpl;
      	protected $model;
      	private $oauth_controller;

		public function __construct(){
		    parent::__construct();
		    $this->tpl=array('sync'=>'home.html');
		}

		function home($f3){
			if($f3->exists('SESSION.user')){
				$this->tpl=array('sync'=>'dashboard.html');

				/* Récupération des informations de l'utilisateur */
				$user_model = new User_model();
				$user = $user_model->getUserById(array('id' => $f3->get('SESSION.user.user_id')));
				if($user){

					$user_infos = $user->cast();
					$f3->set('user', $user_infos);

					/* Récupération des objectis */
					$goals = $user_model->getAllGoalsToByUserId(array('id' => $f3->get('SESSION.user.user_id')));
					$goals_infos = array();
					foreach($goals as $key => $g){
						$goals_infos[$key] = $g->cast();
					}
					$f3->set('goals', $goals_infos);

					/* Récupération des news */
					$news_model = new News_model();
					$news = $news_model->getAllRelationsNews(array('user_id' => $f3->get('SESSION.user.user_id')));
					$f3->set('news', $news);

					/* Récupération des supports propres à l'utilisateur */
					$supports = $news_model->getAllSupportsByUserId(array('user_id' => $f3->get('SESSION.user.user_id')));
					$support_list=array('news' => array());
					if(is_array($supports)){
						foreach($supports as $s){
							array_push($support_list['news'], $s->news_id);
						}
					}
					$f3->set('supports', $support_list);
					
				}else{
					$this->tpl=array('sync'=>'404.html');
				}
			}
		}

		public function logout($f3){
			session_destroy();
			$f3->reroute('/');
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

		function chat($f3){
			$f3->set('from', ($f3->exists('PARAMS.name')?2:1));
			$f3->set('to', ($f3->exists('PARAMS.name')?1:2));
			echo View::instance()->render('chat.html');
		}
		
	}

?>