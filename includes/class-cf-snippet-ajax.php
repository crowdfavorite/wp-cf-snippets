<?php
/**
 * class CF_Snippet_Ajax
 * Handles the
 */

class CF_Snippet_Ajax extends CF_Snippet_Base {
	public function __construct() {
		$this->add_actions();
	}

	public function add_actions() {
		add_action('wp_ajax_cfsp_preview', array($this, 'ajax_preview'));
		add_action('wp_ajax_cfsp_get_snippet', array($this, 'ajax_get_snippet'));
		add_action('wp_ajax_cfsp_save_snippet', array($this, 'ajax_save_snippet'));
		add_action('wp_ajax_cfsp_typeahead_key', array($this, 'ajax_typeahead_key'));
	}

	public function ajax_get_snippet() {
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

	public function ajax_save_snippet() {
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
			$data = array("snippet" => $snippet_post);
			echo json_encode(array("result" => "success", "data" => $data));
		}
		exit();
	}

	public function ajax_preview() {
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
	
	public function ajax_typeahead_key() {

		global $cf_snippet;
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
			add_filter( 'posts_clauses', array($this, 'ajax_typeahed_query'), 0, 2 );
			$args = array (
				 'post_type' => '_cf_snippet',
				 'post_status' => 'publish',
				 'posts_per_page' => 5,
				 'post_name' => $key . '%',
				 'snippet_search' => $key . '%',
				 'fields' => 'ids',
			);
			$query = new WP_Query($args);

			$results = array();
			foreach ($query->posts as $snippet) {
				$results[] = array(
					'value' => $snippet,
					'text' => $snippet,
				);
			}
			remove_filter('posts_clauses', array($this, 'ajax_typeahed_query'));

			$return['data'] = $results;

			echo json_encode($return);
			exit();
		}
	}

	public function ajax_typeahed_query($pieces, $query) {
		$pieces['fields'] = 'post_name';
		$pieces['where'] = $pieces['where'] . " AND post_name LIKE '" . esc_sql( $query->get('snippet_search')) . "'";
		$pieces['orderby'] = 'LENGTH(post_name) ASC, post_name ASC';

		return $pieces;
	}
}

