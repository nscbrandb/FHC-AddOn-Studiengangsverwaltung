<?php
require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/changeStudienplan",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$studienplan = mapDataToStudienplan($data);
$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienplan->studienordnung_id);

if($studienordnung->status_kurzbz != "development" && !($berechtigung->isBerechtigt("stgv/changeStplAdmin")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status ändern.", "detail"=>"stgv/changeStplAdmin");
    returnAJAX(FALSE, $error);
}

if($studienplan->save())
{
    returnAJAX(true, "Studienplan erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Studienplans.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudienplan($data)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->loadStudienplan($data->studienplan_id);
    $stpl->version = $data->version;
    $stpl->bezeichnung = $data->bezeichnung;
    $stpl->aktiv = parseBoolean($data->aktiv);
    $stpl->updatevon = get_uid();
    $stpl->orgform_kurzbz = $data->orgform_kurzbz;
    //$stpl->regelstudiendauer = $data->regelstudiendauer;
    //$stpl->semesterwochen = $data->semesterwochen;
    //$stpl->sprache = $data->sprache;
    $stpl->testtool_sprachwahl = parseBoolean($data->testtool_sprachwahl);
    return $stpl;
}

?>