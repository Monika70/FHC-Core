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

class Entwicklungsteam extends APIv1_Controller
{
	/**
	 * Entwicklungsteam API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model EntwicklungsteamModel
		$this->load->model('codex/entwicklungsteam_model', 'EntwicklungsteamModel');
		// Load set the uid of the model to let to check the permissions
		$this->EntwicklungsteamModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getEntwicklungsteam()
	{
		$entwicklungsteamID = $this->get('entwicklungsteam_id');
		
		if(isset($entwicklungsteamID))
		{
			$result = $this->EntwicklungsteamModel->load($entwicklungsteamID);
			
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
	public function postEntwicklungsteam()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['entwicklungsteam_id']))
			{
				$result = $this->EntwicklungsteamModel->update($this->post()['entwicklungsteam_id'], $this->post());
			}
			else
			{
				$result = $this->EntwicklungsteamModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($entwicklungsteam = NULL)
	{
		return true;
	}
}