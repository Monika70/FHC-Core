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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notizzuordnung extends APIv1_Controller
{
	/**
	 * Notizzuordnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model NotizzuordnungModel
		$this->load->model('person/notizzuordnung_model', 'NotizzuordnungModel');
		// Load set the uid of the model to let to check the permissions
		$this->NotizzuordnungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getNotizzuordnung()
	{
		$notizzuordnungID = $this->get('notizzuordnung_id');
		
		if (isset($notizzuordnungID))
		{
			$result = $this->NotizzuordnungModel->load($notizzuordnungID);
			
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
	public function postNotizzuordnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['notizzuordnung_id']))
			{
				$result = $this->NotizzuordnungModel->update($this->post()['notizzuordnung_id'], $this->post());
			}
			else
			{
				$result = $this->NotizzuordnungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($notizzuordnung = NULL)
	{
		return true;
	}
}