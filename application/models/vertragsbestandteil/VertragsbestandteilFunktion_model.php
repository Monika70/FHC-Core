<?php
/**
 * Description of VertragsbestandteilFunktion_model
 *
 * @author bambi
 */
class VertragsbestandteilFunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_funktion';
		$this->pk = 'vertragsbestandteil_id';
	}
}
