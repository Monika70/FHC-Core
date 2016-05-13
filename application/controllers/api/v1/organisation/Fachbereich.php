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

class Fachbereich extends APIv1_Controller
{
	/**
	 * Fachbereich API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model FachbereichModel
		$this->load->model('organisation/fachbereich_model', 'FachbereichModel');
		// Load set the uid of the model to let to check the permissions
		$this->FachbereichModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getFachbereich()
	{
		$fachbereichID = $this->get('fachbereich_id');
		
		if(isset($fachbereichID))
		{
			$result = $this->FachbereichModel->load($fachbereichID);
			
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
	public function postFachbereich()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['fachbereich_id']))
			{
				$result = $this->FachbereichModel->update($this->post()['fachbereich_id'], $this->post());
			}
			else
			{
				$result = $this->FachbereichModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($fachbereich = NULL)
	{
		return true;
	}
}