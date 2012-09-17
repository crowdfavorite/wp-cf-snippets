<?php
/*
Plugin Name: CF Snippets
Plugin URI: http://crowdfavorite.com
Description: Provides admin level users the ability to define html snippets for use in templates, content, or widgets.
Version: 3.1.2
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

## Constants

define('CFSP_VERSION', '3.1');
define('CFSP_DIR', plugin_dir_path(__FILE__));
//plugin_dir_url seems to be broken for including in theme files
if (file_exists(trailingslashit(get_template_directory()).'plugins/'.basename(dirname(__FILE__)))) {
	define('CFSP_DIR_URL', trailingslashit(trailingslashit(get_bloginfo('template_url')).'plugins/'.basename(dirname(__FILE__))));
}
else {
	define('CFSP_DIR_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));
}
define('CFSP_SHOW_POST_COUNT', 10);

// Load the text domain
load_plugin_textdomain('cfsp');


## Includes

include('classes/snippets.class.php');
if (is_admin()) {
	include 'classes/snippet-upgrader.class.php';
	Snippet_Upgrader::i()->add_actions();
}

## Admin Functionality

function cfsp_register_post_type() {
	$snippet = new CF_Snippet();
	$snippet->register_post_type();
}
add_action('init', 'cfsp_register_post_type', 1);

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
				cfsnip_dialog();
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
				if (!empty($_POST['cfsp_key'])) {
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
			case 'cfsp_post_items_paged':
				if (!empty($_POST['cfsp_page'])) {
					cfsp_ajax_post_items_paged(stripslashes($_POST['cfsp_page']));
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

function cfsp_resources() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfsp_admin_css':
				cfsp_admin_css();
				die();
				break;
			case 'cfsp_admin_js':
				cfsp_admin_js();
				die();
				break;
			case 'cfsp_post_css':
				cfsp_post_css();
				die();
				break;
			case 'cfsp_post_js':
				cfsp_post_js();
				die();
				break;
		}
	}
}
add_action('init', 'cfsp_resources', 1);

function cfsp_admin_css() {
	header('Content-type: text/css');
	do_action('cfsp-admin-css');
	echo file_get_contents(CFSP_DIR.'css/content.css');
	die();
}

function cfsp_admin_js() {
	header('Content-type: text/javascript');
	do_action('cfsp-admin-js');
	echo file_get_contents(CFSP_DIR.'js/behavior.js');
	echo file_get_contents(CFSP_DIR.'js/jquery.DOMWindow.js');
	echo file_get_contents(CFSP_DIR.'js/json2.js');
	echo file_get_contents(CFSP_DIR.'js/popup.js');
	die();
}

function cfsp_post_css() {
	header('Content-type: text/css');
	do_action('cfsp-post-css');
	echo file_get_contents(CFSP_DIR.'css/post.css');
	die();
}

function cfsp_post_js() {
	header('Content-type: text/javascript');
	do_action('cfsp-post-js');
	echo file_get_contents(CFSP_DIR.'js/post.js');
	die();
}

function cfsp_admin_menu() {
	add_options_page(
		__('CF Snippets', 'cfsp'),
		__('CF Snippets', 'cfsp'),
		'manage_options',
		'cf-snippets',
		'cfsp_options'
	);
	if (defined('CF_ADMIN_VER')) {
		add_submenu_page(
			'cf-admin-menu',
			__('CF Snippets', 'cfsp'),
			__('CF Snippets', 'cfsp'),
			10,
			'cf-snippets',
			'cfsp_options'
		);
	}
}
add_action('admin_menu', 'cfsp_admin_menu');

function cfsp_options() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	$table_display = '';
	$message_display = '';
	$post_table_display = '';
	$post_message_display = '';
	$count = 0;
	$post_count = 0;
	$show_post_count = CFSP_SHOW_POST_COUNT;
	$total_post_count = 0;
	$total_post_page_count = 0;

	$table_content = '';
	$post_table_content = '';

	$keys = $cf_snippet->get_keys();
	if (is_array($keys) && !empty($keys)) {
		foreach ($keys as $key) {
			if (!$cf_snippet->has_parent($key)) {
				$table_content .= $cf_snippet->admin_display($key);
				$count++;
				$message_display = ' style="display:none;"';
			}
		}
	}
	else {
		$table_display = ' style="display:none;"';
	}

	$post_keys = $cf_snippet->get_all_post_keys(CFSP_SHOW_POST_COUNT);

	if (is_array($post_keys) && !empty($post_keys)) {
		foreach ($post_keys as $key) {
			$post_table_content .= $cf_snippet->admin_display($key);
			$post_count++;
			$post_message_display = ' style="display:none;"';
			$total_post_count++;
		}
	}
	else {
		$post_table_display = ' style="display:none;"';
	}

	$total_post_page_count = ceil($cf_snippet->get_post_key_count()/CFSP_SHOW_POST_COUNT);
	include('views/options.php');
}

function cfsp_ajax_post_items_paged($page) {
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

		include('views/ajax-post-items-paged.php');
	}
}

function cfsp_get_post_snippet_keys() {
	$snippet_keys = array();

	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();

		$keys = $cf_snippet->get_keys();
		if (is_array($keys) && !empty($keys)) {
			foreach ($keys as $key) {
				$meta = $cf_snippet->get_meta($key);
				if ($meta['post_id']) {
					$snippet_keys[] = $key;
				}
			}
		}
	}
	return $snippet_keys;
}

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

function cfsp_post_admin_head() {
	// Get the post types so we can add snippets to all needed
	$post_types = get_post_types();
	$post_type_excludes = apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item', '_cf_snippet'));

	if (is_array($post_types) && !empty($post_types)) {
		foreach ($post_types as $type) {
			if (!in_array($type, $post_type_excludes)) {
				add_meta_box('cfsp', __('CF Snippets', 'cfsp'), 'cfsp_post_edit', $type, 'advanced', 'high');
			}
		}
	}
}
add_action('admin_init', 'cfsp_post_admin_head');

function cfsp_post_edit() {
	global $post;
	$cf_snippet = new CF_Snippet();
	$keys = $cf_snippet->get_keys_for_post(get_the_ID());
	include('views/post-edit.php');
}

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

function cfsp_admin_enqueue_scripts($hook = '') {
	switch ($hook) {
		case 'post-new.php':
		case 'post.php':
			// Add the proper CSS/JS to the Post/Page/Custom Post Type Edit screen
			wp_enqueue_script('cfsp-post-js', admin_url('?cf_action=cfsp_post_js'), array('jquery'), CFSP_VERSION);
			wp_enqueue_style('cfsp-post-css', admin_url('?cf_action=cfsp_post_css'), array(), CFSP_VERSION, 'screen');
			break;
		case 'settings_page_cf-snippets':
			// Add the proper CSS/JS to the Settings screen
			wp_enqueue_script('cfsp-admin-js', admin_url('?cf_action=cfsp_admin_js'), array('jquery'), CFSP_VERSION);
			wp_enqueue_style('cfsp-admin-css', admin_url('?cf_action=cfsp_admin_css'), array(), CFSP_VERSION, 'screen');
			break;
	}
}
add_action('admin_enqueue_scripts', 'cfsp_admin_enqueue_scripts');


## Display Functionality

function cfsp_get_snippet_info($key, $default = false, $create = true, $args = array()) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create, $args);
}

function cfsp_content($key, $default = false, $create = true, $args = array()) {
	echo cfsp_get_content($key, $default, $create, $args);
}

function cfsp_get_content($key, $default = false, $create = true, $args = array()) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create, $args);
}

function cfsp_shortcode($attrs, $content=null) {
	if (is_array($attrs)) {
		$key = '';
		if (!empty($attrs['name'])) {
			$key = $attrs['name'];
		}
		else if (!empty($attrs['key'])) {
			$key = $attrs['key'];
		}

		if (empty($key)) { return ''; }
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		return $cf_snippet->get($key, false, false);
	}
	return '';
}
add_shortcode('cfsp', 'cfsp_shortcode');

## Deprecated Display Functionality

function cfsnip_get_snippets() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get_all();
}

function cfsnip_snippet($key, $default = false, $create = true) {
	echo cfsnip_get_snippet_content($key, $default, $create);
}

function cfsnip_snippet_content($key, $default = false, $create = true) {
	echo cfsnip_get_snippet_content($key, $default, $create);
}

function cfsnip_get_snippet($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsnip_get_snippet_content($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsnip_filter_content($content, $key) {
	return str_replace(array('{cfsnip_template_url}', '{cfsp_template_url}'), get_stylesheet_directory_uri(), $content);
}
add_filter('cfsp-get-content', 'cfsnip_filter_content', 10, 2);

function cfsnip_snippet_exists($key) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->exists($key);
}

function cfsnip_handle_shortcode($attrs, $content=null) {
	if (is_array($attrs) && !empty($attrs['name'])) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		return $cf_snippet->get($attrs['name'], false, false);
	}
	return '';
}
add_shortcode('cfsnip', 'cfsnip_handle_shortcode');

## Widget Functionality

class cfsnip_Widget extends WP_Widget {
	function cfsnip_Widget() {
		$widget_ops = array('classname' => 'cfsnip-widget', 'description' => 'Widget for displaying selected CF Snippets');
		$this->WP_Widget('cfsnip-widget', 'CF Snippets', $widget_ops);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		// Get the snippet content
		$content = $cf_snippet->get($instance['list_key']);
		// If we don't have anything to display, no need to proceed
		if (empty($content)) { return; }
		$title = esc_html($instance['title']);

		echo $before_widget;
		if (!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		echo $content;
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_key'] = strip_tags($new_instance['list_key']);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'list_key' => ''));

		$title = esc_attr($instance['title']);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		$select = $cf_snippet->select_display($instance['list_key']);

		if (!empty($select)) {
			include('views/widget-edit.php');
		}
		else {
			include('views/widget-empty.php');
		}
	}
}
add_action('widgets_init', create_function('', "register_widget('cfsnip_Widget');"));

## TinyMCE Functionality

function cfsnip_dialog() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	$list = $cf_snippet->list_display(true);
	include('views/tinymce-dialog.php');
}

function cfsnip_addtinymce() {
	// Don't bother doing this stuff if the current user lacks permissions
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) { return; }

	// Add only in Rich Editor mode
	if (get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "add_cfsnip_tinymce_plugin");
		add_filter('mce_buttons', 'register_cfsnip_button');
	}
}
add_action('init', 'cfsnip_addtinymce');

function register_cfsnip_button($buttons) {
	array_push($buttons, '|', "cfsnip_Btn");
	return $buttons;
}

function add_cfsnip_tinymce_plugin($plugin_array) {
	$plugin_array['cfsnippets'] = CFSP_DIR_URL.'js/editor_plugin.js';
	return $plugin_array;
}

## CF Readme Addition

if (function_exists('cfreadme_enqueue')) {
	function cfsp_add_readme() {
		cfreadme_enqueue('cf-snippets', 'cfsp_readme');
	}
	add_action('admin_init', 'cfsp_add_readme');

	function cfsp_readme() {
		$file = CFSP_DIR.'README.txt';
		if (is_file($file) && is_readable($file)) {
			$markdown = file_get_contents($file);
			$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|', '![$1]('.CFSP_DIR.'/$2)', $markdown);
			return $markdown;
		}
		return null;
	}
}

## WordPress Admin Help Addition

function cfsp_admin_help() {
	global $current_screen, $wp_version;

	// Let other parts of the plugin filter in content for the help
	$cfsp_help = apply_filters('cfsp-help-tab', array());

	if (is_array($cfsp_help) && !empty($cfsp_help) && is_admin()) {
		// Check to WordPress 3.3 support.  This is a much improved Help interface and makes it much easier to add Help content to.
		if (version_compare(floatval($wp_version), '3.3') >= 0 && is_admin() && $current_screen->base == 'settings_page_cf-snippets') {
			foreach ($cfsp_help as $key => $data) {
				if (!is_array($data) || empty($data['title']) || empty($data['description'])) { continue; }

				$current_screen->add_help_tab(array(
					'id' => 'cfsp-help-tab_'.sanitize_title($key),
					'title' => wp_kses($data['title'], ''),
					'content' => '<h2>CF Snippets Help</h2>'.$data['description']
				));
			}
		}
		else if (is_admin() && $current_screen->base == 'settings_page_cf-snippets') {
			$context_help = '';

			foreach ($cfsp_help as $key => $data) {
				if (!is_array($data) || empty($data['title']) || empty($data['description'])) { continue; }

				$context_help .= '
				<div class="cfsp-help-tab_'.sanitize_title($key).'">
					<h3>'.wp_kses($data['title'], '').'</h3>
					'.$data['description'].'
				</div>
				';
			}

			if (!empty($context_help)) {
			    add_contextual_help('settings_page_cf-snippets', $context_help);
			}
		}
	}
}
add_action('admin_head', 'cfsp_admin_help');

function cfsp_admin_help_description($help = array()) {
	// If the "Description" tab hasn't been filled, add it
	if (empty($help['description'])) {
		$description = '
<p>The <b>CF Snippets</b> plugin gives Admin users the ability to create chunks of content (including HTML content) to be inserted into posts, widgets and front end display with an easy to use Admin interface.</p>
<p>This functionality gives the Admin users easy ability to edit the chunks of code without editing PHP/HTML files.  The plugin provides PHP functions for display of Snippets, as well as WordPress shortcodes.</p>
<p>On the post edit screen, the plugin provides a TinyMCE button for easy insertion of Snippets shortcodes.</p>
<p><small><b>** NOTE: Plugin requires WordPress 3.1 **</b></small></p>
		';
		$help['description'] = array(
			'title' => __('Description', 'cfsp'),
			'description' => $description
		);
	}
	return $help;
}
add_filter('cfsp-help-tab', 'cfsp_admin_help_description');

function cfsp_admin_help_theme($help = array()) {
	// If the "Theme Inclusion" tab hasn't been filled, add it
	if (empty($help['theme'])) {
		$description = "
<p><b>CF Snippets</b> content can easily be added to a WordPress theme.</p>
<p>To add content from a snippet, simply use the \"template tag\" for display. The template tag for a particular snippet can be found by clicking the \"Template Tag & Shortcode\" link below the snippet description.</p>
<p>The template tag looks like <code>&lt;?php if (function_exists('cfsp_content')) { cfsp_content('new-snippet'); } ?&gt;</code></p>
<p>Simply copy that code from the example display, and paste it into the PHP file where it is needed.  The <b>CF Snippets</b> plugin will automatically display the content of the snippet entered through the admin.</p>
		";
		$help['theme'] = array(
			'title' => __('Theme Inclusion', 'cfsp'),
			'description' => $description
		);
	}
	return $help;
}
add_filter('cfsp-help-tab', 'cfsp_admin_help_theme', 11);

function cfsp_admin_help_shortcodes($help = array()) {
	// If the "Shortcodes" tab hasn't been filled, add it
	if (empty($help['shortcodes'])) {
		$description = "
<p>The <b>CF Snippets</b> plugin also provides WordPress \"Shortcodes\" for easy display of the Snippet data.  The shortcode will display data based on snippet key.</p>
<p>To add content from a snippet, simply use the \"Shortcode\" for display. The Shortcode for a particular snippet can be found by clicking the \"Template Tag & Shortcode\" link below the snippet description.</p>
<p>The Shortcode looks like <code>[cfsp name=\"new-snippet\"]</code></p>
<p>Simply copy that code from the example display, and paste it into the WordPress content area where it is needed.  The <b>CF Snippets</b> plugin will automatically display the content of the snippet entered through the admin.</p>
		";
		$help['shortcodes'] = array(
			'title' => __('Shortcode', 'cfsp'),
			'description' => $description
		);
	}
	return $help;
}
add_filter('cfsp-help-tab', 'cfsp_admin_help_shortcodes', 12);

function cfsp_admin_help_shortcode_support($help = array()) {
	// If the "Shortcode Support" tab hasn't been filled, add it
	if (empty($help['shortcode-support'])) {
		$description = "
<p>The <b>CF Snippets</b> plugin also provides the ability to process WordPress \"Shortcodes\" within the content of a snippet.</p>
<p>To have a Snippet display the content of a shortcode, simply add the shortcode to the content of a snippet.</p>
<p>The <b>CF Snippets</b> plugin will automatically process the content of any shortcode saved inside of a snippet.</p>
		";
		$help['shortcode-support'] = array(
			'title' => __('Shortcode Support', 'cfsp'),
			'description' => $description
		);
	}
	return $help;
}
add_filter('cfsp-help-tab', 'cfsp_admin_help_shortcode_support', 13);

function cfsp_admin_help_moreinfo($help = array()) {
	// If the "More Info" tab hasn't been filled, add it
	if (empty($help['moreinfo'])) {
		$description = '
<p>For more information on using the <b>CF Snippets</b>, view the README.txt file in the plugin folder.</p>
		';
		$help['moreinfo'] = array(
			'title' => __('More Info', 'cfsp'),
			'description' => $description
		);
	}
	return $help;
}
add_filter('cfsp-help-tab', 'cfsp_admin_help_moreinfo', 999999);

## Auxillary Functionality

/**
 * Add some information to the "Right Now" section of the WP Admin Dashboard.  This will make it easier to
 * get into the Snippets edit screen.
 */
