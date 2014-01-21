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
			CF_Snippet_Upgrader::i()->add_actions();
		}
	}

	function add_actions() {
		add_action('init', array($this, 'set_defines'), 1);
		add_action('init', array($this, 'register_post_types'), 1);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_resources'));
	}

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

	public function enqueue_admin_resources($hook = '') {
		switch ($hook) {
			case 'post-new.php':
				// fallthrough
			case 'post.php':
				// Add the proper CSS/JS to the Post/Page/Custom Post Type Edit screen
				wp_enqueue_script('cfsp-post-js', CFSP_DIR_URL . 'js/post.js', array('jquery'), CFSP_VERSION);
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
}
