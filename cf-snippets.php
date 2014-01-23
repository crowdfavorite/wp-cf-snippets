<?php
/*
Plugin Name: CF Snippets
Plugin URI: http://crowdfavorite.com
Description: Provides admin level users the ability to define html snippets for use in templates, content, or widgets.
Version: 4.0.0-dev
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/
/*
 * @package cf-snippets
 *
 * Copyright (c) 2009-2014 Crowd Favorite, Ltd. All rights reserved.
 * http://crowdfavorite.com
 *
 * **********************************************************************
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * **********************************************************************
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

## Constants

define('CFSP_VERSION', '4.0.0-dev');
define('CFSP_DIR', plugin_dir_path(__FILE__));
// CFSP_DIR_URL is defined during init in CF_Snippet_Core
define('CFSP_SHOW_POST_COUNT', 10);

// Load the text domain
load_plugin_textdomain('cfsp');


// Autoloading
function cfsp_autoload($class) {
	if (0 !== strpos($class, 'CF_Snippet_')) {
		return;
	}

	$file = 'includes/class-' . str_replace('_', '-', strtolower($class)) . '.php';
	include $file;
}

if (function_exists('spl_autoload_register')) {
	spl_autoload_register('cfsp_autoload');
}
else {
	include 'includes/class-cf-snippet-base.php';
	include 'includes/class-cf-snippet-core.php';
	include 'includes/class-cf-snippet-manager.php';
	if (is_admin()) {
		include 'includes/class-cf-snippet-upgrader.php';
	}
}

// Load the scaffolding up here
$cf_snippet = new CF_Snippet_Core();

// Load the currently non-class support code and template API
include 'includes/template.php';
include 'includes/shortcode.php';
include 'includes/widget.php';
include 'includes/tinymce.php';


function cfsp_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfsp_iframe_preview':
				if (!empty($_GET['cfsp_key'])) {
					cfsp_iframe_preview(stripslashes($_GET['cfsp_key']));
				}
				die();
				break;
			case 'cfsp-dialog':
				cfsp_dialog();
				die();
				break;
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfsp_new':
				cfsp_ajax_new();
				die();
				break;
			case 'cfsp_new_add':
				if (!empty($_POST['cfsp_key']) || !empty($_POST['cfsp_description'])) {
					cfsp_add_new(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				die();
				break;
			case 'cfsp_save':
				if (!empty($_POST['cfsp_id'])) {
					cfsp_save_snippet_post(stripslashes($_POST['cfsp_id']), stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				else if (!empty($_POST['cfsp_key'])) {
					cfsp_save(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				die();
				break;
			case 'cfsp_edit':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_edit(stripslashes($_POST['cfsp_key']));
				}
				die();
				break;
			case 'cfsp_preview':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_preview(stripslashes($_POST['cfsp_key']));
				}
				die();
				break;
			case 'cfsp_delete':
				if (!empty($_POST['cfsp_key'])) {
					if (!empty($_POST['cfsp_delete_confirm']) && $_POST['cfsp_delete_confirm'] == 'yes') {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), true);
					}
					else {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), false);
					}
				}
				die();
				break;
		}
	}

	// Setup the class object
	if (!empty($_GET['page']) && strpos($_GET['page'], 'cf-snippets') !== false) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
	}
}
add_action('init', 'cfsp_request_handler');

function cfsp_ajax_new() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	include('views/ajax-new.php');
	die();
}

function cfsp_ajax_edit($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		include('views/ajax-edit-exists.php');
	}
	else {
		include('views/ajax-edit-error.php');
	}
	die();
}

function cfsp_ajax_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		include('views/ajax-preview-exists.php');
	}
	else {
		include('views/ajax-preview-error.php');
	}
	die();
}

function cfsp_ajax_delete($key, $confirm = false) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		// If the delete has been confirmed, remove the key and return
		if ($confirm) {
			$cf_snippet->remove($key);
		}
		else {
			include('views/ajax-delete-exists.php');
		}
	}
	else {
		include('views/ajax-delete-error.php');
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$cf_snippet->save($key, $content, $description);
}

function cfsp_save_snippet_post($id, $key = '', $description = '', $content = '') {
	if (empty($id)) { return false; }
	global $cf_snippet;

	$post_arr = array('ID' => $id, 'post_name' => $key, 'post_title' => $description, 'post_content' => $content);

	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	$cf_snippet->save_snippet_post($post_arr);
}

function cfsp_iframe_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	if (!empty($key) && $cf_snippet->exists($key)) {
		echo $cf_snippet->get($key);
	}
}

## Post Functionality


function cfsp_save_post($post_id, $post) {
	if ($post->post_status == 'inherit' || in_array($post->post_type, apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item', '_cf_snippet')))) { return; }
	if (!empty($_POST) && is_array($_POST) && !empty($_POST['cfsp']) && is_array($_POST['cfsp'])) {
		unset($_POST['cfsp']['###SECTION###']);

		$cf_snippet = new CF_Snippet();
		// Get the old list of keys so we make sure that we remove any deleted snippets
		$old_keys = $cf_snippet->get_keys_for_post(get_the_ID());

		foreach ($_POST['cfsp'] as $id => $item) {
			$name = $item['name'];
			$content = $item['content'];
			if (strpos($id, 'cfsp-'.$post_id.'-') === false) {
				$key = 'cfsp-'.$post_id.'-'.$id;
			}
			else {
				$key = $id;
			}


			// Make sure the key is a valid key
			$key = sanitize_title($key);

			$args = array(
				'post_parent' => $post_id,
			);

			if ($cf_snippet->check_key(stripslashes($key))) {
				$description = 'Post Snippet created for Post ID: '.$post_id.' with a unique ID of: '.$id;
				$cf_snippet->save($key, $content, $description, $args);
			}
			else {
				$description = 'Post Snippet created for Post ID: '.$post_id.' with a unique ID of: '.$id;
				$key = $cf_snippet->save($key, $content, $description, $args);
			}

			if (is_array($old_keys) && in_array($key, $old_keys)) {
				$flip = array_flip($old_keys);
				unset($old_keys[$flip[$key]]);
			}
		}

		if (is_array($old_keys) && !empty($old_keys)) {
			foreach ($old_keys as $key) {
				$cf_snippet->remove_from_parent($key);
			}
		}
	}
}
add_action('save_post', 'cfsp_save_post', 10, 2);

## JS/CSS Addition

## Auxillary Functionality

## Integration with the CF Links Plugin

function cfsp_cflk_integration() {
	if (function_exists('cflk_register_link')) {
		include('classes/cflk.snippets.class.php');
		cflk_register_link('cfsp_link');
	}
}
add_action('plugins_loaded', 'cfsp_cflk_integration', 99999);

