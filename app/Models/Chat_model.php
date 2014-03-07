<?php
	
	class Chat_model extends Model{

		private $mapper;
		private $oauth_model;
  
		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('message');
		} 

		function getUserMessages($params){
			$this->dB->exec('SET lc_time_names = "fr_FR"');
			return $this->dB->exec('SELECT CASE WHEN message.message_from=:user_from THEN "me" ELSE "friend" END message_from_class, message.message_from, message.message_content, DATE_FORMAT(message.message_time,"%Hh%i") message_time, message.message_time message_datetime, DATE_FORMAT(message.message_time,"%W %d %M %Y") message_date, message_from FROM message WHERE (message.message_from=:user_from AND message.message_to=:user_to) OR (message.message_from=:user_to AND message.message_to=:user_from) ORDER BY message_datetime', array(':user_from'=>$params['user_from'], ':user_to'=>$params['user_to']));
		}

		function getLastMessage($params){
			return $this->mapper->find(array('message_to=? OR message_from=?', $params['user_id'], $params['user_id']), array('order'=>'message_time DESC', 'limit'=>1, 'offset' => 0));
		}
	}

?>