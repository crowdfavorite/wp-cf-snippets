<?php

/**
 * Standard return message class to help ensure
 * consistent handling of json return messages 
 * across the system.
 *
 * @package cfcal_calendar
 */
class cfsp_message {
	private $_html;
	private $_message;
	private $_success;
	
	public function __construct(array $args = array('success' => false, 'html' => null, 'message' => null)) {
		$this->add($args);
	}
	
// Setters
	public function add(array $args = array('success' => false, 'html' => null, 'message' => null)) {
		$this->_success = (bool) $args['success'];
		$this->_html = strval($args['html']);
		$this->_message = strval($args['message']);
	}

// Getters
	public function get_results() {
		return array(
			'success' => trim($this->_success),
			'html' => trim($this->_html),
			'message' => trim($this->_message)
		);
	}
	
	public function get_json() {
		return cfsp_json_encode($this->get_results());
	}

	public function __toString() {
		return $this->get_json();
	}

// Delivery
	/**
	 * Deliver the JSON and get out of the page load.
	 *
	 * @return void
	 */
	public function send() {
		header('Content-type: application/json');
		echo $this->get_json();
		exit;
	}
}

?>