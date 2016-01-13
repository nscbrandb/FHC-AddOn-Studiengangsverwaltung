<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/StudienordnungAddonStgv.class.php');

require_once('../../functions.php');
//TODO Berechtigung

$dms_id = filter_input(INPUT_GET, "dms_id");
$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");

if (is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");
} 
elseif (is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");
} 
elseif (($dms_id == false) || ($studienordnung_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$studienordnung = new studienordnungAddonStgv();
if($studienordnung->deleteDokument($studienordnung_id, $dms_id))
{
    returnAJAX(true,"Dokument erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Dokuments.", "detail"=>$dms->errormsg);
    returnAJAX(false, $error);
}
