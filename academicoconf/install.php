<?php
global $astman;
global $amp_conf;

// Register FeatureCode - Activate
$fcc = new featurecode('academicoconf', 'academicoconf');
$fcc->setDescription('Servicio academico de conferencias');
$fcc->setDefault('9000');
$fcc->update();
unset($fcc);

?>