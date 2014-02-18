<?php
	
	class News_model extends Model{

		private $mapper;

		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('news_infos');
		} 

		function createNews($params){
			$mapper = $this->getMapper('news');
			$mapper->news_from = $params['from'];
			$mapper->news_to = $params['to'];
			$mapper->news_type = $params['type'];
			$mapper->news_content = $params['content'];
			$mapper->news_date = $params['date'];
			$mapper->save();
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

 		function createSupport($params){
 			$mapper=$this->getMapper('support');
 			$mapper->user_id = $params['user_id'];
			$mapper->news_id = $params['news_id'];
			$mapper->save();
 		}

 		function removeSupport($params){
 			$mapper=$this->getMapper('support');
 			$support=$mapper->load(array('news_id=? AND user_id=?', $params['news_id'], $params['user_id']));	
			if($support)	$support->erase();
 		}

 		function getAllSupportsByUserId($params){
 			$mapper=$this->getMapper('support');
 			return $mapper->find(array('user_id=?', $params['user_id']));	
 		}

 		function getSupportByUserId($params){
 			$mapper=$this->getMapper('support');
 			return $mapper->load(array('news_id=? AND user_id=?', $params['news_id'], $params['user_id']));	
 		}
	}

?>