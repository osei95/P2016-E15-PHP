<?php
	
	class News_model extends Model{

		private $mapper;

		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('news');
		} 

		function createNews($params){
			$this->mapper->news_from = $params['from'];
			$this->mapper->news_to = $params['to'];
			$this->mapper->news_type = $params['type'];
			$this->mapper->news_content = $params['content'];
			$this->mapper->news_date = $params['date'];
			$this->mapper->save();
		}

		function getAllNewsFromUserId($params){
			return $this->mapper->find(array('news_from=?', $params['user_id']), array('order'=>'news_date DESC'));
 		}

 		function getAllRelationsNews($params){
			return $this->mapper->find(array('news_to=?', $params['user_id']), array('order'=>'news_date DESC'));
 		}

 		function getNewsFromUserIdByType($params){
			return $this->mapper->find(array('news_from=? AND news_type=?', $params['user_id'], $params['type']), array('order'=>'news_date DESC'));
 		}

 		function getNewsToUserIdByType($params){
			return $this->mapper->find(array('news_to=? AND news_type=?', $params['user_id'], $params['type']), array('order'=>'news_date DESC'));
 		}

 		function support($params){
 			$mapper=$this->getMapper('support');
 			$mapper->user_id = $params['user_id'];
			$mapper->news_id = $params['news_id'];
			$mapper->save();
 		}
	}

?>