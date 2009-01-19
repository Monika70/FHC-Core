<?php
//@version $Id$
/*
	Dieses Programm listet nach einem Suchbegriff bestehender Benutzer auf. 
	Fuer jede UserID wird geprueft ob dieser bereits einen Moodle ID besitzt.
	Bestehende Moodle IDs werden angezeigt, fuer alle anderen wird die moeglichkeit
	der neuanlage geboten.
*/

// ---------------- Standart Include Dateien einbinden
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/globals.inc.php');
// ---------------- Moodle Daten Classe
	require_once('../../include/moodle_user.class.php');

// ***********************************************************************************************	
// Variable Initialisieren
// ***********************************************************************************************
// AusgabeStream
	$content='';

// ***********************************************************************************************
// POST oder GET Parameter einlesen 
// ***********************************************************************************************

// $cUID UserID fuer Moodelaccount anlage
	$cUID = (isset($_REQUEST['uid'])?trim($_REQUEST['uid']):'');
// @$cMdl_user_id Moodleaccount zum loeschen
	$cMdl_user_id = (isset($_REQUEST['mdl_user_id'])?trim($_REQUEST['mdl_user_id']):'');
// @cSearchstr Suchtext in Tabelle Benutzer 
	$cSearchstr = (isset($_REQUEST['searchstr'])?trim($_REQUEST['searchstr']):'');
// @cCharset Zeichensatz - Ajax mit UTF-8
	$cCharset= (isset($_REQUEST['client_encode'])?trim($_REQUEST['client_encode']):'iso-8859-15');
// ***********************************************************************************************
//	Datenbankverbindungen zu Moodle und Vilesci und Classen
// ***********************************************************************************************
	// DB Connect
	$conn=@pg_pconnect(CONN_STRING) or die('<div style="text-align:center;"><br />Datenbank zurzeit NICHT Online.<br />Bitte etwas Geduld.<br />Danke</div>');// 	Datenbankverbindung
	$conn_moodle = pg_pconnect(CONN_STRING_MOODLE) or die('<div style="text-align:center;"><br />MOODLE Datenbank zurzeit NICHT Online.<br />Bitte etwas Geduld.<br />Danke</div>');
	// Classen Instanzen
	$objMoodle = new moodle_user($conn, $conn_moodle);	

// ***********************************************************************************************
//	Verarbeitung einer Moodle-Account Anlageaktion
// ***********************************************************************************************
	if ($cUID!='') // Bearbeiten User UID Anfrage
	{
		// Check ob User nicht bereits angelegt ist
		if (!$bStatus=$objMoodle->loaduser($cUID))
		{
			$objMoodle->errormsg='';
		//  User ist noch nicht in Moodle angelegt => Neuanlage
			if (!$bStatus=$objMoodle->createUser($cUID))
				$content.=$objMoodle->errormsg;
		}	
	}	
// ***********************************************************************************************
//	HTML Suchfeld (Teil 1)
// ***********************************************************************************************
	$content.='
		<form name="search" method="GET" action="'.$_SERVER["PHP_SELF"].'" target="_self">
	  		Bitte Suchbegriff eingeben: 
	  		<input type="text" name="searchstr" size="30" value="'.htmlentities($cSearchstr).'">
	  		<input type="submit" value=" suchen ">
	  	</form>	
		<hr>';
