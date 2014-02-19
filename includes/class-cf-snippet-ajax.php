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

		// Iframe Preview Display
		add_action('admin_post_cfsp_iframe_preview', array($this, 'iframe_preview'));

		// Fully Hooked Up
		add_action('wp_ajax_cfsp_preview', array($this, 'ajax_preview'));

		// Authenticated only
		add_action('wp_ajax_cfsp_get_snippet', array($this, 'ajax_get_snippet'));
		add_action('wp_ajax_cfsp_save_snippet', array($this, 'ajax_save_snippet'));
		add_action('wp_ajax_cfsp_post_items_paged', array($this, 'ajax_post_items_paged'));

		//TODO
		add_action('wp_ajax_cfsp_new', array($this, 'ajax_new'));
		add_action('wp_ajax_cfsp_new_add', array($this, 'ajax_new_add'));
		/*
				if (!empty($_POST['cfsp_key']) || !empty($_POST['cfsp_description'])) {
					cfsp_add_new(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
		 */

		add_action('wp_ajax_cfsp_save', array($this, 'ajax_save'));
		/*
				if (!empty($_POST['cfsp_id'])) {
					cfsp_save_snippet_post(stripslashes($_POST['cfsp_id']), stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				else if (!empty($_POST['cfsp_key'])) {
					cfsp_save(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
		 */

		add_action('wp_ajax_cfsp_edit', array($this, 'ajax_edit'));
		/*
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_edit(stripslashes($_POST['cfsp_key']));
				}
		 */

		add_action('wp_ajax_cfsp_delete', array($this, 'ajax_delete'));
		/*
				if (!empty($_POST['cfsp_key'])) {
					if (!empty($_POST['cfsp_delete_confirm']) && $_POST['cfsp_delete_confirm'] == 'yes') {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), true);
					}
					else {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), false);
					}
				}
		 */

		add_action('admin_enqueue_scripts', array($this, 'set_nonces'));
	}

	function set_nonces() {
		wp_localize_script('cfsp-admin-js-behavior', 'nonces', array(
			"cfsp_get_snippet" => wp_create_nonce("cfsp_get_snippet"),
			"cfsp_save_snippet" => wp_create_nonce("cfsp_save_snippet"),
			"cfsp_post_items_paged" => wp_create_nonce("cfsp_post_items_paged"),
			"cfsp_new" => wp_create_nonce("cfsp_new"),
			"cfsp_new_add" => wp_create_nonce("cfsp_new_add"),
			"cfsp_save" => wp_create_nonce("cfsp_save"),
			"cfsp_edit" => wp_create_nonce("cfsp_save"),
			"cfsp_preview" => wp_create_nonce("cfsp_preview"),
			"cfsp_delete" => wp_create_nonce("cfsp_delete"),
		));
	}


	function iframe_preview() {
		global $cf_snippet;

		if (!isset($_GET['key'])) {
			return;
		}

		$key = stripslashes($_GET['key']);

		if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
			$cf_snippet = new CF_Snippet_Manager();
		}

		if (!empty($key) && $cf_snippet->exists($key)) {
			echo $cf_snippet->get($key);
		}
	}


	function ajax_get_snippet() {
		check_ajax_referer('cfsp_get_snippet');

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
		check_ajax_referer('cfsp_save_snippet');
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

	function ajax_preview() {
		global $cf_snippet;
		check_ajax_referer('cfsp_preview');

		if (!isset($_POST['key'])) {
			return;
		}

		$key = stripslashes($_POST['key']);

		if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
			$cf_snippet = new CF_Snippet_Manager();
		}

		if (!empty($key) && $cf_snippet->exists($key)) {
			include(CFSP_DIR . 'views/ajax-preview-exists.php');
		}
		else {
			include(CFSP_DIR . 'views/ajax-preview-error.php');
		}
		die();
	}

function cfsp_ajax_new() {
	global $cf_snippet;
	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}
	include(CFSP_DIR . 'views/ajax-new.php');
	die();
}

function cfsp_ajax_edit($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		include(CFSP_DIR . 'views/ajax-edit-exists.php');
	}
	else {
		include(CFSP_DIR . 'views/ajax-edit-error.php');
	}
	die();
}


function cfsp_ajax_delete($key, $confirm = false) {
	global $cf_snippet;
	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		// If the delete has been confirmed, remove the key and return
		if ($confirm) {
			$cf_snippet->remove($key);
		}
		else {
			include(CFSP_DIR . 'views/ajax-delete-exists.php');
		}
	}
	else {
		include(CFSP_DIR . 'views/ajax-delete-error.php');
	}
	die();
}

function cfsp_add_new($key = '', $description = '', $content = '') {
	if (empty($key)) {
		$key = $description;
		if (strlen($key) > 20) {
			$key = substr($key, 0, 20);
		}
	}
	global $cf_snippet;
	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$new_key = $cf_snippet->add($key, $content, $description);
	// Now that we have inserted, get the row to insert into the table
	echo $cf_snippet->admin_display($new_key);
}

function cfsp_save($key, $description = '', $content = '') {
	if (empty($key)) { return false; }

	global $cf_snippet;
	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$cf_snippet->save($key, $content, $description);
}

function cfsp_save_snippet_post($id, $key = '', $description = '', $content = '') {
	if (empty($id)) { return false; }
	global $cf_snippet;

	$post_arr = array('ID' => $id, 'post_name' => $key, 'post_title' => $description, 'post_content' => $content);

	if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}

	$cf_snippet->save_snippet_post($post_arr);
}


}
