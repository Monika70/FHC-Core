<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


class Notiz extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getNotizen($person_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		$result = $this->NotizModel->getNotizWithDocEntries($person_id);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result) ?: []);
		}
	}

	public function loadNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'notiz_id', 'LEFT');

		$this->NotizModel->addSelect('*');

		$this->NotizModel->addLimit(1);

		$result = $this->NotizModel->loadWhere(
			array('notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result); //success mit Wert null
			//	$this->outputJson(getData($result) ?: []);
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function addNewNotiz($person_id)
	{
/*		var_dump($this->input->post('titel'));
		var_dump($this->input->post('anhang'));*/
		var_dump($_FILES);

		$this->load->library('form_validation');
		$this->load->library('DmsLib');

		$name = $this->input->post('anhang')['name'];


		//$this->DmsLib->add($name, $mimetype, $fileHandle);


/*		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}*/

		$this->load->model('person/Notiz_model', 'NotizModel');

		$uid = getAuthUID();
		$titel = isset($_POST['titel']) ? $_POST['titel'] : null;
		$text = isset($_POST['text']) ? $_POST['text'] : null;
		$verfasser_uid = $uid;
		$start = isset($_POST['von']) ? $_POST['von'] : null;
		$ende = isset($_POST['bis']) ? $_POST['bis'] : null;
		$erledigt = $_POST['erledigt'];

		$result = $this->NotizModel->addNotizForPersonWithDoc($person_id, $titel, $text, $erledigt, $verfasser_uid, $start, $ende);

	//	var_dump($result);

/*		$result = $this->NotizModel->insert(
			[
				'titel' => $titel,
				'text' =>  $text,
				'insertvon' => $uid,
				'insertamum' => date('c'),
				'verfasser_uid' => $verfasser_uid,
				'bearbeiter_uid' => $bearbeiter_uid,
				'start' => $start,
				'ende' => $ende,
				'erledigt' => $_POST['erledigt'],
				//'dms_id' => $dms_id
			]
		);*/
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		return $this->outputJsonSuccess(true);
	}

	public function updateNotiz($notiz_id)
	{
		$uid = getAuthUID();
		$this->load->library('form_validation');
		$_POST = json_decode($this->input->raw_input_stream, true);

		$this->form_validation->set_rules('titel', 'titel', 'required');
		$this->form_validation->set_rules('text', 'Text', 'required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$this->load->model('person/Notiz_model', 'NotizModel');

		if(!$notiz_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

	//	$person_id = isset($_POST['person_id']) ? $_POST['person_id'] : null;
		$uid = getAuthUID();
		$titel = isset($_POST['titel']) ? $_POST['titel'] : null;
		$text = isset($_POST['text']) ? $_POST['text'] : null;
		$verfasser_uid = isset($_POST['verfasser_uid']) ? $_POST['verfasser_uid'] : null;
		$bearbeiter_uid = $uid;
		$start = isset($_POST['von']) ? $_POST['von'] : null;
		$ende = isset($_POST['bis']) ? $_POST['bis'] : null;
		$erledigt = $_POST['erledigt'];

		$result = $this->NotizModel->update(
			[
				'notiz_id' => $notiz_id
			],
			[
				'titel' =>  $titel,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'text' => $text,
				'verfasser_uid' => $verfasser_uid,
				'bearbeiter_uid' => $bearbeiter_uid,
				'start' => $start,
				'ende' => $ende,
				'erledigt' => $erledigt
			]
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteNotiz ($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		$result = $this->NotizModel->delete(
			array('notiz_id' => $notiz_id)
		);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		return $this->outputJsonSuccess(current(getData($result)));
	}

	public function loadDokumente($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		//TODO(manu) check, ob mehr Dateien bzw. -versionen
		//warum nur ein Eintrag???
		$this->NotizModel->addSelect('campus.tbl_dms_version.*');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument','ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version','ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

		$result = $this->NotizModel->loadWhere(
			array('public.tbl_notiz.notiz_id' => $notiz_id)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$this->outputJsonSuccess(getData($result));
		}
	}

}