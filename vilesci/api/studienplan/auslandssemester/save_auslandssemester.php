<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/auslandssemester.class.php');
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

$auslandssemester = mapDataToAuslandssemester($data);

$studienordnung = new studienordnungAddonStgv();
$studienordnung->getStudienordnungFromStudienplan($auslandssemester->studienplan_id);

if($studienordnung->status_kurzbz != "development" && !($berechtigung->isBerechtigt("stgv/changeStplAdmin")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status zu ändern.", "detail"=>"stgv/changeStplAdmin");
    returnAJAX(FALSE, $error);
}

if($auslandssemester->save())
{
    returnAJAX(true, array($auslandssemester->auslandssemester_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Auslandssemester.", "detail"=>$auslandssemester->errormsg);
    returnAJAX(false, $error);
}

function mapDataToAuslandssemester($data)
{
    $t = new auslandssemester();
    if($data->auslandssemester_id === "")
	$t->new = true;
    else
    {
	$t->new = false;
	$t->load($data->auslandssemester_id);
    }
    
    $t->studienplan_id = $data->studienplan_id;
    $t->erlaeuterungen = $data->erlaeuterungen;
    $t->data = $data->data;
    $t->insertvon = get_uid();
    return $t;
}

?>
