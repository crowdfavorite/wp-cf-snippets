<?php
/**
 * class CF_Snippet_Admin
 * @package cf-snippets
 */

class CF_Snippet_Admin extends CF_Snippet_Base {
	function __construct() {

		if (!is_admin()) {
			return;
		}

		$this->add_actions();
	}

	/**
	 * Register all admin-specific actions
	 */
	function add_actions() {

		// Add admin screen columsn and sorting
		add_filter('manage__cf_snippet_posts_columns', array($this, 'add_key_column'));
		add_action('manage__cf_snippet_posts_custom_column', array($this, 'key_column_content'), 10, 2);
		add_filter('manage_edit-_cf_snippet_sortable_columns',  array($this, 'key_column_sort'));
		add_action('pre_get_posts',  array($this, 'key_column_orderby'));
	
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_resources'));
		add_action('admin_init', array($this, 'post_admin_head'));

		if (function_exists('cfreadme_enqueue')) {
			add_action('admin_init', array($this, 'enqueue_cf_readme'));
		}
	}

	public function key_column_content($column_name, $id) {
		if( $column_name == 'key' ) {
			$post_slug = get_post($id)->post_name;
			echo $post_slug;
		}
	}

	public function add_key_column($defaults) {

		$defaults = array(
			'title' => __('Description'),
			'key' => __('Key'),
		);

		return $defaults;
	}

	public function key_column_sort($columns) {
		$columns['key'] = 'key';
		return $columns;
	}

	public function key_column_orderby( $query ) {
		if( ! is_admin() ) {
			return;
		}
		$orderby = $query->get('orderby');
		if( 'key' == $orderby ) {
			$query->set('orderby','name');
		}
	}

	/**
	 * Called during admin_enqueue_scripts hook processing
	 */
	public function enqueue_admin_resources($hook = '') {
		switch ($hook) {
		case 'post-new.php':
			// fallthrough
		case 'post.php':
			// Add the proper CSS/JS to the Post/Page/Custom Post Type Edit screen
			wp_enqueue_script('cfsp-post-js', CFSP_DIR_URL . 'js/post.js', array('jquery'), CFSP_VERSION);
			wp_localize_script('cfsp-post-js', 'snippetKey', wp_create_nonce('cf-snippets-key'));
			wp_enqueue_style('cfsp-post-css', CFSP_DIR_URL . 'css/post.css', array(), CFSP_VERSION, 'screen');
			break;
		case 'settings_page_cf-snippets':
			// Add the proper CSS/JS to the Settings screen
			// if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
			wp_enqueue_script('cfsp-admin-js', false, array('cfsp-admin-js-behavior', 'csfp-admin-js-domwindow', 'cfsp-admin-js-popup'), CFSP_VERSION);
			wp_enqueue_script('cfsp-admin-js-behavior', CFSP_DIR_URL . 'js/behavior.js', array('jquery'), CFSP_VERSION);
			wp_enqueue_script('cfsp-admin-js-domwindow', CFSP_DIR_URL . 'js/jquery.DOMWindow.js', array('cfsp-admin-js-behavior'), CFSP_VERSION);
			wp_enqueue_script('cfsp-admin-js-popup', CFSP_DIR_URL . 'js/popup.js', array('cfsp-admin-js-domwindow'), CFSP_VERSION);
			// } else { <concatenated resource> }

			wp_enqueue_style('cfsp-admin-css', CFSP_DIR_URL . 'css/content.css', array(), CFSP_VERSION, 'screen');
			break;
		}
	}

	/**
	 * Support CF Readme integration - enqueue to cfreadme
	 */
	function enqueue_cf_readme() {
		cfreadme_enqueue('cf-snippets', array($this, 'cf_readme'));
	}

	/**
	 * Support CF Readme integration - output readme file
	 */
	function cf_readme() {
		$file = CFSP_DIR.'README.txt';
		if (is_file($file) && is_readable($file)) {
			$markdown = file_get_contents($file);
			$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|', '![$1]('.CFSP_DIR.'/$2)', $markdown);
			return $markdown;
		}
		return null;
	}


	function post_edit_callback() {
		global $post;
		$cf_snippet = new CF_Snippet_Manager();
		$keys = $cf_snippet->get_keys();
		include(CFSP_DIR . 'views/post-edit.php');
	}

	function post_admin_head() {
		// Get the post types so we can add snippets to all needed
		$post_types = get_post_types();
		$post_type_excludes = apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item', '_cf_snippet'));

		if (is_array($post_types) && !empty($post_types)) {
			foreach ($post_types as $type) {
				if (!in_array($type, $post_type_excludes)) {
					add_meta_box('cfsp', __('CF Snippets', 'cfsp'), array($this, 'post_edit_callback'), $type, 'advanced', 'high');
				}
			}
		}
	}
}