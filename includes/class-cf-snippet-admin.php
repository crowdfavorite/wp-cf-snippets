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

}