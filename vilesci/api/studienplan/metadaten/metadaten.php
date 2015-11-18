<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/studienplan.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$sto_array = array();

$stplId = filter_input(INPUT_GET, "stplId");

if(is_null($stplId))
{
    returnAJAX(false, "Variable stplId nicht gesetzt");    
}
elseif(($stplId == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studienplan = new studienplan();
$studienplan->loadStudienplan($stplId);

//TODO Anpassen um nur nötige Felder zu holen
$data = array(
    'studienplan_id'=> $studienplan->studienplan_id,
    'version'=> $studienplan->version, 				
    'orgform_kurzbz' => $studienplan->orgform_kurzbz,				
    'bezeichnung' => $studienplan->bezeichnung,
    'regelstudiendauer' => $studienplan->regelstudiendauer,
    'sprache' => $studienplan->sprache,
    'aktiv' => $studienplan->aktiv,
    'semesterwochen' => $studienplan->semesterwochen,
    'testtool_sprachwahl' => $studienplan->testtool_sprachwahl,
    'updateamum' => $studienplan->updateamum,
    'updatevon' => $studienplan->updatevon,
    'insertamum' => $studienplan->insertamum,
    'insertvon' => $studienplan->insertvon
);


returnAJAX(true, $data);
?>