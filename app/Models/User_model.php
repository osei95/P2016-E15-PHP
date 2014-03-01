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

		/* Goals */

		function getAllGoalsToByUserId($params){
			$mapper=$this->getMapper('goal_infos');
			return $mapper->find(array('user_to_id=?', $params['id']));
		}

		/* Relations */

		function getAllRelationsByUserId($params){
			 return $this->dB->exec("SELECT `user`.`user_id`, `user`.`user_username`, `user`.`user_firstname`, `user`.`user_lastname` FROM `following` `following_from` INNER JOIN `user` ON `following_from`.`following_to` = `user`.`user_id` WHERE EXISTS( SELECT `following_to`.`following_from`, `following_to`.`following_to` FROM `following` `following_to` WHERE `following_from`.`following_from` = `following_to`.`following_to` AND `following_to`.`following_from` = `following_from`.`following_to`) AND `following_from`.`following_from`=".intval($params['id']));
		}

		/* Notifications */

		function getAllNotificationsByUserId($params){
			 return $this->dB->exec("SELECT `notification`.`notification_type`, COUNT(`notification`.`notification_id`) `notifications` FROM `notification` WHERE user_id=".intval($params['id'])." GROUP BY notification_type");
		}

		function getAllNotificationsByUserIdAndType($params){
			 return $this->dB->exec("SELECT `notification`.`notification_from`, COUNT(`notification`.`notification_id`) `notifications` FROM `notification` WHERE user_id=".intval($params['id'])." AND `notification`.`notification_type`='".$params['type']."' GROUP BY notification_from");
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