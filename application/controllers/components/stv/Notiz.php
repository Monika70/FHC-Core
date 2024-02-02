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

	public function getUid()
	{
		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$result = getAuthUid();

		$this->outputJsonError($result);
	}

	public function getNotizen($id, $type)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		//check if valid type
		$isValidType = $this->NotizzuordnungModel->isValidType($type);

		if($isValidType)
		{
			$result = $this->NotizModel->getNotizWithDocEntries($id, $type);

			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson(getError($result));
			} else {
				$this->outputJson(getData($result) ?: []);
			}
		}
		else
		{
			//Todo manu (phrases, response?)
			$result = "datatype not yet implemented for notes";
			$this->outputJson(getError($result));
		}
	}

	public function loadNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'notiz_id', 'LEFT');
		$this->NotizModel->addSelect('*');
		$this->NotizModel->addSelect("TO_CHAR(CASE WHEN public.tbl_notiz.updateamum >= public.tbl_notiz.insertamum 
			THEN public.tbl_notiz.updateamum ELSE public.tbl_notiz.insertamum END::timestamp, 'DD.MM.YYYY HH24:MI:SS') AS lastUpdate");
		$this->NotizModel->addLimit(1);

		$result = $this->NotizModel->loadWhere(
			array('notiz_id' => $notiz_id)
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
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function addNewNotiz($id, $paramTyp = null)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');

		$this->load->library('DmsLib');
		$this->load->library('form_validation');

		$uid = getAuthUID();

		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		//Überprüfung ob type übergeben wurde (via Funktions- oder Postparameter)
		$type = null;
		if ($paramTyp)
			$type = $paramTyp;
		if(isset($_POST['typeId']))
			$type = $this->input->post('typeId');

		if(!$type)
		{
			$result = error('kein Type für ID vorhanden', ERROR);
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

			return $this->outputJson(getError($result));
		}

		//Form Validation
		$this->form_validation->set_rules('titel', 'titel', 'callback_titel_required');
		$this->form_validation->set_rules('text', 'text', 'callback_text_required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$erledigt = $this->input->post('erledigt');
		$verfasser_uid = isset($_POST['verfasser']) ? $_POST['verfasser'] : $uid;
		$bearbeiter_uid = isset($_POST['bearbeiter']) ? $_POST['bearbeiter'] : null;
		$type = $this->input->post('typeId');
		$start = $this->input->post('Von');
		$ende = $this->input->post('Bis');

		//Speichern der Notiz und Notizzuordnung inkl Prüfung ob valid type
		$result = $this->NotizModel->addNotizForType($type, $id, $titel, $text, $uid, $start, $ende, $erledigt, $verfasser_uid, $bearbeiter_uid);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$notiz_id = $result->retval;

		//Speichern der Files
		$dms_id_arr = [];
		foreach ($_FILES as $k => $file)
		{
			$dms = array(
				'kategorie_kurzbz'  => 'notiz',
				'version'           => 0,
				'name'              => $file["name"],
				'mimetype'          => $file["type"],
				'insertamum'        => date('c'),
				'insertvon'         => $uid
			);

			//Todo(manu) check if filetypes weiter eingeschränkt werden sollen
			$result = $this->dmslib->upload($dms, $k, ['*']);
/*			$result = $this->dmslib->upload($dms, $k, ['application/pdf','application/x.fhc-dms+json']);*/
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$dms_id_arr[] = $result->retval['dms_id'];
		}

		//Eintrag in Notizdokument speichern
		if($dms_id_arr)
		{
			// Loads model Notizdokument_model
			$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
			foreach($dms_id_arr as $dms_id)
			{
				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
			}
		}

		return $this->outputJsonSuccess(true);
	}

	public function updateNotiz($notiz_id)
	{
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$this->load->library('form_validation');
		$this->load->library('DmsLib');

		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		if(!$notiz_id)
		{
			return $this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}

		//Form Validation
		$this->form_validation->set_rules('titel', 'titel', 'callback_titel_required');
		$this->form_validation->set_rules('text', 'text', 'callback_text_required');

		if ($this->form_validation->run() == false)
		{
			return $this->outputJsonError($this->form_validation->error_array());
		}

		//update Notiz
		$uid = getAuthUID();
		$titel = $this->input->post('titel');
		$text = $this->input->post('text');
		$verfasser_uid = $this->input->post('verfasser');
		$bearbeiter_uid = isset($_POST['bearbeiter']) ? $_POST['bearbeiter'] : $uid;
		$erledigt = $this->input->post('erledigt');
		//$type = $this->input->post('typeId'); //soll auch dieser geändert werden können?
		$start = $this->input->post('Von');
		$ende = $this->input->post('Bis');

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

		//update(1) laden aller bereits mit dieser notiz_id verknüpften DMS-Einträge
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');
		$this->NotizdokumentModel->addJoin('campus.tbl_dms_version', 'dms_id');
		$dms_uploaded = null;

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		}
		elseif (!hasData($result))
		{
			$dms_id_arr = null;
		}
		else
		{
			$result = getData($result);
			foreach($result as $doc) {
				$dms_id_arr[] = array(
					'name' => $doc->name,
					'dms_id' => $doc->dms_id
					);
			}
		}

		foreach ($_FILES as $k => $file)
		{
			//update(2) alle neuen files (alle außer type application/x.fhc-dms+json) anhängen
			if($file["type"] == 'application/x.fhc-dms+json')
			{
				$dms_uploaded[] = array(
					'name' => $file["name"]
				);
			}
			else
			{
				$dms = array(
					'kategorie_kurzbz'  => 'notiz',
					'version'           => 0,
					'name'              => $file["name"],
					'mimetype'          => $file["type"],
					'insertamum'        => date('c'),
					'insertvon'         => $uid
				);

				//Todo(manu) check if filetypes weiter eingeschränkt werden sollen
				$result = $this->dmslib->upload($dms, $k, array('*'));

				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
				$dms_id = $result->retval['dms_id'];

				$result = $this->NotizdokumentModel->insert(array('notiz_id' => $notiz_id, 'dms_id' => $dms_id));
				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
			}
		}

		//update(3) check if Dateien gelöscht wurden
		if(count($dms_uploaded) != count($dms_id_arr))
		{
			$upload_new_names = array_column($dms_uploaded, "name");

			$filesDeleted = array_filter($dms_id_arr, function ($file) use ($upload_new_names) {
				return !in_array($file["name"], $upload_new_names);
			});

			foreach ($filesDeleted as $file)
			{
				$result = $this->dmslib->removeAll($file['dms_id']);

				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
				else
					$this->outputJson($result);
			}
		}
		return $this->outputJsonSuccess(true);
	}

	public function deleteNotiz($notiz_id)
	{
		//dms_id auslesen aus notizdokument wenn vorhanden
		$dms_id_arr = [];
		$this->load->model('person/Notizdokument_model', 'NotizdokumentModel');

		$result = $this->NotizdokumentModel->loadWhere(array('notiz_id' => $notiz_id));

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		}
		elseif (!hasData($result))
		{
			$this->outputJson($result);
		}
		else
		{
			$result = getData($result);
			foreach($result as $doc) {
				$dms_id_arr[] = $doc->dms_id;
			}
		}

		if($dms_id_arr)
		{
			$this->load->library('DmsLib');
			foreach($dms_id_arr as $dms_id)
			{
				$result = $this->dmslib->removeAll($dms_id);

				if (isError($result))
				{
					$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
					return $this->outputJson(getError($result));
				}
				else
					$this->outputJson($result);
			}
		}

		//Todo(manu) rollback?
		//delete Notiz und Notizzuordnung
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->NotizModel->addJoin('public.tbl_notizzuordnung', 'notiz_id');

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


		$this->NotizModel->addSelect('campus.tbl_dms_version.*');

		$this->NotizModel->addJoin('public.tbl_notiz_dokument', 'ON (public.tbl_notiz_dokument.notiz_id = public.tbl_notiz.notiz_id)');
		$this->NotizModel->addJoin('campus.tbl_dms_version', 'ON (public.tbl_notiz_dokument.dms_id = campus.tbl_dms_version.dms_id)');

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

	public function getMitarbeiter($searchString)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$result = $this->MitarbeiterModel->searchMitarbeiter($searchString);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function titel_required($value)
	{
		if (empty($value)) {
			$this->form_validation->set_message('titel_required',  $this->p->t('ui','error_fieldRequired',['field' => 'Titel']));
			return false;
		}
		else
		{
			return true;
		}
	}

	public function text_required($value)
	{
		if (empty($value)) {
			$this->form_validation->set_message('text_required', $this->p->t('ui','error_fieldRequired',['field' => 'Text']));
			return false;
		}
		else
		{
			return true;
		}
	}
}
