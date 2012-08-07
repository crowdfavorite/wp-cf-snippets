<?php

class Snippet_Upgrader {
	static $i = null;

	function i() {
		if (is_null(self::$i)) {
			self::$i = new Snippet_Upgrader;
		}
		return self::$i;
	}

	function add_actions() {
		add_action('admin_init', array($this, 'admin_request_handler'));
		add_action('admin_notices', array($this, 'prompt_for_upgrade_if_necessary'));
	}

	protected function needs_which_upgrade() {
		$ver = false;

		// Changing from db option to post type
		if (1
			&& defined('CFSP_VERSION')
			&& version_compare(CFSP_VERSION, '2.2') >= 0
			&& get_option('cfsnip_snippets') // AND still have old snippets
			) {
			$ver = '3.0';
		}
		// Simply setting an option for the plugin version
		else if (!get_option('cfsnip_version')) {
			$ver = '3.0.1';
		}

		/* Future versions can compare a DB option (cfsnip_version) that should
		 * be set in the upgrade routine.  This was added in 3.0.1, so prior
		 * versions will need to set the option */
		return $ver;
	}

	public function prompt_for_upgrade_if_necessary() {
		// Only prompt for people who can do something about it.
		if (!current_user_can('manage_options')) {
			return;
		}

		$upgrade_ver = $this->needs_which_upgrade();
		if ($upgrade_ver == false) {
			return;
		}

		$upgrade_url = wp_nonce_url(
			add_query_arg(array('cf_action' => 'cfsp_upgrade', 'ver' => $upgrade_ver), admin_url()),
			'cfsp_upgrade'
		);
		?>
		<div class="error">
			<p>
				<?php printf(
					__('The CF Snippets plugin requires an upgrade.  Please back up your database and then <a href="%s">click here</a> to perform this upgrade.', 'cfsp'),
					esc_url($upgrade_url)
				); ?>
			</p>
		</div><!-- /error -->
		<?php
	}

	public function admin_request_handler() {
		if (isset($_GET['cf_action']) && $_GET['cf_action'] == 'cfsp_upgrade') {
			$upgrade_ver = isset($_GET['ver'])
				? preg_replace('|\D|', '', $_GET['ver'])
				: 0;

			if (!current_user_can('manage_options')) {
				wp_die('Error: cfsp_99'); // Not enough permissions
			}

			if (empty($upgrade_ver) || !check_admin_referer('cfsp_upgrade')) {
				wp_die('Error: cfsp_100'); // Didn't pass nonce or upgrade version check
			}

			$function_name = 'upgrade_to_'.$upgrade_ver;
			if (!method_exists($this, $function_name)) {
				wp_die('Error cfsp_101'); // Invalid Version Number
			}

			$this->{$function_name}();

			wp_safe_redirect(wp_get_referer());
			exit;
		}
	}

	/**
	 * Converts storage from option to post type
	 */
	protected function upgrade_to_30() {
		if (!class_exists('CF_Snippet')) {
			require_once 'snippets.class.php';
		}

		$cf_snippet = new CF_Snippet();
		$old_snippets = get_option('cfsnip_snippets');
		if (is_array($old_snippets) && !empty($old_snippets)) {
			foreach ($old_snippets as $key => $data) {
				// Make sure the key is a valid key
				$key = sanitize_title($key);
				$args = array();

				// Check to see if this Snippet is related to a post
				if (!empty($data['post_id'])) {
					$args['post_parent'] = $data['post_id'];
				}

				$cf_snippet->add($key, $data['content'], $data['description'], $args);
			}
		}
		delete_option('cfsnip_snippets');
	}

	/**
	 * Just set the DB option for the current version of the plugin
	 */
	protected function upgrade_to_301() {
		$this->set_version();
	}

	protected function set_version() {
		update_option('cfsnip_version', CFSP_VERSION);
	}
}
