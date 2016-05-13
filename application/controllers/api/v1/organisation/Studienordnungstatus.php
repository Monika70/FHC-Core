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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Studienordnungstatus extends APIv1_Controller
{
	/**
	 * Studienordnungstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StudienordnungstatusModel
		$this->load->model('organisation/studienordnungstatus_model', 'StudienordnungstatusModel');
		// Load set the uid of the model to let to check the permissions
		$this->StudienordnungstatusModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStudienordnungstatus()
	{
		$studienordnungstatusID = $this->get('studienordnungstatus_id');
		
		if(isset($studienordnungstatusID))
		{
			$result = $this->StudienordnungstatusModel->load($studienordnungstatusID);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postStudienordnungstatus()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['studienordnungstatus_id']))
			{
				$result = $this->StudienordnungstatusModel->update($this->post()['studienordnungstatus_id'], $this->post());
			}
			else
			{
				$result = $this->StudienordnungstatusModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($studienordnungstatus = NULL)
	{
		return true;
	}
}