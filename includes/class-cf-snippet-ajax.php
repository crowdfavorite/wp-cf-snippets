<?php
/**
 * class CF_Snippet_Ajax
 * Handles the
 */

class CF_Snippet_Ajax extends CF_Snippet_Base {
	function __construct() {
		$this->add_actions();
	}

	function add_actions() {
		add_action('wp_ajax_cfsp_preview', array($this, 'ajax_preview'));
		add_action('wp_ajax_cfsp_get_snippet', array($this, 'ajax_get_snippet'));
		add_action('wp_ajax_cfsp_save_snippet', array($this, 'ajax_save_snippet'));
		add_action('wp_ajax_cfsp_typeahead_key', array($this, 'ajax_typeahead_key'));
	}

	function ajax_get_snippet() {
		check_ajax_referer('cf-snippets-key', 'security');

		if ($this->user_can_admin_snippets() == false) {
			header('HTTP/1.0 403 Forbidden');
			echo json_encode(array('result' => 'error', 'data' => '[CFSP=002] Unauthorized access'));
			exit();
		}

		global $cf_snippet;
		if (empty($cf_snippet)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		if (empty($_GET['key'])) {
			header('HTTP/1.0 400 Bad Request');
			echo json_encode(array('result' => 'error', 'data' => 'The parameter "key" is required.'));
		}
		$snippet_post = $cf_snippet->get_snippet_post_by_key($_GET['key']);
		if (empty($snippet_post)) {
			header('HTTP/1.0 404 Not Found');
			echo json_encode(array('result' => 'error', 'data' => 'No snippet found'));
		}
		else if (is_wp_error($snippet_post)) {
			header('HTTP/1.0 500 Internal Server Error');
			echo json_encode(array('result' => 'error', 'data' => 'There was an error processing your request.'));
		}
		else {
			echo json_encode(array('result' => 'success', 'data' => $snippet_post));
		}
		exit();
	}

	function ajax_save_snippet() {
		check_ajax_referer('cf-snippets-key', 'security');

		if ($this->user_can_admin_snippets() == false) {
			return;
		}

		global $cf_snippet;
		if (empty($cf_snippet)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		$post_arr = array_merge(array(
			'ID' => null,
			'post_name' => 'cfsp-new-snippet',
			'post_title' => 'New Snippet',
			'post_content' => '',
		), $_POST);
		$result = $cf_snippet->save_snippet_post($post_arr);
		if (empty($result)) {
			header('HTTP/1.0 500 Internal Server Error');
			echo json_encode(array("result" => "error", "data" => "No data returned"));
		}
		else if (is_wp_error($result)) {
			header('HTTP/1.0 500 Internal Server Error');
			echo json_encode(array("result" => "error", "data" => "There was an error processing your request."));
		}
		else {
			$snippet_post = get_post($result);
			$keys = $cf_snippet->get_keys();
			$data = array("snippet" => $snippet_post, "keys" => $keys);
			echo json_encode(array("result" => "success", "data" => $data));
		}
		exit();
	}

	function ajax_preview() {
		global $cf_snippet;
		check_ajax_referer('cf-snippets-key', 'security');

		$key = isset($_REQUEST['key']) ? stripslashes($_REQUEST['key']) : '';

		if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		
		$response = array(
			'result' => 'error',
			'data' => 'Snippet not found'
		);

		if (!empty($key) && $cf_snippet->exists($key)) {
			$response['result'] = 'success';
			$response['data'] = $cf_snippet->get($key, '', false);
		}
		else {
			header('HTTP/1.0 400 Bad Request');
		}
		echo json_encode($response);
		exit();
	}
	
	function ajax_typeahead_key() {global $cf_snippet;
		check_ajax_referer('cf-snippets-key', 'security');

		$key = isset($_GET['snippet_key']) ? stripslashes($_GET['snippet_key']) : '';
		$return = array(
			'result' => 'success',
			'data' => array(),
		);
		if (strlen($key) == 0) {
			header('HTTP/1.0 400 Bad Request');
			$return['result'] = 'error';
			$return['data'] = 'Missing search string';
		}
		else {
			// We're manually building this query to only return back the fields we require.
			global $wpdb;
			$key .= '%';
			$query = 
				$wpdb->prepare(
					"SELECT post_name as `value`, post_name as `text` FROM {$wpdb->posts} WHERE post_type = '_cf_snippet' AND post_status = 'publish' AND post_name LIKE %s ORDER BY LENGTH(post_name) ASC, post_name ASC LIMIT 5",
					$key
				);
			$results = $wpdb->get_results(
				$query,
				ARRAY_A
			);
			$return['data'] = $results;
		}
		echo json_encode($return);
		exit();
	}
}
