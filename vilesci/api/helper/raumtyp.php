<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/raumtyp.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$raumtyp = new raumtyp();
if($raumtyp->getAll(true, true))
{
    $data =  $raumtyp->result;
}
else
{
    returnAJAX(false, $raumtyp->errormsg);
}

returnAJAX(true, $data);
?>