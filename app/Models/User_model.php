<?php
	
	class User_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user_infos');
		}

		function getUserById($params){
			return $this->mapper->load(array('user_id=?', $params['id']));
		}

		function getUserByInputId($params){
			return $this->mapper->load(array('user_input_id=? AND input_shortname=?', $params['input_id'], $params['input_name']));
		}

		function getUserByUsername($params){
			return $this->mapper->load(array('user_username=?', $params['username']));
		}

		function getUserByEmail($params){
			return $this->mapper->load(array('user_email=?', $params['email']));
		}

		function createUser($params){
			$mapper=$this->getMapper('user');
			$key=uniqid();
			$mapper->user_username = $params['username'];
			$mapper->user_password = $params['password'];
			$mapper->user_email = $params['email'];
			$mapper->user_gender = $params['gender'];
			$mapper->user_description = $params['description'];
			$mapper->user_firstname = $params['firstname'];
			$mapper->user_lastname = $params['lastname'];
			$mapper->user_city = $params['city'];
			$mapper->user_birthday = $params['birthday'];
			$mapper->user_sport = $params['sport'];
			$mapper->user_appearance = $params['appearance'];
			$mapper->user_temperament = $params['temperament'];
			$mapper->user_key = $key;
			$mapper->save();
			return $mapper;
		}

		/* Followers */

		function follow($params){
			$mapper=$this->getMapper('following');
			$mapper->following_from = $params['from'];
			$mapper->following_to = $params['to'];
			$mapper->save();
		}

		function unfollow($params){
			$mapper=$this->getMapper('following');
			$follow=$mapper->load(array('following_from=? AND following_to=?', $params['from'], $params['to']));
			if($follow)	$follow->erase();
		}

		function isFollow($params){
			$mapper=$this->getMapper('following');
			return $mapper->load(array('following_from=? AND following_to=?', $params['from'], $params['to']));
		}

		function getAllFollowersByUserId($params){
			return $this->dB->exec("SELECT DISTINCT user_id, user_firstname, user_lastname, user_username, user_gender, user_city_name, user_age FROM user_infos INNER JOIN following ON user_infos.user_id=following.following_from WHERE following.following_to=".$params['user_id']);
		}

		/* Goals */

		function getAllGoalsToByUserId($params){
			$mapper=$this->getMapper('goal_infos');
			return $mapper->find(array('user_to_id=?', $params['id']));
		}

		function getAllGoalsFromByUserId($params){
			$mapper=$this->getMapper('goal_infos');
			return $mapper->find(array('user_from_id=? AND goal_deadline>?', $params['id'], $params['deadline']));
		}

		function getAllGoalsByUserId($params){
			return $this->dB->exec("SELECT COUNT(*) AS goal, (SELECT COUNT(*) FROM goal WHERE goal_to=".$params['user_id']." AND goal_deadline<=".$params['datePresent']." AND goal_achievement >= 100) AS goalFail FROM goal WHERE goal_to =".$params['user_id']." AND goal_deadline<=".$params['datePresent']);
		}

		function addGoalUser($params){
			$mapper=$this->getMapper('goal');
			$mapper->goal_from = $params['from'];
			$mapper->goal_to = $params['to'];
			$mapper->goal_unit = $params['unit'];
			$mapper->goal_value = $params['value'];
			$mapper->goal_date = $params['date'];
			$mapper->goal_deadline = $params['deadline'];
			$mapper->save();
		}

		function updateGoal($params){
			$mapper=$this->getMapper('goal');
			$goal = $mapper->load(array('goal_from=? AND goal_to=?', $params['from'], $params['to']));
			if($goal){
				if(isset($params['unit']))			$goal->goal_unit	 = $params['unit'];
				if(isset($params['value']))			$goal->goal_value = $params['value'];
				if(isset($params['achievement']))	$goal->goal_achievement = $params['achievement'];
				if(isset($params['date']))			$goal->goal_date = $params['date'];
				if(isset($params['deadline']))		$goal->goal_deadline = $params['deadline'];
				if(isset($params['accepted']))		$goal->goal_accepted = $params['accepted'];
				$goal->save();
				return true;
			}else{
				return false;
			}
		}

		function getGoalByUsersId($params){
			$mapper=$this->getMapper('goal_infos');
			return $mapper->load(array('user_to_id=? AND user_from_id=?', $params['to'], $params['from']));
		}

		/* Relations */

		function getAllRelationsByUserId($params){
			return $this->dB->exec("SELECT DISTINCT `user`.`user_id`, `user`.`user_username`, `user`.`user_firstname`, `user`.`user_lastname`, `user`.`user_gender`, `user`.`user_city_name`, `user`.`user_age`, `relationship`.`request_state`, `relationship`.`request_time`, `relationship`.`request_from`, CASE WHEN  `relationship`.`request_from`=".intval($params['user_id'])." THEN 'me' ELSE 'friend' END as `from` FROM `user_infos` `user` INNER JOIN `relationship` ON `user`.`user_id` = CASE WHEN  `relationship`.`request_from`=".intval($params['user_id'])." THEN `relationship`.`request_to` ELSE `relationship`.`request_from`  END WHERE (request_from=".intval($params['user_id'])." OR request_to=".intval($params['user_id']).")".(isset($params['state'])?" AND request_state=".intval($params['state']):"")." ORDER BY request_time DESC");
		}

		function getAllRequestsByUserId($params){
			return $this->dB->exec("SELECT `user`.`user_id`, `user`.`user_username`, `user`.`user_firstname`, `user`.`user_lastname`, `user`.`user_gender`, `relationship`.`request_state`, `relationship`.`request_time` FROM `user_infos` `user` INNER JOIN `relationship` ON `user`.`user_id` = `relationship`.`request_from` WHERE request_to=".intval($params['user_id'])." AND request_state=".intval($params['state'])." ORDER BY request_time DESC");
		}

		function addRelation($params){
			$mapper=$this->getMapper('relationship');
			$mapper->request_from = $params['from'];
			$mapper->request_to = $params['to'];
			$mapper->request_state	 = $params['state'];
			$mapper->request_time = $params['time'];
			$mapper->save();
		}

		function updateRelation($params){
			$mapper=$this->getMapper('relationship');
			$relation = $mapper->load(array('request_from=? AND request_to=?', $params['from'], $params['to']));
			if($relation){
				if(isset($params['state']))	$relation->request_state	 = $params['state'];
				if(isset($params['time']))	$relation->request_time = $params['time'];
				$relation->save();
				return true;
			}else{
				return false;
			}
		}

		function isRelation($params){
			$mapper=$this->getMapper('relationship');
			return $mapper->load(array('((request_from=? AND request_to=?) OR (request_from=? AND request_to=?)) AND request_state=1', $params['from'], $params['to'], $params['to'], $params['from']));
		}

		function isInvitation($params){
			$mapper=$this->getMapper('relationship');
			return $mapper->load(array('request_from=? AND request_to=? AND request_state=?', $params['from'], $params['to'], $params['state']));
		}

		/* Notifications */

		function getAllNotificationsByUserId($params){
			 return $this->dB->exec("SELECT `notification`.`notification_type`, COUNT(`notification`.`notification_id`) `notifications` FROM `notification` WHERE user_id=".intval($params['id'])." GROUP BY notification_type");
		}

		function getAllNotificationsByUserIdAndType($params){
			 return $this->dB->exec("SELECT `notification`.`notification_from`, COUNT(`notification`.`notification_id`) `notifications` FROM `notification` WHERE user_id=".intval($params['id'])." AND `notification`.`notification_type`='".$params['type']."' GROUP BY notification_from");
		}

		function removeNotificationsByUserId($params){
			 return $this->dB->exec("DELETE FROM `notification`WHERE user_id=".intval($params['id'])." AND `notification`.`notification_type`='".$params['type']."'");
		}

		function addNotification($params){
			$mapper=$this->getMapper('notification');
			$mapper->user_id = $params['user_id'];
			$mapper->notification_type = $params['type'];
			$mapper->notification_content = $params['content'];
			$mapper->notification_from = $params['from'];
			$mapper->save();
		}

		/* Search */
		function searchUsers($params){
			$values = array();
			if(isset($params['type']) && $params['type']=='count'){
				$query = 'SELECT DISTINCT COUNT(user_infos.user_id) as count';
			}else{
				$query = 'SELECT DISTINCT user_infos.*, get_distance_gps_points(city_user.city_lat, city_user.city_lng, city_search.city_lat, city_search.city_lng) distance';
			}
			$query.= ' FROM user_infos';
			$query.= ' LEFT JOIN cities_list city_user ON user_infos.user_city=city_user.city_id';
			$query.= ' LEFT JOIN cities_list city_search ON city_search.city_slug=:city';	$values[':city']=$params['city_slug'];
			$query.= ' WHERE user_gender=:gender';				$values[':gender']=$params['gender'];
			$query.= ' AND user_age>=:age_min';					$values[':age_min']=$params['age_min'];
			$query.= ' AND user_age<=:age_max';					$values[':age_max']=$params['age_max'];
			if(isset($params['appearance'])):	$query.= ' AND appearance_name=:appearance';		$values[':appearance']=$params['appearance'];	endif;
			if(isset($params['temperament'])):	$query.= ' AND temperament_name=:temperament';		$values[':temperament']=$params['temperament'];	endif;
			$query.= ' AND body_weight>=:weight_min';			$values[':weight_min']=$params['weight_min'];
			$query.= ' AND body_weight<=:weight_max';			$values[':weight_max']=$params['weight_max'];
			$query.= ' AND body_height>=:height_min';			$values[':height_min']=$params['height_min'];
			$query.= ' AND body_height<=:height_max';			$values[':height_max']=$params['height_max'];
			for($cpt=0; $cpt<count($params['sports']); $cpt++){
				if($cpt==0)	$query.=' AND';
				else 		$query.=' OR';
				$query.=' sport_name=:sport_'.$cpt;				$values[':sport_'.$cpt]=$params['sports'][$cpt];
			}
			if(!empty($params['city_slug'])){
				$query.= ' AND get_distance_gps_points(city_user.city_lat, city_user.city_lng, city_search.city_lat, city_search.city_lng)>=:rayon_min';				$values[':rayon_min']=$params['rayon_min'];
				$query.= ' AND get_distance_gps_points(city_user.city_lat, city_user.city_lng, city_search.city_lat, city_search.city_lng)<=:rayon_max';				$values[':rayon_max']=$params['rayon_max'];
			}
			if(!isset($params['type']) || !$params['type']=='count'){
				$query.= ' LIMIT '.$params['offset'].', '.$params['row_count'];
			}
			return $this->dB->exec($query, $values);	
		}
	}
?>