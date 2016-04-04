<?php
class Rookie_Exception_Http extends Rookie_Exception{
	
	/**
	 * @var     int      http status code
	 */
	protected $_code = 0;

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string   status message, custom content to display with error
	 * @param   array    translation variables
	 * @param   integer  the http status code
	 * @return  void
	 */
	public function __construct($message = NULL, array $variables = NULL, $code = 0)
	{
		if ($code == 0)
		{
			$code = $this->_code;
		}
		parent::__construct($message, $variables, $code);
	}
	
}
?>
