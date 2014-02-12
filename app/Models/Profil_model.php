<?php 
	class Profil_model extends Model {

		private $mapper;

		public function __construct(){
			parent::__construct();
			$this->mapper=$this->getMapper('user');
		}

		function getUserById($params){
			return $this->mapper->load(array('user_id=?',$params)); //load recupère 1 seule ligne sinon find
		}
		function getWeight($params){
			$mapper = $this->getMapper('user_view');
			return $mapper->load(array('user_id=?',$params));
		}
	}
?>