// ***********************************************************************************************
//	HTML Listenanzeige (Teil 2)
// ***********************************************************************************************
	if($cSearchstr!='' && $cSearchstr!='?'  && $cSearchstr!='*')
	{
		// SQL Select-String
		$qry = "SELECT distinct tbl_person.person_id,tbl_person.nachname,tbl_person.vorname,tbl_person.aktiv,tbl_benutzer.uid
			FROM public.tbl_person 
			LEFT JOIN public.tbl_benutzer ON tbl_benutzer.person_id=tbl_person.person_id 
			WHERE (
			tbl_person.nachname ~* '".addslashes($cSearchstr)."' OR 
			tbl_person.vorname ~* '".addslashes($cSearchstr)."' OR
			tbl_benutzer.alias ~* '".addslashes($cSearchstr)."' OR
			tbl_person.nachname || ' ' || tbl_person.vorname = '".addslashes($cSearchstr)."' OR 
			tbl_person.vorname || ' ' || tbl_person.nachname = '".addslashes($cSearchstr)."' OR 
			tbl_benutzer.uid ~* '".addslashes($cSearchstr)."'
			) 
			and tbl_benutzer.uid >'' 
			and tbl_benutzer.uid IS NOT NULL 
			ORDER BY nachname, vorname;";

			if($result = @pg_query($conn, $qry))
			{	
				// Header Top mit Anzahl der gelisteten Kurse		
				$content.= '<a name="top">'. @pg_num_rows($result).' Person(en) gefunden</a>';	
				
				$content.='<table  style="border: 1px outset #F7F7F7;">';

				// Header Teil Information der Funktion	
					$content.='<tr class="liste" align="center">';
						$content.='<td colspan="6"><b>Benutzer</b></td>';
					$content.='</tr>';
					
				// Headerinformation der Tabellenfelder 
					$content.='<tr class="liste" align="center">';
						$content.='<th>&nbsp;Nachname&nbsp;</th>';
						$content.='<th>&nbsp;Vorname&nbsp;</th>';
						$content.='<th>&nbsp;UserID&nbsp;</th>';
						$content.='<th>&nbsp;Status&nbsp;</th>';
						$content.='<th>&nbsp;MoodleAccount&nbsp;</th>';
#						$content.='<th>&nbsp;Bearbeitung&nbsp;</th>';
					$content.='</tr>';
				
					// Alle gefundenen User in einer Schleife anzeigen.
					$iTmpCounter=0;
					while($row = @pg_fetch_object($result))
					{
						// ZeilenCSS (gerade/ungerade) zur besseren Ansicht
						$iTmpCounter++;
						if ($iTmpCounter%2)
							$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FEFFEC" ';
						else
							$showCSS=' style="text-align: left;border: 1px outset #F7F7F7;padding: 1px 5px 1px 5px; background:#FCFCFC"  ';			

						// Listenzeile
						$content.= '<tr '.$showCSS.'>';
							$content.= '<td '.$showCSS.'>'.$row->nachname.'</td>';
							$content.= '<td '.$showCSS.'>'.$row->vorname.'</td>';
							$content.= '<td '.$showCSS.'>'.$row->uid.'</td>';
							$content.= '<td '.$showCSS.'>'.(strtoupper($row->aktiv)=='T' || strtoupper($row->aktiv)=='TRUE' ?'aktiv':'deaktiviert').'</td>';
							$arrMoodleUser=array();	
							$objMoodle->errormsg='';
							$objMoodle->mdl_user_id='';
							if (!empty($row->uid))
							{
								if (!$boolReadMoodle=$objMoodle->loaduser($row->uid))
									$objMoodle->mdl_user_id='';
							}
							// Es gibt noch keinen Moodle User - Anlage ermoeglichen
							if (!isset($objMoodle->mdl_user_id) || empty($objMoodle->mdl_user_id))
							{
								$content.= '<td style="vertical-align:bottom;cursor: pointer;" onclick="document.work'.$iTmpCounter.'.submit();">';
								$content.='<form style="display: inline;border:0px;" name="work'.$iTmpCounter.'" method="GET" target="_self" action="'.$_SERVER["PHP_SELF"].'">';
								  	$content.= '<input style="display:none" type="text" name="uid" value="'.htmlentities($row->uid).'" />';
								  	$content.= '<input style="display:none" type="text" name="searchstr" value="'.htmlentities($cSearchstr).'" />';
									$content.= '<img height="12" src="../../skin/images/table_row_insert.png" border="0" title="MoodleUser anlegen" alt="table_row_insert.png" />';					
									$content.= '<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$iTmpCounter.'" />';
									$content.= 'anlegen';					
								$content.='</form>';
								$content.= '</td>';
							}
							else // Anzeige bestehende Moodle User ID
							{
								$content.= '<td '.$showCSS.'>'.((isset($objMoodle->mdl_user_id) && !empty($objMoodle->mdl_user_id))?$objMoodle->mdl_user_id:'').'</td>';
							}
							// Tastatureingabe ermoeglichen
						$content.= '</tr>';
					} // Ende Schleife der gefundenen User
					$content.= '</table>';
					$content.= '<a href="#top">zum Anfang</a>';
			}	// 	Ende SQL Result abfrage
	} // Ende ob Suchanfrage gestellt (Submit) wurde
	$content='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
		<title>Moodle - Accountverwaltung</title>
		<base target="main">
		<meta http-equiv="Content-Type" content="text/html; charset='.$cCharset.'">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="background_main">
	<h2>Moodle - Accountverwaltung</h2>
	<!-- MoodleAccount Content Start -->
		'.$content.'
	<!-- MoodleAccount Content Ende -->
	</body>
		</html>';
	exit($content);
#-------------------------------------------------------------------------------------------	
# Testfunktion zur Anzeige einer übergebenen Variable oder Array, Default ist GLOBALS
function Test($arr=constLeer,$lfd=0,$displayShow=true,$onlyRoot=false )
{

    $tmpArrayString='';
    if (!is_array($arr) && !is_object($arr)) return $arr;
    if (is_array($arr) && count($arr)<1 && $displayShow) return '';
    if (is_array($arr) && count($arr)<1 && $displayShow) return "<br /><b>function Test (???)</b><br />";
   
    $lfdnr=$lfd + 1; 
    $tmpAnzeigeStufe='';
    for ($i=1;$i<$lfdnr;$i++) $tmpAnzeigeStufe.="=";
    $tmpAnzeigeStufe.="=>";
	while (list( $tmp_key, $tmp_value ) = each($arr) ) 
	{
       	if (!$onlyRoot && (is_array($tmp_value) || is_object($tmp_value)) && count($tmp_value) >0) 
       	{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key</b>".Test($tmp_value,$lfdnr);
       	} else if ( (is_array($tmp_value) || is_object($tmp_value)) ) 
       	{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe <b>$tmp_key -- 0 Records</b>";
		} else if ($tmp_value!='') 
		{
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :== ".$tmp_value;
		} else {
                   $tmpArrayString.="<br />$tmpAnzeigeStufe $tmp_key :-- (is Empty :: $tmp_value)";
		}  
    }
     if ($lfd!='') { return $tmpArrayString; }
     if (!$displayShow) { return $tmpArrayString; }
       
    $tmpArrayString.="<br />";
    $tmpArrayString="<br /><hr /><br />******* START *******<br />".$tmpArrayString."<br />******* ENDE *******<br /><hr /><br />";
	if (defined('Sprache_ISO')) 
	{
	    $tmpArrayString.="<br />Language:: ".Sprache_ISO;
	}    
    $tmpArrayString.="<br />Server:: ".$_SERVER['PHP_SELF']."<br />";
	return "$tmpArrayString";


}

?>
