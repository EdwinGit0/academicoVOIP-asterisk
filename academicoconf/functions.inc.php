<?php

function academicoconf_get_config($engine) {
	$modulename = 'academicoconf';
	
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

function academicoconf_academicoconf($c) {
	global $ext;
	global $core_conf;

	$id = "app-academicoconf"; // The context to be included
	$id2 = "function-academicoconf";

	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal

	$ext->add($id, $c, '', new ext_goto('1','s',$id2));

	$ext->add($id2, s, '', new ext_answer(''));
    $ext->add($id2, s, 'start', new ext_read('CONFERENCENUM','enter-conf-call-number','11','40'));
	$ext->add($id2, s, '', new ext_playback('one-moment-please'));
    $ext->add($id2, s, '', new ext_agi('academico.php,5,${CONFERENCENUM}'));
	$ext->add($id2, s, '', new ext_gotoif('$["${conference}"="FALSE"]','invalid'));
    $ext->add($id2, s, '', new ext_confbridge('${conference}','my_bridge,my_user','my_menu'));
	$ext->add($id2, s, 'invalid', new ext_goto('s,start'));
}

?>