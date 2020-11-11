<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * Klasse Zeitaufzeichnung Geteilte Dienste
 * @create 13-06-2019
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
class zeitaufzeichnung_gd extends basis_db
{
    public $new;		                // boolean
    public $result = array();	        // object array

    // Table columns
    public $zeitaufzeichnungs_gd_id;	// integer
    public $uid;                        // varchar(32)
    public $studiensemester_kurzbz;		// varchar(16)
    public $selbstverwaltete_pause;		// boolean
    public $insertamum;				    // timestamp
    public $insertvon;				    // varchar(32)
    public $updateamum;				    // timestamp
    public $updatevon;				    // varchar(32)
	public $geteilte_pause;             // boolean

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

	/**
     * Loads entry for specific user and semester
     * @return boolean  True, if entry is found.
     */
    public function load($user, $sem)
    {
        if ($user && $sem)
        {
            $qry = '
                SELECT * FROM campus.tbl_zeitaufzeichnung_gd
                    WHERE uid = '.$this->db_add_param($user).
					' AND studiensemester_kurzbz = ' . $this->db_add_param($sem) .
					'limit 1';

			if(!$this->db_query($qry))
			{
				$this->errormsg = 'Fehler bei einer Datenbankabfrage';
				return false;
			}
			if($row = $this->db_fetch_object())
			{
				$this->zeitaufzeichnung_gd_id = $row->zeitaufzeichnung_gd_id;
				$this->uid = $row->uid;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->selbstverwaltete_pause = $this->db_parse_bool($row->selbstverwaltete_pause);
				$this->geteilte_pause = $this->db_parse_bool($row->geteilte_pause);
				return true;
			}
			else
			{
				$this->errormsg = 'Es ist kein Datensatz vorhanden';
				return false;
			}
        }
        else
        {
            $this->errormsg = 'Falsche Parameterübergabe';
            return false;
        }
    }

    /**
     * Saves decision about self-managing breaks during parted working times.
     * @return boolean  True, if saving succeeded.
     */
    public function save()
    {
        if (is_string($this->uid) &&
            is_string($this->studiensemester_kurzbz))
        {
			$qry = '
                INSERT INTO campus.tbl_zeitaufzeichnung_gd (
                    uid,
                    studiensemester_kurzbz,
                    selbstverwaltete_pause,
                    geteilte_pause,
                    insertamum,
                    insertvon                    
                )
                VALUES ('.
                    $this->db_add_param($this->uid). ', '.
                    $this->db_add_param($this->studiensemester_kurzbz). ', '.
                    $this->db_add_param($this->selbstverwaltete_pause, FHC_BOOLEAN, true). ', '.
					$this->db_add_param($this->geteilte_pause, FHC_BOOLEAN, true). ', '.
					$this->db_add_param(date_create()->format('Y-m-d H:i:s')). ', '.
                    $this->db_add_param($this->uid). '
                );
            ';

            if ($this->db_query($qry))
            {
                return true;
            }
            else
            {
                $this->errormsg = 'Fehler beim Speichern der selbstverwalteten Pause';
                return false;
            }
        }
        else
        {
            $this->errormsg = 'Falsche Parameterübergabe';
            return false;
        }
    }

    public function update()
    {
	    if (is_string($this->uid) &&
		    is_string($this->studiensemester_kurzbz))
	    {
		    $qry = '
                UPDATE campus.tbl_zeitaufzeichnung_gd SET '.
                'uid='.$this->db_add_param($this->uid).', '.
			    'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
			    'selbstverwaltete_pause='.$this->db_add_param($this->selbstverwaltete_pause, FHC_BOOLEAN).', '.
			    'geteilte_pause='.$this->db_add_param($this->geteilte_pause, FHC_BOOLEAN).', '.
			    'updateamum='.$this->db_add_param(date_create()->format('Y-m-d H:i:s')).', '.
			    'updatevon='.$this->db_add_param($this->updatevon).' '.
			    'WHERE zeitaufzeichnung_gd_id='.$this->db_add_param($this->zeitaufzeichnung_gd_id).';
            ';

		    if ($this->db_query($qry))
		    {
			    return true;
		    }
		    else
		    {
			    $this->errormsg = 'Fehler beim Update von Zeitaufzeichnung_gd';
			    return false;
		    }
	    }
	    else
	    {
		    $this->errormsg = 'Falsche Parameterübergabe';
		    return false;
	    }
    }
}
