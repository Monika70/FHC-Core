<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/** 
 * Klasse fachbereich (FAS-Online)
 * @create 04-12-2006
 */

class fachbereich
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $fachbereich_kurzbz;	// @var string
	var $bezeichnung;		// @var string
	var $farbe;			// @var string
	var $studiengang_kz;	// @var integer
	var $aktiv;			// @var boolean
	var $ext_id;			// @var bigint
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $fachb_id ID des zu ladenden Fachbereiches
	 */
	function fachbereich($conn, $fachbereich_kurzbz=null)
	{
		$this->conn = $conn;
		if($fachbereich_kurzbz != null)
		{
			$this->load($fachbereich_kurzbz);
		}
	}
	
	/**
	 * Laedt alle verfuegbaren Fachbereiche
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_fachbereich order by fachbereich_kurzbz;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$fachb_obj = new fachbereich($this->conn);
			
			$fachb_obj->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$fachb_obj->bezeichnung = $row->bezeichnung;
			$fachb_obj->farbe = $row->farbe;
			$fachb_obj->studiengang_kz = $row->studiengang_kz;
			$fachb_obj->ext_id = $row->ext_id;
			
			$this->result[] = $fachb_obj;
		}
		return true;
	}
	
	/**
	 * Laedt einen Fachbereich
	 * @param $fachb_id ID des zu ladenden Fachbereiches
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($fachbereich_kurzbz)
	{
		if($fachbereich_kurzbz == '')
		{
			$this->errormsg = 'fachbereich_kurzbz ungueltig!';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_fachbereich WHERE fachbereich_kurzbz = '".addslashes($fachbereich_kurzbz)."';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->farbe = $row->farbe;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->ext_id = $row->ext_id;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $fachb_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($fachb_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{	
		$this->bezeichnung = str_replace("'",'�',$this->bezeichnung);
		$this->fachbereich_kurzbz = str_replace("'",'�',$this->fachbereich_kurzbz);

		
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)           
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->fachbereich_kurzbz)>16)
		{
			$this->errormsg = "Kurzbez darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->fachbereich_kurzbz";
			return false;
		}		
		$this->errormsg = '';
		return true;		
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Pruefen ob fachbereich_kurzbz gueltig ist
			if($this->fachbereich_kurzbz == '')
			{
				$this->errormsg = 'fachbereich_id ungueltig! ('.$this->fachbereich_kurzbz.'/'.$this->ext_id.')';
				return false;
			}
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO public.tbl_fachbereich (fachbereich_kurzbz, bezeichnung, farbe, aktiv, ext_id, studiengang_kz) VALUES ('.
				$this->addslashes($this->fachbereich_kurzbz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->farbe).', '.
				($this->aktiv?'true':'false').', '. 
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->studiengang_kz).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob fachbereich_kurzbz gueltig ist
			if($this->fachbereich_kurzbz == '')
			{
				$this->errormsg = 'fachbereich_kurzbz ungueltig.';
				return false;
			}
			
			$qry = 'UPDATE public.tbl_fachbereich SET '. 
				'fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'farbe='.$this->addslashes($this->farbe).', '.
				'aktiv='.($this->aktiv?'true':'false').', '.
				'ext_id='.$this->addslashes($this->ext_id).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).' '.
				'WHERE fachbereich_kurzbz = '.$this->addslashes($this->fachbereich_kurzbz).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			/*//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}*/
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}		
	}
}
?>