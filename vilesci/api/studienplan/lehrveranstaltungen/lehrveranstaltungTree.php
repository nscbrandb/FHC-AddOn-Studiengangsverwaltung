<?php
/* Copyright (C) 2016 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../functions.php');

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");
$parent_id = filter_input(INPUT_POST, "id");

if(is_null($studienplan_id))
{
    returnAJAX(false, "Variable studienplan_id nicht gesetzt");
}
elseif(($studienplan_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$data = array();

$studienplan = new studienplanAddonStgv();
$studienplan->loadStudienplan($studienplan_id);

$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id);

if($parent_id == null)
{
    for($i=0; $i <= $studienplan->regelstudiendauer; $i++)
    {
        $node = new stdClass();
        $node->id = $i.'_sem';
        $node->bezeichnung = $i.'. Semester';
        $node->type = "sem";
        $node->sem = $i;
        $node->iconCls = "tree-folder";
        $node->state = "open";
        $node->ects = 0;
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if(($lv->stpllv_semester == $i) && ($lv->studienplan_lehrveranstaltung_id_parent==""))
            {
                $node->state = "closed";
                $node->ects += $lv->ects;
            }
        }
        array_push($data, $node);
    }
}
else
{
    $idParams = explode("_",$parent_id);
    if(isset($idParams[1]) && ($idParams[1] == "sem"))
    {
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if(($lv->stpllv_semester == $idParams[0]) && ($lv->studienplan_lehrveranstaltung_id_parent==""))
            {
                $lv->parentId = $parent_id;
                $lv->id = $lv->studienplan_lehrveranstaltung_id;
                $lv->type = $lv->lehrtyp_kurzbz;

                $studienplan = new StudienplanAddonStgv();
                $studienplan->getStudienplanLehrveranstaltung($lv->lehrveranstaltung_id);
                $lv->zugewieseneStudienplaene='';
                foreach($studienplan->result as $row_stpl)
                    $lv->zugewieseneStudienplaene.=$row_stpl->bezeichnung.' ';
                $lv_obj = new lehrveranstaltung();
                $lv->gesperrt = $lv_obj->isGesperrt($lv->lehrveranstaltung_id);

                if($lehrveranstaltung->hasChildren($lv->studienplan_lehrveranstaltung_id))
                {
                    $lv->state = "closed";
                }
                switch($lv->lehrtyp_kurzbz)
                {
                    case "lv":
                        $lv->iconCls = "icon-lv";
                        break;
                    case "modul":
                        $lv->iconCls = "icon-module";
                        break;
                    case "lf":
                        $lv->iconCls = "icon-lv";
                        break;
                    default:
                        $lv->iconCls = "icon-lv";
                        break;
                }
                array_push($data, $lv);
            }
        }
        usort($data, "cmp_type");
        usort($data, "cmp_name");
    }
    else
    {
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if($lv->studienplan_lehrveranstaltung_id_parent == $idParams[0])
            {
                $lv->parentId = $lv->studienplan_lehrveranstaltung_id_parent;
                $lv->id = $lv->studienplan_lehrveranstaltung_id;
                $lv->type = $lv->lehrtyp_kurzbz;

                $studienplan = new StudienplanAddonStgv();
                $studienplan->getStudienplanLehrveranstaltung($lv->lehrveranstaltung_id);
                $lv->zugewieseneStudienplaene='';
                foreach($studienplan->result as $row_stpl)
                    $lv->zugewieseneStudienplaene.=$row_stpl->bezeichnung.' ';
                $lv_obj = new lehrveranstaltung();
                $lv->gesperrt = $lv_obj->isGesperrt($lv->lehrveranstaltung_id);

                if($lehrveranstaltung->hasChildren($lv->studienplan_lehrveranstaltung_id))
                {
                    $lv->state = "closed";
                }
                switch($lv->lehrtyp_kurzbz)
                {
                    case "lv":
                        $lv->iconCls = "icon-lv";
                        break;
                    case "modul":
                        $lv->iconCls = "icon-module";
                        break;
                    case "lf":
                        $lv->iconCls = "icon-lv";
                        break;
                    default:
                        $lv->iconCls = "icon-lv";
                        break;
                }
                array_push($data, $lv);
            }
        }
        usort($data, "cmp_type");
        usort($data, "cmp_name");
    }
}
returnAJAX(true, $data);

function cmp_name($a, $b)
{
    if ($a->bezeichnung == $b->bezeichnung)
    {
        return 0;
    }
    return ($a->bezeichnung < $b->bezeichnung) ? -1 : 1;
}

function cmp_type($a, $b)
{
    if ($a->type == $b->type)
    {
        return 0;
    }
    return ($a->type < $b->type) ? -1 : 1;
}

?>