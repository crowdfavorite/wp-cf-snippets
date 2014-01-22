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
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_resources'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('right_now_content_table_end', array($this, 'rightnow_end'));
		add_action('cf_admin_rightnow', array($this, 'rightnow_cfadmin_end'));
		if (function_exists('cfreadme_enqueue')) {
			add_action('admin_init', array($this, 'enqueue_cf_readme'));
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

	public function admin_menu() {
		add_options_page(
			__('CF Snippets', 'cfsp'),
			__('CF Snippets', 'cfsp'),
			'manage_options',
			'cf-snippets',
			array($this, 'options_page')
		);
		if (defined('CF_ADMIN_VER')) {
			add_submenu_page(
				'cf-admin-menu',
				__('CF Snippets', 'cfsp'),
				__('CF Snippets', 'cfsp'),
				10,
				'cf-snippets',
				array($this, 'options_page')
			);
		}
	}

	function options_page() {
		global $cf_snippet;
		if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
			$cf_snippet = new CF_Snippet_Manager();
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
				if ($cf_snippet->exists($key)) {
					$table_content .= $cf_snippet->admin_display($key);
					$count++;
					$message_display = ' style="display:none;"';
				}
			}
		}
		include(CFSP_DIR . 'views/options.php');
	}

	/**
	 * Add some information to the "Right Now" section of the WP Admin Dashboard.  This will make it easier to
	 * get into the Snippets edit screen.
	 */
	function rightnow_end() {
		if (!defined('CF_ADMIN_VER')) {
			$cf_snippet = new CF_Snippet_Manager();
			$count = count($cf_snippet->get_keys());
			$link = admin_url('options-general.php?page=cf-snippets');
			include(CFSP_DIR . 'views/admin-rightnow.php');
		}
	}

	function rightnow_cfadmin_end() {
		$cf_snippet = new CF_Snippet_Manager();
		$count = count($cf_snippet->get_keys());
		$link = admin_url('options-general.php?page=cf-snippets');
		include(CFSP_DIR . 'views/admin-rightnow.php');
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

}
