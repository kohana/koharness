<?php
/**
 * Fake session driver that just stores the session data in memory in the instance - so there are no issues with
 * setting cookies etc during tests.
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2014 Kohana Team
 * @license   http://kohanaframework.org/license
 */

/**
 * Fake session driver that stores values in the instance.
 */
class Session_Fake extends Session {

	/**
	 * @var string the serialised data
	 */
	protected $data = NULL;

	/**
	 * @var string the session ID
	 */
	protected $id = NULL;

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string $id session id
	 *
	 * @return  string
	 */
	protected function _read($id = NULL)
	{
		$this->id = $id;
		return $this->data;
	}

	/**
	 * Generate a new session id and return it.
	 *
	 * @return  string
	 */
	protected function _regenerate()
	{
		$this->id++;
	}

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	protected function _write()
	{
		$this->data = $this->__toString();
	}

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 */
	protected function _destroy()
	{
		$this->data = NULL;
		return TRUE;
	}

	/**
	 * Restarts the current session.
	 *
	 * @return  boolean
	 */
	protected function _restart()
	{
		return true;
	}

	/**
	 * Retrieves the current session ID
	 *
	 * @return string
	 */
	public function id()
	{
		return $this->id;
	}

}