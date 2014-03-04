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
			return $this->mapper->find(array('user_from_id=? AND news_date<=?', $params['user_id'], $params['news_date']), array('order'=>'news_date DESC'));
 		}

 		function getAllFollowingsNews($params){
 			return $this->dB->exec("SELECT DISTINCT * FROM news_infos INNER JOIN following ON news_infos.user_from_id=following.following_to WHERE following.following_from=".intval($params['user_id'])." ".(isset($params['news_date'])?"AND news_date<=".$params['news_date']:"")." GROUP BY news_infos.news_id ORDER BY news_date DESC ".(isset($params['offset']) && isset($params['limit'])?"LIMIT ".$params['offset'].", ".$params['limit']:''));
 		}

 		function getAllRelationsNews($params){
 			return $this->dB->exec("SELECT DISTINCT * FROM news_infos INNER JOIN `relationship` ON news_infos.user_from_id = CASE WHEN  `relationship`.`request_from`=".intval($params['user_id'])." THEN `relationship`.`request_to` ELSE `relationship`.`request_from`  END WHERE (request_from=".intval($params['user_id'])." OR request_to=".intval($params['user_id']).") AND request_state=1 ".(isset($params['news_date'])?"AND news_date<=".$params['news_date']:"")." GROUP BY news_infos.news_id ORDER BY news_date DESC ".(isset($params['offset']) && isset($params['limit'])?"LIMIT ".$params['offset'].", ".$params['limit']:''));
 		}

 		function getAllNews($params){
 			return $this->dB->exec(" SELECT DISTINCT * FROM news_infos WHERE news_infos.user_from_id IN (SELECT following_to FROM following WHERE following.following_from=".intval($params['user_id']).") OR news_infos.user_from_id IN (SELECT CASE WHEN  `relationship`.`request_from`=".intval($params['user_id'])." THEN `relationship`.`request_to` ELSE `relationship`.`request_from` END user_id FROM relationship WHERE ((request_from=".intval($params['user_id'])." OR request_to=".intval($params['user_id']).") AND request_state=1)) OR user_from_id=".intval($params['user_id'])." ".(isset($params['news_date'])?"AND news_date<=".$params['news_date']:"")." GROUP BY news_infos.news_id ORDER BY news_date DESC ".(isset($params['offset']) && isset($params['limit'])?"LIMIT ".$params['offset'].", ".$params['limit']:''));
 		}

 		function getNewsFromUserIdByType($params){
			return $this->mapper->find(array('user_from_id=? AND news_type=?', $params['user_id'], $params['type']), array('order'=>'news_date DESC'));
 		}

 		function getNewsToUserIdByType($params){
			return $this->mapper->find(array('user_to_id=? AND news_type=?', $params['user_id'], $params['type']), array('order'=>'news_date DESC'));
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