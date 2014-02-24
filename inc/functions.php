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

	function testLevel($valueDistance){
		$tableResult = array();
		$level = 0;
		$bearing = 5;

		while($valueDistance >= $bearing){
			$bearingBefore = $bearing;
			$bearing = $bearing * 2;
			$level++;
		}
		/* Récupère la distance restante pour faire la comparaison dans le cercle */
		$distanceLeft = $valueDistance - $bearingBefore;
		/* Permet de faire la différence de valeur entre les deux niveaux pour calculer et tracer le cercle */
		$radiusCircle = $bearing - $bearingBefore;
		/* Variable contenant le niveau du user */
		$value = ($distanceRestante/$radiusCircle) * 100; 
		/* Nombre de km restant */
		$leftKm = $bearing - $valueDistance; 
		$tableResult[0] = $value;
		$tableResult[1] = $level;
		$tableResult[2] = $leftKm;

		return $tableResult; 
	}
?>