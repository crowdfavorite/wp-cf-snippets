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
		if (1
			&& defined('CFSP_VERSION')
			&& version_compare(CFSP_VERSION, '2.2') >= 0
			&& get_option('cfsnip_snippets')
			) {
			$ver = '30';
		}
		return $ver;
	}

	public function prompt_for_upgrade_if_necessary() {
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
				? intval($_GET['ver'])
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
	function upgrade_to_30() {
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
}
