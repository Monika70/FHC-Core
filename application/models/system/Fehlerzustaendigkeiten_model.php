<?php
class Fehlerzustaendigkeiten_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler_zustaendigkeiten';
		$this->pk = 'fehlerzustaendigkeiten_id';
	}

	/**
	 * Gets active Mitarbeiter not assigned to a Fehler.
	 * @param $fehlercode
	 * @return object
	 */
	public function getNonAssignedMitarbeiter($fehlercode)
	{
		$query = "SELECT person_id, ben.uid, vorname, nachname, titelpre, titelpost, personalnummer
					FROM public.tbl_mitarbeiter
					JOIN public.tbl_benutzer ben ON tbl_mitarbeiter.mitarbeiter_uid = ben.uid
					JOIN public.tbl_person pers USING (person_id)
					WHERE ben.aktiv
					AND NOT EXISTS (
					    SELECT 1 FROM system.tbl_fehler_zustaendigkeiten
					    WHERE person_id = pers.person_id
					      AND fehlercode = ?
					)
					ORDER BY nachname, vorname, uid";

		return $this->execReadOnlyQuery($query, array($fehlercode));
	}

	/**
	 * Gets Organisationseinheiten not assigned to a Fehler.
	 * @param $fehlercode
	 * @return object
	 */
	public function getNonAssignedOes($fehlercode)
	{
		$query = "SELECT oe_kurzbz, bezeichnung
					FROM public.tbl_organisationseinheit oe
					WHERE aktiv
					AND NOT EXISTS (
					    SELECT 1 FROM system.tbl_fehler_zustaendigkeiten
					    WHERE oe_kurzbz = oe.oe_kurzbz
					    AND fehlercode = ?
					)
					ORDER BY bezeichnung";

		return $this->execReadOnlyQuery($query, array($fehlercode));
	}

	/**
	 * Gets Funktionen not assigned to a Fehler.
	 * @param $fehlercode
	 * @return object
	 */
	public function getNonAssignedFunktionen($fehlercode)
	{
		$query = "SELECT funktion_kurzbz, beschreibung
					FROM public.tbl_funktion funk
					WHERE aktiv
					AND NOT EXISTS (
					    SELECT 1 FROM system.tbl_fehler_zustaendigkeiten
					    WHERE funktion_kurzbz = funk.funktion_kurzbz
					    AND fehlercode = ?
					)
					ORDER BY funktion_kurzbz";

		return $this->execReadOnlyQuery($query, array($fehlercode));
	}
}
