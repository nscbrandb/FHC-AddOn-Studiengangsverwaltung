<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/Zugangsvoraussetzung.class.php');
require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$zugangsvoraussetzung = mapDataToZugangsvoraussetzung($data);

if($zugangsvoraussetzung->save())
{
    returnAJAX(true, array($zugangsvoraussetzung->zugangsvoraussetzung_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Tätigkeitsfelder.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToZugangsvoraussetzung($data)
{
    $t = new zugangsvoraussetzung();
    if($data->zugangsvoraussetzung_id === "")
    {
	$t->new = true;
	$t->insertvon = get_uid();
    }
    else
    {
	$t->new = false;
	$t->load($data->zugangsvoraussetzung_id);
	$t->updatevon = get_uid();
    }
    
    $t->studienordnung_id = $data->studienordnung_id;
    $t->data = $data->data;
    
    return $t;
}

?>