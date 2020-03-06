<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library that contains all the needed functionalities to operate with the Jobs Queue System
 */
class JobsQueueLib
{
	// Job statuses
	const STATUS_NEW = 'new';
	const STATUS_RUNNING = 'running';
	const STATUS_DONE = 'done';
	const STATUS_FAILED = 'failed';

	// Job object properties
	const PROPERTY_JOBID = 'jobid';
	const PROPERTY_CREATIONTIME = 'creationtime';
	const PROPERTY_TYPE = 'type';
	const PROPERTY_STATUS = 'status';
	const PROPERTY_INPUT = 'input';
	const PROPERTY_OUTPUT = 'output';
	const PROPERTY_START_TIME = 'starttime';
	const PROPERTY_END_TIME = 'endtime';
	const PROPERTY_ERROR = 'error';

	private $_ci; // CI instance

	/**
	 * Constructor
	 */
	public function __construct($authenticate = true)
	{
		// Gets CI instance
		$this->_ci =& get_instance();

		// Loads all needed models
		$this->_ci->load->model('system/JobsQueue_model', 'JobsQueueModel');
		$this->_ci->load->model('system/JobTypes_model', 'JobTypesModel');
		$this->_ci->load->model('system/JobStatuses_model', 'JobStatusesModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To get all the most recently added jobs using the given job type
	 */
	public function getLastJobs($type)
	{
		$this->_ci->JobsQueueModel->resetQuery();

		$this->_ci->JobsQueueModel->addOrder('creationtime', 'DESC');

		return $this->_ci->JobsQueueModel->loadWhere(array('status' => self::STATUS_NEW, 'type' => $type));
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	public function addNewJobsToQueue($type, $jobs)
	{
		// Checks parameters
		if (isEmptyString($type)) return error('The provided type parameter is not a valid string');
		if (isEmptyArray($jobs)) return error('The provided jobs parameter is not a valid array');

		// Get all the job types
		$dbResult = $this->_ci->JobTypesModel->load();
		if (isError($dbResult)) return $dbResult;
		$types = getData($dbResult);

		// If the given type is not present in database
		if (!$this->_checkJobType($type, $types)) return error('The provided type parameter is not valid');

		$results = $jobs; // returned values
		$errorOccurred = false; // very optimistic

		// Get all the job statuses
		$dbResult = $this->_ci->JobStatusesModel->load();
		if (isError($dbResult)) return $dbResult;
		$statuses = getData($dbResult);

		// Loops through all the provided jobs
		foreach ($results as $job)
		{
			// If the structure of the job object is valid AND the type is valid AND the status is valid
			if ($this->_checkNewJobStructure($job) && $this->_checkJobStatus($job, $statuses))
			{
				$this->_dropNotAllowedPropertiesNewJob($job); // remove the black listed properties from this object

				$job->{self::PROPERTY_TYPE} = $type; // What you asked is what you get!

				// Try to insert the single job into database
				$dbResult = $this->_ci->JobsQueueModel->insert($job);

				// If an error occurred during while inserting in database
				if (isError($dbResult))
				{
					$job->{self::PROPERTY_ERROR} = getError($dbResult); // retrieve the cause and store it in job object
					$errorOccurred = true; // set error occurred flag
				}
				else // otherwise
				{
					$job->{self::PROPERTY_JOBID} = getData($dbResult); // get the jobid and store it in job object
				}
			}
			else // otherwise
			{
				$errorOccurred = true; // set error occurred flag
			}
		}

		// If an error occurred then returns the results in an error object
		if ($errorOccurred) return error($results);

		return success($results); // otherwise return results in a success object
	}

	/**
	 * Updates jobs already present in the jobs queue
	 * jobs is an array of job objects
	 */
	public function updateJobsQueue($type, $jobs)
	{
		// Checks parameters
		if (isEmptyArray($jobs)) return error('The provided jobs parameter is not a valid array');

		$results = $jobs; // returned values
		$errorOccurred = false; // very optimistic

		// Get all the job statuses
		$dbResult = $this->_ci->JobStatusesModel->load();
		if (isError($dbResult)) return $dbResult;
		$statuses = getData($dbResult);

		// Loops through all the provided jobs
		foreach ($results as $job)
		{
			// If the structure of the job object is valid
			if ($this->_checkUpdateJobStructure($job) && $this->_checkJobStatus($job, $statuses))
			{
				$this->_dropNotAllowedPropertiesUpdateJob($job); // remove the black listed properties from this object

				$job->{self::PROPERTY_TYPE} = $type; // What you asked is what you get!

				// Try to update the single job into database
				$dbResult = $this->_ci->JobsQueueModel->update($job->{self::PROPERTY_JOBID}, (array)$job);

				// If an error occurred during while updating in database
				if (isError($dbResult))
				{
					$job->{self::PROPERTY_ERROR} = getError($dbResult); // retrieve the cause and store it in job object
					$errorOccurred = true; // set error occurred flag
				}
			}
			else // otherwise
			{
				$errorOccurred = true; // set error occurred flag
			}
		}

		// If an error occurred then returns the results in an error object
		if ($errorOccurred) return error($results);

		return success($results); // otherwise return results in a success object
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks the job object structure when needed for insert
	 */
	private function _checkNewJobStructure(&$job)
	{
		// If job is a valid object and contains the required properties AND does NOT already contain the property error
		if (is_object($job)
			&& property_exists($job, self::PROPERTY_STATUS)
			&& !property_exists($job, self::PROPERTY_ERROR))
		{
			return true; // it is valid!
		}

		// If not object then object it!
		if (!is_object($job)) $job = new stdClass();

		// If an error property was not already previously stored then store an error message in job object
		if (!property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The structure of the provided job is not valid';
		}

		return false; // better sorry than wrong
	}

	/**
	 * Checks the job object structure when needed for update
	 */
	private function _checkUpdateJobStructure(&$job)
	{
		// If job is a valid object
		if (is_object($job) && property_exists($job, self::PROPERTY_JOBID)) return true; // it is valid!

		// If not object then object it!
		if (!is_object($job)) $job = new stdClass();

		// If an error property was not already previously stored then store an error message in job object
		if (!property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The structure of the provided job is not valid';
		}

		return false; // better sorry than wrong
	}

	/**
	 * Checks if the given job contains a valid type
	 */
	private function _checkJobType($type, $types)
	{
		return $this->_inArray($type, $types, self::PROPERTY_TYPE);
	}

	/**
	 * Checks if the given job contains a valid status
	 */
	private function _checkJobStatus(&$job, $statuses)
	{
		$found = $this->_inArray($job->{self::PROPERTY_STATUS}, $statuses, self::PROPERTY_STATUS);

		// No status was not found and does NOT already contain the property error
		if (!$found && !property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The provided status of this job is not valid'; // store the error message in the object
		}

		return $found;
	}

	/**
	 * Search in an array the given value
	 * The elements of the given array are objects
	 * The given value is compared with the property specified by the $propertyName parameter of each object of the given array
	 */
	private function _inArray($value, $array, $propertyName)
	{
		$found = false;

		foreach ($array as $element)
		{
			if ($value == $element->{$propertyName})
			{
				$found = true;
				break;
			}
		}

		return $found;
	}

	/**
	 * Drop not allowed properties from the given job
	 */
	private function _dropNotAllowedPropertiesNewJob(&$job)
	{
		unset($job->{self::PROPERTY_JOBID});
		unset($job->{self::PROPERTY_CREATIONTIME});
		unset($job->{self::PROPERTY_TYPE});
	}

	/**
	 * Drop not allowed properties from the given job
	 */
	private function _dropNotAllowedPropertiesUpdateJob(&$job)
	{
		unset($job->{self::PROPERTY_CREATIONTIME});
		unset($job->{self::PROPERTY_TYPE});
	}
}
