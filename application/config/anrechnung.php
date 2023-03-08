<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


// Deadline for Application given as Time-Interval after Semesterstart.
$config['interval_blocking_application'] = 'P1M';

// Lehrveranstaltungen with these grades will be blocked for application
$config['grades_blocking_application'] = array(
	5,  // nicht genügend
	6,  // angerechnet
	9,  // noch nicht eingetragen
	13, // nicht erfolgreich absolviert
	14, // nicht bestanden,
	15, // nicht teilgenommen
	18  // unentschuldigt
);