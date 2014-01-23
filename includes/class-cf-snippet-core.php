<?php
/**
 * class CF_Snippet_Core
 * Handles code loading and startup tasks for cf-snippets
 *
 * @package cf-snippets
 */

class CF_Snippet_Core extends CF_Snippet_Base {

	public function __construct() {
		$this->add_actions();

		if (is_admin()) {
			new CF_Snippet_Admin();
			new CF_Snippet_Admin_Help();
			CF_Snippet_Upgrader::i()->add_actions();
		}
	}

	/**
	 * All actions needed for non-admin-specific operation
	 */
	function add_actions() {
		add_action('init', array($this, 'set_defines'), 1);
		add_action('init', array($this, 'register_post_types'), 1);

		add_filter('cfsp-get-content', array($this, 'filter_snippet_output'), 10, 2);
	}

	/**
	 * Define the CFSP_DIR_URL url to this plugin's top-level directory
	 *
	 * Since cf-snippets can be used either as a standalone plugin or baked into
	 * a theme, the plugins_url call by itself is not a reliable method of
	 * determining URL structure.
	 */
	public function set_defines() {
		// Per http://codex.wordpress.org/Function_Reference/plugins_url this is called in init, not at plugin load
		if (!defined('CFSP_DIR_URL')) {
			if (file_exists(trailingslashit(get_template_directory()) . 'plugins/' . basename(CFSP_DIR))) {
				define('CFSP_DIR_URL', trailingslashit(trailingslashit(get_template_directory_uri()) . 'plugins/' . basename(CFSP_DIR)));
			}
			else {
				define('CFSP_DIR_URL', trailingslashit(plugins_url(basename(CFSP_DIR))));
			}
		}
	}

	/**
	 * This function registers a custom post type where we store the snippets
	 * 
	 * @return void
	 */
	public function register_post_types() {
		$args = array(
			'public' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'can_export' => false,
		  );

		register_post_type($this->post_type, $args);
	}

	/**
	 * Replace cf-snippets-specific tags in snippet content
	 *
	 * Currently only {cfsp_template_url}
	 */
	function filter_snippet_output($content, $key) {
		return str_replace('{cfsp_template_url}', get_stylesheet_directory_uri(), $content);
	}

}
