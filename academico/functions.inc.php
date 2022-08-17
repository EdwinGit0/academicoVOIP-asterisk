<?php

function academico_get_config($engine) {
	$modulename = 'academico';
	
	// This generates the dialplan
	global $ext;  
	switch($engine) {
		case "asterisk":
			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);
						
						if ($fc != '')
							$fname($fc);
					} else {
						$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
						var_dump($item);
					}	
				}
			}
		break;
	}
}

function academico_academico($c) {
	global $ext;
	global $core_conf;

	$id = "app-academico"; // The context to be included
	$id2 = "function-academico";

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_goto('1','s',$id2));

	$ext->add($id2, s, '', new ext_answer(''));
    $ext->add($id2, s, '', new ext_set('phone','${CALLERID(num)}'));
	$ext->add($id2, s, 'start', new ext_festival('Para iniciar sesion. Ingrese su clave'));
    $ext->add($id2, s, '', new ext_read('password','','20','10'));
	$ext->add($id2, s, '', new ext_playback('one-moment-please'));
    $ext->add($id2, s, '', new ext_agi('academico.php,1,${phone},${password}'));
    $ext->add($id2, s, '', new ext_festival('${message}'));
    $ext->add($id2, s, 'menu', new ext_festival('Para una consulta de sus calificaciones presione 1. para verificar su informacion presione 2. para verificar conferencias programadas presione 3. para volver a repetir presione 4. para salir presione 0'));
    $ext->add($id2, s, '', new ext_waitexten('10'));

	$ext->add($id2, 1, '', new ext_gotoif('$["${studentmss}"="student"]','gestion'));
	$ext->add($id2, 1, '', new ext_festival('${studentmss}'));
	$ext->add($id2, 1, '', new ext_read('ci','','15','20'));
	$ext->add($id2, 1, 'gestion', new ext_festival('Ingrese la gestion. Ejemplo gestion 2022'));
	$ext->add($id2, 1, '', new ext_read('gestion','','4','10'));
	$ext->add($id2, 1, '', new ext_festival('Ingrese el periodo. Ejemplo periodo 1'));
	$ext->add($id2, 1, '', new ext_read('periodo','','1','10'));
	$ext->add($id2, 1, '', new ext_playback('one-moment-please'));
	$ext->add($id2, 1, '', new ext_agi('academico.php,2,${gestion},${periodo},${token},${ci}'));
	$ext->add($id2, 1, '', new ext_goto('s,menu'));

	$ext->add($id2, 2, '', new ext_playback('one-moment-please'));
	$ext->add($id2, 2, '', new ext_agi('academico.php,3,${token}'));
	$ext->add($id2, 2, '', new ext_goto('s,menu'));

	$ext->add($id2, 3, '', new ext_playback('one-moment-please'));
	$ext->add($id2, 3, '', new ext_agi('academico.php,4,${token}'));
	$ext->add($id2, 3, '', new ext_goto('s,menu'));

	$ext->add($id2, 4, '', new ext_goto('s,menu'));

	$ext->add($id2, 0, 'colgar', new ext_playback('thank-you-for-calling&goodbye'));
	$ext->add($id2, 0, '', new ext_hangup(''));

	$ext->add($id2, i, '', new ext_festival('opcion invalida'));
	$ext->add($id2, i, '', new ext_goto('s,menu'));

	$ext->add($id2, t, '', new ext_festival('opcion invalida'));
	$ext->add($id2, t, '', new ext_goto('s,menu'));
}

?>