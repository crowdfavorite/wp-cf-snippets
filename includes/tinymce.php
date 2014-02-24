<?php
## TinyMCE Functionality

add_action('admin_post_cfsp_dialog', 'cfsp_dialog');

function cfsp_dialog() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet_Manager)) {
		$cf_snippet = new CF_Snippet_Manager();
	}
	$list = $cf_snippet->list_display(true);
	include(CFSP_DIR . 'views/tinymce-dialog.php');
	die();
}

function cfsp_addtinymce() {
	// Don't bother doing this stuff if the current user lacks permissions
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) { return; }

	// Add only in Rich Editor mode
	if (get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "add_cfsnip_tinymce_plugin");
		add_filter('mce_buttons', 'register_cfsnip_button');
	}
}
add_action('init', 'cfsp_addtinymce');

function register_cfsnip_button($buttons) {
	array_push($buttons, '|', "cfsnip_Btn");
	return $buttons;
}

function add_cfsnip_tinymce_plugin($plugin_array) {
	$plugin_array['cfsnippets'] = CFSP_DIR_URL.'js/editor_plugin.js';
	return $plugin_array;
}

