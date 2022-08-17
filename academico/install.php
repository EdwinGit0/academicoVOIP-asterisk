<?php
global $astman;
global $amp_conf;

// Register FeatureCode - Activate
$fcc = new featurecode('academico', 'academico');
$fcc->setDescription('Servicio academico de calificaciones');
$fcc->setDefault('*9923');
$fcc->update();
unset($fcc);

?>