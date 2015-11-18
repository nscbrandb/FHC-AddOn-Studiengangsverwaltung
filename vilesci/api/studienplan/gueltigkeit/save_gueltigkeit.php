<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/StudienplanAddonStgv.class.php');
require_once('../../../../include/StudienordnungAddonStgv.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$data = filter_input_array(INPUT_POST, array("data" => array('flags' => FILTER_REQUIRE_ARRAY)));
$data = $data["data"];

$studienplan = new StudienplanAddonStgv();

foreach ($data as $key)
{
    if (!isZuordnungGuelitg($key["studienplan_id"], $key["studiensemester_kurzbz"]))
    {
	$error = array("message" => "Studiensemester liegt ausßerhalb der Gültigkeit der Studienordnung.");
	returnAJAX(false, $error);
    }
    if($studienplan->isSemesterZugeordnet($key["studienplan_id"], $key["studiensemester_kurzbz"], $key["ausbildungssemester"]))
    {
	$error = array("message" => "Studiensemester ist bereits zugeordnet.");
	returnAJAX(false, $error);
    }
}


if ($studienplan->saveSemesterZuordnung($data))
{
    returnAJAX(TRUE, "Zuordnung erfolgreich.");
} else
{
    $error = array("message" => "Fehler beim Speichern der Zuordnung.", "detail" => $studienplan->errormsg);
    returnAJAX(false, $error);
}

/*
 * TODO
 * prüfen ob innerhalb gültigkeit von STO
 * prüfen ob noch nicht zugeordnet
 * speichern
 * 
 */

function isZuordnungGuelitg($studienplan_id, $studiensemester_kurzbz)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->loadStudienplan($studienplan_id);
    $studienordnung = new StudienordnungAddonStgv();
    $studienordnung->loadStudienordnung($stpl->studienordnung_id);
    $studiensemester = new studiensemester();
    $studiensemester->getTimestamp($studiensemester_kurzbz);

    $semGueltigVon = $studiensemester->begin->start;

    $studiensemester = new studiensemester();
    $studiensemester->getTimestamp($studienordnung->gueltigvon);

    $stoGueltigVon = $studiensemester->begin->start;

    if ($studienordnung->gueltigbis != null)
    {
	$studiensemester = new studiensemester();
	$studiensemester->getTimestamp($studienordnung->gueltigbis);
	$stoGueltigBis = $studiensemester->ende->ende;
    } else
    {
	$stoGueltigBis = null;
    }
    if (($semGueltigVon >= $stoGueltigVon && $semGueltigVon <= $stoGueltigBis) || ($semGueltigVon >= $stoGueltigVon && $stoGueltigBis == null))
    {
	return true;
    }
    return false;
}