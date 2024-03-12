<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

class Config extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	public function student()
	{
		$result = [];
		$result['details'] = [
			'title' => 'Details',
			'component' => './Stv/Studentenverwaltung/Details/Details.js'
		];
		$result['notizen'] = [
			'title' => 'Notizen',
			'component' => './Stv/Studentenverwaltung/Details/Notizen.js'
		];
		$result['kontakt'] = [
			'title' => 'Kontakt',
			'component' => './Stv/Studentenverwaltung/Details/Kontakt.js'
		];
		$result['prestudent'] = [
			'title' => 'PreStudentIn',
			'component' => './Stv/Studentenverwaltung/Details/Prestudent.js'
		];
		$result['status'] = [
			'title' => 'Status',
			'component' => './Stv/Studentenverwaltung/Details/Status.js'
		];
		$result['noten'] = [
			'title' => 'Noten',
			'component' => './Stv/Studentenverwaltung/Details/Noten.js'
		];

		Events::trigger('stv_conf_student', function & () use (&$result) {
			return $result;
		});

		$this->outputJsonSuccess($result);
	}

	public function students()
	{
		$this->outputJsonSuccess([]);
	}
}
