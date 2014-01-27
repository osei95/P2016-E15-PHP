<?php
	
	class Input_model{

		function _construct(){

		}

		function newInput($f3, $params){
			$input = $this->getInputByName($f3, array('input_shortname' => $params['input_name']));
			if($input!=null){
				$user_has_input = new DB\SQL\Mapper($f3->get('dB'), 'user_has_input');
				$user_has_input->user_id = $params['user_id'];
				$user_has_input->input_id = $input->input_id;
				$user_has_input->user_has_input_id = $params['input_key'];
				$user_has_input->save();
			}
		}

		function getInputByName($f3, $params){
			$mapper = new DB\SQL\Mapper($f3->get('dB'),'input');
			$input = $mapper->load(array('input_shortname=?', $params['input_shortname']));
			return (!$input?null:$input);
		}
	}

?>