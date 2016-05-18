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

class Besqual extends APIv1_Controller
{
	/**
	 * Besqual API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BesqualModel
		$this->load->model('codex/besqual_model', 'BesqualModel');
		// Load set the uid of the model to let to check the permissions
		$this->BesqualModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBesqual()
	{
		$besqualID = $this->get('besqual_id');
		
		if(isset($besqualID))
		{
			$result = $this->BesqualModel->load($besqualID);
			
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
	public function postBesqual()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['besqual_id']))
			{
				$result = $this->BesqualModel->update($this->post()['besqual_id'], $this->post());
			}
			else
			{
				$result = $this->BesqualModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($besqual = NULL)
	{
		return true;
	}
}