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
		add_action('wp_ajax_cfsp_get_snippet', array($this, 'ajax_get_snippet'));
		add_action('wp_ajax_cfsp_save_snippet', array($this, 'ajax_save_snippet'));
	}

	function ajax_get_snippet() {
		check_ajax_referer('csfp_get_snippet');

		global $cf_snippet;
		if (empty($cf_snippet)) {
			$cf_snippet = new CF_Snippet();
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
		check_ajax_referer('csfp_save_snippet');
		// TODO: Determine appropriate permissions for creating snippets
		global $cf_snippet;
		if (empty($cf_snippet)) {
			$cf_snippet = new CF_Snippet();
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
}
