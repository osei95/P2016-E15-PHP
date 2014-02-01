<?php
	function valid($value, $bans=array()){
		$bool = true;
		if(!isset($value) || empty($value)){
			$bool = false;
		}else{
			foreach($bans as $val){
				if($value==$val){
					$bool = false;
					break;
				}
			}
		}
		return $bool;
	}
?>