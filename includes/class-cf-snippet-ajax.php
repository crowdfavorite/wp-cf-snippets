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
		// Authenticated only
		add_action('wp_ajax_cfsp_get_snippet', array($this, 'ajax_get_snippet'));
		add_action('wp_ajax_cfsp_save_snippet', array($this, 'ajax_save_snippet'));
		add_action('wp_ajax_cfsp_post_items_paged', array($this, 'ajax_post_items_paged'));
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

	function ajax_post_items_paged() {
		if (!isset($_POST['cfsp_page']) || empty($_POST['cfsp_page'])) {
			exit();
		}
		$page = $_POST['cfsp_page'];

		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();

			$offset = (CFSP_SHOW_POST_COUNT*($page-1));
			$keys = $cf_snippet->get_all_post_keys(CFSP_SHOW_POST_COUNT, $offset);

			$post_table_content = '';
			$total_pages = ceil($cf_snippet->get_post_key_count()/CFSP_SHOW_POST_COUNT);

			if (is_array($keys) && !empty($keys)) {
				foreach ($keys as $key) {
					$post_table_content .= $cf_snippet->admin_display($key);
				}
			}

			include(CFSP_DIR . 'views/ajax-post-items-paged.php');
		}
	}


}
