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

	/* Source : http://www.weirdog.com/blog/php/supprimer-les-accents-des-caracteres-accentues.html */
	function remove_accents($str, $charset='utf-8'){
	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    
	    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
	    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
	    
	    return $str;
	}

	function testLevel($valueDistance){

		$tableResult = array();
		$level = 1;
		$bearingBefore = $valueDistance;
		$bearing = 5;

		if($valueDistance>0){

			while($valueDistance >= $bearing){
				$bearingBefore = $bearing;
				$bearing = $bearing * 2;
				$level++;
			}
			/* Variable contenant le niveau du user */
			$value = ($valueDistance * 100)/$bearing; 
			/* Nombre de km restant */
			$leftKm = $bearing-$valueDistance; 
		}else{
			$value=0;
			$level=0;
			$leftKm=$bearing;
		}
		$tableResult['value'] = $value;
		$tableResult['level'] = $level;
		$tableResult['leftKm'] = $leftKm;

		return $tableResult; 
	}
?>