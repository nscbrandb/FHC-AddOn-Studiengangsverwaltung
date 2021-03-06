<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
/**
 * Klasse Studienjahr
 * @create 10-01-2007
 */
//require_once('../../../inlcude/basis_db.class.php');
require_once (dirname(__FILE__).'/../../../include/basis_db.class.php');

class studienjahrAddonStgv extends basis_db
{
	public $new;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $studienjahr_id;//  integer
	public $studienplan_id;	//  integer
	public $studienjahr_kurzbz;
	public $bezeichnung; //string
	public $data;	//jsonb
	public $insertamum;		//  timestamp
	public $insertvon;		//  string
	public $updateamum;		//  timestamp
	public $updatevon;		//  string

	/**
	 * Konstruktor
	 * @param $studienjahr_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($studienjahr_id=null)
	{
		parent::__construct();

		if(!is_null($studienjahr_id))
			$this->load($studienjahr_id);
	}

	/**
	 * Laedt die Tätigkeitsfelder mit der ID $studienjahr_id
	 * @param  $studienjahr_id ID der zu ladenden Tätigkeitsfelder
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($studienjahr_id)
	{
		if(!is_numeric($studienjahr_id))
		{
			$this->errormsg = 'studienjahr_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM addon.tbl_stgv_studienjahr WHERE studienjahr_id=".$this->db_add_param($studienjahr_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studienjahr_id = $row->studienjahr_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->studienjahr_kurzbz = $row->studienjahr_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->data = json_decode($row->data);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Zugangsvoraussetzung existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zugangsvoraussetzung';
			return false;
		}
	}

	/**
	 * Liefert alle Tätigkeitsfelder
	 */
	public function getAll($studienplan_id=null)
	{
		$qry = "SELECT * FROM addon.tbl_stgv_studienjahr ";
		if($studienplan_id!=null)
			$qry.=" WHERE studienplan_id=".$this->db_add_param($studienplan_id);
		$qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studienjahrAddonStgv();

				$obj->studienjahr_id = $row->studienjahr_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->studienjahr_kurzbz = $row->studienjahr_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->data = json_decode($row->data);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zugangsvoraussetzung.';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->studienplan_id))
		{
			$this->errormsg='studienplan_id enthaelt ungueltige Zeichen';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO addon.tbl_stgv_studienjahr (studienplan_id, studienjahr_kurzbz, bezeichnung, data, insertamum, insertvon) VALUES('.
			     $this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->studienjahr_kurzbz, FHC_STRING).', '.
			     $this->db_add_param($this->bezeichnung, FHC_STRING).', '.
			     $this->db_add_param($this->data).', now(),'.
			     $this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry='UPDATE addon.tbl_stgv_studienjahr SET '.
				'studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
				'studienjahr_kurzbz='.$this->db_add_param($this->studienjahr_kurzbz).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'data='.$this->db_add_param($this->data).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE studienjahr_id='.$this->db_add_param($this->studienjahr_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('addon.tbl_stgv_studienjahr_studienjahr_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->studienjahr_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	public function delete($studienjahr_id)
	{
	    $qry = "DELETE from addon.tbl_stgv_studienjahr WHERE studienjahr_id=".$this->db_add_param($studienjahr_id);
	    
	    if(!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Löschen der Daten';
		return false;
	    }
	    
	    return true;
	}
}