function cfsp_rightnow_end() {
	if (!defined('CF_ADMIN_VER')) {
		$cf_snippet = new CF_Snippet();
		$count = count($cf_snippet->get_keys());
		$link = admin_url('options-general.php?page=cf-snippets');
		include('views/admin-rightnow.php');
	}
}
add_action('right_now_content_table_end', 'cfsp_rightnow_end');

function cfsp_rightnow_cfadmin_end() {
	$cf_snippet = new CF_Snippet();
	$count = count($cf_snippet->get_keys());
	$link = admin_url('options-general.php?page=cf-snippets');
	include('views/admin-rightnow.php');
}
add_action('cf_admin_rightnow', 'cfsp_rightnow_cfadmin_end');

/**
 * JSON ENCODE and DECODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode & json_decode
 * Uses the Pear Class Services_JSON - http://pear.php.net/package/Services_JSON
 */
function cfsp_include_json() {
	global $wp_version;
	if (!function_exists('json_encode') && !class_exists('Services_JSON') && version_compare($wp_version, '3.0', '<')) {
		require_once('classes/external/JSON.php');
	}
}

/**
 * cfsp_json_encode
 *
 * @param array/object $json
 * @return string json
 */
function cfsp_json_encode($data) {
	if (function_exists('json_encode')) {
		return json_encode($data);
	}
	else {
		cfsp_include_json();
		global $cfsp_json_object;
		if (!($cfsp_json_object instanceof Services_JSON)) {
			$cfsp_json_object = new Services_JSON();
		}
		return $cfsp_json_object->encode($data);
	}
}

/**
 * cfsp_json_decode
 *
 * @param string $json
 * @param bool $array - toggle true to return array, false to return object
 * @return array/object
 */
function cfsp_json_decode($json,$array) {
	if (function_exists('json_decode')) {
		return json_decode($json,$array);
	}
	else {
		cfsp_include_json();
		global $cfsp_json_object;
		if (!($cfsp_json_object instanceof Services_JSON)) {
			$cfsp_json_object = new Services_JSON();
		}
		$cfsp_json_object->use = $array ? SERVICES_JSON_LOOSE_TYPE : 0;
		return $cfsp_json_object->decode($json);
	}
}

## Integration with the CF Links Plugin

function cfsp_cflk_integration() {
	if (function_exists('cflk_register_link')) {
		include('classes/cflk.snippets.class.php');
		cflk_register_link('cfsp_link');
	}
}
add_action('plugins_loaded', 'cfsp_cflk_integration', 99999);
