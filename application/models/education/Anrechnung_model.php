<?php
class Anrechnung_model extends DB_Model
{
	const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnung';
		$this->pk = 'anrechnung_id';
	}
	
	/**
	 * Creates new Anrechnungsantrag.
	 * Saves new Anrechnung and sets Anrechnungstatus for the new Anrechnung.
	 *
	 * @param $prestudent_id
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @param $begruendung_id
	 * @param $dms_id   DMS ID of uploaded Nachweisdokument
	 * @param null $anmerkung_student  = Herkunft der Kenntnisse
	 * @return array
	 */
	public function createAnrechnungsantrag(
		$prestudent_id, $studiensemester_kurzbz, $lehrveranstaltung_id,
		$begruendung_id, $dms_id, $anmerkung_student = null
	)
	{
		// Start DB transaction
		$this->db->trans_start(false);
		
		// Save Anrechnung
		$result = $this->AnrechnungModel->insert(array(
			'prestudent_id' => $prestudent_id,
			'lehrveranstaltung_id' => $lehrveranstaltung_id,
			'begruendung_id' => $begruendung_id,
			'dms_id' => $dms_id,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'anmerkung_student' => $anmerkung_student,
			'insertvon' => $this->_uid
		));
		
		// Store just inserted Anrechnung ID
		$lastInsert_anrechnung_id = $result->retval;
		
		// Save Anrechnungstatus
		$this->AnrechnungModel->saveAnrechnungstatus($lastInsert_anrechnung_id, self::ANRECHNUNGSTATUS_PROGRESSED_BY_STGL);
		
		// Transaction complete
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === false)
		{
			$this->db->trans_rollback();
			return error('Failed inserting Anrechnung', EXIT_ERROR);
		}
		
		return success();
	}
	
	/**
	 * Save Anrechnungstatus.
	 * @param $anrechnung_id
	 * @param $status_kurzbz
	 * @return array|null
	 */
	public function saveAnrechnungstatus($anrechnung_id, $status_kurzbz)
	{
		$qry = '
			INSERT INTO lehre.tbl_anrechnung_anrechnungstatus (
				anrechnung_id, status_kurzbz, insertvon
			) VALUES ( ?, ?, ?);
		';
		
		return $this->execQuery($qry, array($anrechnung_id, $status_kurzbz, getAuthUID()));
	}
	
	/**
	 * Get the last inserted Anrechnungstatus
	 * @param $anrechnung_id
	 * @return array|null
	 */
	public function getLastAnrechnungstatus($anrechnung_id, $status_kurzbz = null)
	{
		if (is_string($status_kurzbz))
		{
			$qry = '
				SELECT *
				FROM lehre.tbl_anrechnungstatus
				JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
				WHERE anrechnung_id = ?
				AND status_kurzbz = ?
				ORDER BY insertamum DESC
				LIMIT 1
			';
			
			return $this->execQuery($qry, array($anrechnung_id, $status_kurzbz));
		}
		
		
		$qry = '
			SELECT *
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = ?
			ORDER BY insertamum DESC
			LIMIT 1
		';
		
		return $this->execQuery($qry, array($anrechnung_id));
	}
	
	/**
	 * Get status approved / rejected, if present.
	 * @param $anrechnung_id
	 * @return array|null
	 */
	public function getApprovedOrRejected($anrechnung_id)
	{
		$qry = '
			SELECT *
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = ?
			AND (status_kurzbz = \'approved\' OR status_kurzbz = \'rejected\')
			ORDER BY insertamum DESC
			LIMIT 1
		';
		
		return $this->execQuery($qry, array($anrechnung_id));
	}
	
	/**
	 * Delete Anrechnungstatus.
	 *
	 * @param $anrechnungstatus_id
	 */
	public function deleteAnrechnungstatus($anrechnungstatus_id){
		
		$qry = '
			DELETE FROM lehre.tbl_anrechnung_anrechnungstatus
			WHERE anrechnungstatus_id = ?
		';
		
		return $this->execQuery($qry, array($anrechnungstatus_id));
	}
}
