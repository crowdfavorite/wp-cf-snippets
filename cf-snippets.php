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

	$file = CFSP_DIR . 'includes/class-' . str_replace('_', '-', strtolower($class)) . '.php';
	include $file;
}

if (function_exists('spl_autoload_register')) {
	spl_autoload_register('cfsp_autoload');
}
else {
	CFSP_DIR . include 'includes/class-cf-snippet-base.php';
	CFSP_DIR . include 'includes/class-cf-snippet-core.php';
	CFSP_DIR . include 'includes/class-cf-snippet-manager.php';
	if (is_admin()) {
		CFSP_DIR . include 'includes/class-cf-snippet-upgrader.php';
	}
}

// Load the scaffolding up here
$cf_snippet_core = new CF_Snippet_Core();
$cf_snippet = new CF_Snippet_Manager();
$cf_snippet_ajax = new CF_Snippet_Ajax();

// Load the currently non-class support code and template API
include CFSP_DIR . 'includes/template.php';
include CFSP_DIR . 'includes/shortcode.php';
include CFSP_DIR . 'includes/widget.php';


## Post Functionality

function cfsp_save_post($post_id, $post) {	
	
	if ($post->post_type == '_cf_snippet' && isset($_POST['cf_snippet_post_name']) && $_POST['cf_snippet_post_name'] != $post->post_name) {
		remove_action('save_post', 'cfsp_save_post', 10, 2);
		wp_update_post(array(
			'ID' => $post_id,
			'post_name' => $_REQUEST['cf_snippet_post_name'],
		));
		add_action('save_post', 'cfsp_save_post', 10, 2);
	}
	
	if ($post->post_status == 'inherit' || in_array($post->post_type, apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item', '_cf_snippet')))) { return; }
	if (!empty($_POST) && is_array($_POST) && !empty($_POST['cfsp']) && is_array($_POST['cfsp'])) {
		unset($_POST['cfsp']['###SECTION###']);

		$cf_snippet = new CF_Snippet_Manager();
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

