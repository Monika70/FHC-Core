<?php
class Zeitsperre_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitsperre';
		$this->pk = 'zeitsperre_id';
	}

    public function deleteEntriesForCurrentDay()
    {
        $today = date('Y-m-d');
        $qry = "DELETE FROM " . $this->dbTable . " 
                WHERE vondatum = '" . $today . "';";

        return $this->execQuery($qry);
    }

    public function getMitarbeiterListWithPendingVacation()
    {
	    $qry = "SELECT 
					DISTINCT mitarbeiter_uid
				FROM 
					campus.tbl_zeitsperre 
				WHERE 
					freigabeamum is NULL 
					AND zeitsperretyp_kurzbz='Urlaub'
					AND vondatum>=now()
				ORDER BY mitarbeiter_uid ASC;";
	    return $this->execQuery($qry);
    }
}
