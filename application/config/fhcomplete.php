<?php  
	if (! defined('BASEPATH'))
		exit('No direct script access allowed');

$config['fhc_version'] = '3.2';

$config['fhc_acl'] = array
(
	'bis.tbl_archiv' => 'basis/archiv',
	'bis.tbl_ausbildung' => 'basis/ausbildung',
	'bis.tbl_berufstaetigkeit' => 'basis/berufstaetigkeit',
	'bis.tbl_beschaeftigungsausmass' => 'basis/beschaeftigungsausmass',
	'bis.tbl_besqual' => 'basis/besqual',
	'bis.tbl_bisfunktion' => 'basis/bisfunktion',
	'bis.tbl_bisio' => 'basis/bisio',
	'bis.tbl_bisorgform' => 'basis/bisorgform',
	'bis.tbl_bisverwendung' => 'basis/bisverwendung',
	'bis.tbl_bundesland' => 'basis/bundesland',
	'bis.tbl_entwicklungsteam' => 'basis/entwicklungsteam',
	'bis.tbl_gemeinde' => 'basis/gemeinde',
	'bis.tbl_hauptberuf' => 'basis/hauptberuf',
	'bis.tbl_lgartcode' => 'basis/lgartcode',
	'bis.tbl_mobilitaetsprogramm' => 'basis/mobilitaetsprogramm',
	'bis.tbl_nation' => 'basis/nation',
	'bis.tbl_orgform' => 'basis/orgform',
	'bis.tbl_verwendung' => 'basis/verwendung',
	'bis.tbl_zgv' => 'basis/zgv',
	'bis.tbl_zgvdoktor' => 'basis/zgvdoktor',
	'bis.tbl_zgvgruppe' => 'basis/zgvgruppe',
	'bis.tbl_zgvmaster' => 'basis/zgvmaster',
	'bis.tbl_zweck' => 'basis/zweck',
	'campus.tbl_abgabe' => 'basis/abgabe',
	'campus.tbl_anwesenheit' => 'basis/anwesenheit',
	'campus.tbl_beispiel' => 'basis/beispiel',
	'campus.tbl_content' => 'basis/content',
	'campus.tbl_contentchild' => 'basis/contentchild',
	'campus.tbl_contentgruppe' => 'basis/contentgruppe',
	'campus.tbl_contentlog' => 'basis/contentlog',
	'campus.tbl_contentsprache' => 'basis/contentsprache',
	'campus.tbl_coodle' => 'basis/coodle',
	'campus.tbl_dms' => 'basis/dms',
	'campus.tbl_erreichbarkeit' => 'basis/erreichbarkeit',
	'campus.tbl_feedback' => 'basis/feedback',
	'campus.tbl_freebusy' => 'basis/freebusy',
	'campus.tbl_freebusytyp' => 'basis/freebusytyp',
	'campus.tbl_infoscreen' => 'basis/infoscreen',
	'campus.tbl_legesamtnote' => 'basis/legesamtnote',
	'campus.tbl_lvgesamtnote' => 'basis/lvgesamtnote',
	'campus.tbl_lvinfo' => 'basis/lvinfo',
	'campus.tbl_news' => 'basis/news',
	'campus.tbl_notenschluessel' => 'basis/notenschluessel',
	'campus.tbl_notenschluesseluebung' => 'basis/notenschluesseluebung',
	'campus.tbl_paabgabe' => 'basis/paabgabe',
	'campus.tbl_paabgabetyp' => 'basis/paabgabetyp',
	'campus.tbl_pruefung' => 'basis/pruefung',
	'campus.tbl_pruefungsanmeldung' => 'basis/pruefungsanmeldung',
	'campus.tbl_pruefungsfenster' => 'basis/pruefungsfenster',
	'campus.tbl_pruefungsstatus' => 'basis/pruefungsstatus',
	'campus.tbl_pruefungstermin' => 'basis/pruefungstermin',
	'campus.tbl_reservierung' => 'basis/reservierung',
	'campus.tbl_resturlaub' => 'basis/resturlaub',
	'campus.tbl_studentbeispiel' => 'basis/studentbeispiel',
	'campus.tbl_studentuebung' => 'basis/studentuebung',
	'campus.tbl_template' => 'basis/template',
	'campus.tbl_uebung' => 'basis/uebung',
	'campus.tbl_veranstaltung' => 'basis/veranstaltung',
	'campus.tbl_veranstaltungskategorie' => 'basis/veranstaltungskategorie',
	'campus.tbl_zeitaufzeichnung' => 'basis/zeitaufzeichnung',
	'campus.tbl_zeitsperre' => 'basis/zeitsperre',
	'campus.tbl_zeitsperretyp' => 'basis/zeitsperretyp',
	'campus.tbl_zeitwunsch' => 'basis/zeitwunsch',
	'fue.tbl_aktivitaet' => 'basis/aktivitaet',
	'fue.tbl_aufwandstyp' => 'basis/aufwandstyp',
	'fue.tbl_projekt' => 'basis/projekt',
	'fue.tbl_projekt_ressource' => 'basis/projekt_ressource',
	'fue.tbl_projektphase' => 'basis/projektphase',
	'fue.tbl_projekttask' => 'basis/projekttask',
	'fue.tbl_ressource' => 'basis/ressource',
	'fue.tbl_scrumsprint' => 'basis/scrumsprint',
	'fue.tbl_scrumteam' => 'basis/scrumteam',
	'lehre.tbl_abschlussbeurteilung' => 'basis/abschlussbeurteilung',
	'lehre.tbl_abschlusspruefung' => 'basis/abschlusspruefung',
	'lehre.tbl_akadgrad' => 'basis/akadgrad',
	'lehre.tbl_anrechnung' => 'basis/anrechnung',
	'lehre.tbl_betreuerart' => 'basis/betreuerart',
	'lehre.tbl_ferien' => 'basis/ferien',
	'lehre.tbl_lehreinheit' => 'basis/lehreinheit',
	'lehre.tbl_lehreinheitgruppe' => 'basis/lehreinheitgruppe',
	'lehre.tbl_lehreinheitmitarbeiter' => 'basis/lehreinheitmitarbeiter',
	'lehre.tbl_lehrfach' => 'basis/lehrfach',
	'lehre.tbl_lehrform' => 'basis/lehrform',
	'lehre.tbl_lehrfunktion' => 'basis/lehrfunktion',
	'lehre.tbl_lehrmittel' => 'basis/lehrmittel',
	'lehre.tbl_lehrtyp' => 'basis/lehrtyp',
	'lehre.tbl_lehrveranstaltung' => 'basis/lehrveranstaltung',
	'lehre.tbl_lvangebot' => 'basis/lvangebot',
	'lehre.tbl_lvregel' => 'basis/lvregel',
	'lehre.tbl_lvregeltyp' => 'basis/lvregeltyp',
	'lehre.tbl_moodle' => 'basis/moodle',
	'lehre.tbl_note' => 'basis/note',
	'lehre.tbl_notenschluessel' => 'basis/notenschluessel',
	'lehre.tbl_notenschluesselaufteilung' => 'basis/notenschluesselaufteilung',
	'lehre.tbl_notenschluesselzuordnung' => 'basis/notenschluesselzuordnung',
	'lehre.tbl_projektarbeit' => 'basis/projektarbeit',
	'lehre.tbl_projektbetreuer' => 'basis/projektbetreuer',
	'lehre.tbl_projekttyp' => 'basis/projekttyp',
	'lehre.tbl_pruefung' => 'basis/pruefung',
	'lehre.tbl_pruefungstyp' => 'basis/pruefungstyp',
	'lehre.tbl_studienordnung' => 'basis/studienordnung',
	'lehre.tbl_studienordnungstatus' => 'basis/studienordnungstatus',
	'lehre.tbl_studienplan' => 'basis/studienplan',
	'lehre.tbl_studienplatz' => 'basis/studienplatz',
	'lehre.tbl_stunde' => 'basis/stunde',
	'lehre.tbl_stundenplan' => 'basis/stundenplan',
	'lehre.tbl_stundenplandev' => 'basis/stundenplandev',
	'lehre.tbl_vertrag' => 'basis/vertrag',
	'lehre.tbl_vertragsstatus' => 'basis/vertragsstatus',
	'lehre.tbl_vertragstyp' => 'basis/vertragstyp',
	'lehre.tbl_zeitfenster' => 'basis/zeitfenster',
	'lehre.tbl_zeugnis' => 'basis/zeugnis',
	'lehre.tbl_zeugnisnote' => 'basis/zeugnisnote',
	'public.tbl_adresse' => 'basis/adresse',
	'public.tbl_akte' => 'basis/akte',
	'public.tbl_ampel' => 'basis/ampel',
	'public.tbl_aufmerksamdurch' => 'basis/aufmerksamdurch',
	'public.tbl_aufnahmeschluessel' => 'basis/aufnahmeschluessel',
	'public.tbl_aufnahmetermin' => 'basis/aufnahmetermin',
	'public.tbl_aufnahmetermintyp' => 'basis/aufnahmetermintyp',
	'public.tbl_bankverbindung' => 'basis/bankverbindung',
	'public.tbl_benutzer' => 'basis/benutzer',
	'public.tbl_benutzerfunktion' => 'basis/benutzerfunktion',
	'public.tbl_benutzergruppe' => 'basis/benutzergruppe',
	'public.tbl_bewerbungstermine' => 'basis/bewerbungstermine',
	'public.tbl_buchungstyp' => 'basis/buchungstyp',
	'public.tbl_dokument' => 'basis/dokument',
	'public.tbl_dokumentprestudent' => 'basis/dokumentprestudent',
	'public.tbl_dokumentstudiengang' => 'basis/dokumentstudiengang',
	'public.tbl_erhalter' => 'basis/erhalter',
	'public.tbl_fachbereich' => 'basis/fachbereich',
	'public.tbl_filter' => 'basis/filter',
	'public.tbl_firma' => 'basis/firma',
	'public.tbl_firmatag' => 'basis/firmatag',
	'public.tbl_firmentyp' => 'basis/firmentyp',
	'public.tbl_fotostatus' => 'basis/fotostatus',
	'public.tbl_funktion' => 'basis/funktion',
	'public.tbl_geschaeftsjahr' => 'basis/geschaeftsjahr',
	'public.tbl_gruppe' => 'basis/gruppe',
	'public.tbl_kontakt' => 'basis/kontakt',
	'public.tbl_kontaktmedium' => 'basis/kontaktmedium',
	'public.tbl_kontakttyp' => 'basis/kontakttyp',
	'public.tbl_konto' => 'basis/konto',
	'public.tbl_lehrverband' => 'basis/lehrverband',
	'public.tbl_log' => 'basis/log',
	'public.tbl_mitarbeiter' => 'basis/mitarbeiter',
	'public.tbl_msg_message' => 'basis/msg_message',
	'public.tbl_msg_thread' => 'basis/msg_thread',
	'public.tbl_notiz' => 'basis/notiz',
	'public.tbl_notizzuordnung' => 'basis/notizzuordnung',
	'public.tbl_organisationseinheit' => 'basis/organisationseinheit',
	'public.tbl_organisationseinheittyp' => 'basis/organisationseinheittyp',
	'public.tbl_ort' => 'basis/ort',
	'public.tbl_ortraumtyp' => 'basis/ortraumtyp',
	'public.tbl_person' => 'basis/person',
	'public.tbl_personfunktionstandort' => 'basis/personfunktionstandort',
	'public.tbl_preincoming' => 'basis/preincoming',
	'public.tbl_preinteressent' => 'basis/preinteressent',
	'public.tbl_preinteressentstudiengang' => 'basis/preinteressentstudiengang',
	'public.tbl_preoutgoing' => 'basis/preoutgoing',
	'public.tbl_prestudent' => 'basis/prestudent',
	'public.tbl_prestudentstatus' => 'basis/prestudentstatus',
	'public.tbl_raumtyp' => 'basis/raumtyp',
	'public.tbl_reihungstest' => 'basis/reihungstest',
	'public.tbl_semesterwochen' => 'basis/semesterwochen',
	'public.tbl_service' => 'basis/service',
	'public.tbl_sprache' => 'basis/sprache',
	'public.tbl_standort' => 'basis/standort',
	'public.tbl_statistik' => 'basis/statistik',
	'public.tbl_status' => 'basis/status',
	'public.tbl_student' => 'basis/student',
	'public.tbl_studentlehrverband' => 'basis/studentlehrverband',
	'public.tbl_studiengang' => 'basis/studiengang',
	'public.tbl_studiengangstyp' => 'basis/studiengangstyp',
	'public.tbl_studienjahr' => 'basis/studienjahr',
	'public.tbl_studiensemester' => 'basis/studiensemester',
	'public.tbl_tag' => 'basis/tag',
	'public.tbl_variable' => 'basis/variable',
	'public.tbl_vorlage' => 'basis/vorlage',
	'public.tbl_vorlagestudiengang' => 'basis/vorlagestudiengang',
	'system.tbl_appdaten' => 'basis/appdaten',
	'system.tbl_benutzerrolle' => 'basis/benutzerrolle',
	'system.tbl_berechtigung' => 'basis/berechtigung',
	'system.tbl_cronjob' => 'basis/cronjob',
	'system.tbl_rolle' => 'basis/rolle',
	'system.tbl_rolleberechtigung' => 'basis/rolleberechtigung',
	'system.tbl_server' => 'basis/server',
	'system.tbl_webservicelog' => 'basis/webservicelog',
	'system.tbl_webservicerecht' => 'basis/webservicerecht',
	'system.tbl_webservicetyp' => 'basis/webservicetyp',
	'testtool.tbl_ablauf' => 'basis/ablauf',
	'testtool.tbl_antwort' => 'basis/antwort',
	'testtool.tbl_frage' => 'basis/frage',
	'testtool.tbl_gebiet' => 'basis/gebiet',
	'testtool.tbl_kategorie' => 'basis/kategorie',
	'testtool.tbl_kriterien' => 'basis/kriterien',
	'testtool.tbl_pruefling' => 'basis/pruefling',
	'testtool.tbl_vorschlag' => 'basis/vorschlag',
	'wawi.tbl_aufteilung' => 'basis/aufteilung',
	'wawi.tbl_bestelldetail' => 'basis/bestelldetail',
	'wawi.tbl_bestelldetailtag' => 'basis/bestelldetailtag',
	'wawi.tbl_bestellstatus' => 'basis/bestellstatus',
	'wawi.tbl_bestellung' => 'basis/bestellung',
	'wawi.tbl_bestellungtag' => 'basis/bestellungtag',
	'wawi.tbl_betriebsmittel' => 'basis/betriebsmittel',
	'wawi.tbl_betriebsmittelperson' => 'basis/betriebsmittelperson',
	'wawi.tbl_betriebsmittelstatus' => 'basis/betriebsmittelstatus',
	'wawi.tbl_betriebsmitteltyp' => 'basis/betriebsmitteltyp',
	'wawi.tbl_buchung' => 'basis/buchung',
	'wawi.tbl_buchungstyp' => 'basis/buchungstyp',
	'wawi.tbl_budget' => 'basis/budget',
	'wawi.tbl_konto' => 'basis/konto',
	'wawi.tbl_kostenstelle' => 'basis/kostenstelle',
	'wawi.tbl_rechnung' => 'basis/rechnung',
	'wawi.tbl_rechnungsbetrag' => 'basis/rechnungsbetrag',
	'wawi.tbl_rechnungstyp' => 'basis/rechnungstyp',
	'wawi.tbl_zahlungstyp' => 'basis/zahlungstyp',
	
	'lehre.vw_studienplan' => 'basis/studienplan',
	
	'public.tbl_sprache' => 'admin',
	'public.tbl_msg_thread' => 'admin',
	'public.tbl_msg_message' => 'admin'
);