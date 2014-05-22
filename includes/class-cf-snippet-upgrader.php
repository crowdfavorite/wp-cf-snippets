<?php

class CF_Snippet_Upgrader {
	static $i = null;

	public static function i() {
		if (is_null(self::$i)) {
			self::$i = new CF_Snippet_Upgrader;
		}
		return self::$i;
	}

	public function add_actions() {
		add_action('admin_init', array($this, 'admin_request_handler'));
		add_action('admin_notices', array($this, 'prompt_for_upgrade_if_necessary'));
	}

	protected function needs_which_upgrade() {
		$ver = false;
		$ver_option = get_option('cfsnip_version');

		// No option stored and no snippets, then it's a new install with nothing to update.
 		if (!$ver_option && !get_option('cfsnip_snippets')) {
			// Check to see if posts exist. It's possible these will need migration.
			global $wpdb;
			$has_posts = (bool) ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = '_cf_snippet'")) > 0;
			if (!$has_posts) {
				// Set to latest verison.
				$this->set_version(CFSP_VERSION);
				return false;
			}
		}

		// Changing from db option to post type
		if (1
			&& defined('CFSP_VERSION')
			&& version_compare(CFSP_VERSION, '2.2', '>=')
			&& get_option('cfsnip_snippets') // AND still have old snippets
			) {
			$ver = '3.0';
		}
		// Simply setting an option for the plugin version
		else if (!$ver_option) {
			$ver = '3.1';
		}
		else if (version_compare($ver_option, '3.2', '<')) {
			$ver = '3.2';
		}

		/* Future versions can compare a DB option (cfsnip_version) that should
		 * be set in the upgrade routine.  This was added in 3.1, so prior
		 * versions will need to set the option */
		return $ver;
	}

	public function prompt_for_upgrade_if_necessary() {
		// Only prompt for people who can do something about it.
		if (!current_user_can('manage_options')) {
			return;
		}

		if ($this->needs_which_upgrade() == false) {
			return;
		}

		$upgrade_url = wp_nonce_url(
			add_query_arg(array('cf_action' => 'cfsp_upgrade'), admin_url()),
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

			if (!current_user_can('manage_options')) {
				wp_die('Error: cfsp_99'); // Not enough permissions
			}

			if (!check_admin_referer('cfsp_upgrade')) {
				wp_die('Error: cfsp_100'); // Didn't pass nonce
			}


			/* Ability to perform multiple upgrades at once, so user isn't concerned
			 * by having to click a seemingly same link multiple times if there's more
			 * than one upgrade necessary */
			while (($upgrade_ver = $this->needs_which_upgrade())) {
				$function_name = 'upgrade_to_'.preg_replace('|\D|', '', $upgrade_ver);
				if (!method_exists($this, $function_name)) {
					wp_die('Error cfsp_101'); // Invalid Version Number
				}
				$this->{$function_name}();
			}

			wp_safe_redirect(wp_get_referer());
			exit;
		}
	}

	/**
	 * Converts storage from option to post type
	 */
	protected function upgrade_to_30() {
		global $cf_snippet;
		if (empty($cf_snippet)) {
			$cf_snippet = new CF_Snippet_Manager();
		}

		$old_snippets = get_option('cfsnip_snippets');
		if (is_array($old_snippets) && !empty($old_snippets)) {
			foreach ($old_snippets as $key => $data) {
				$cf_snippet = new CF_Snippet_Manager();
				// Make sure the key is a valid key
				$key = sanitize_title($key);
				$args = array();

				// Check to see if this Snippet is related to a post
				if (!empty($data['post_id'])) {
					$args['post_parent'] = $data['post_id'];
				}

				$cf_snippet->save($key, $data['content'], $data['description'], $args);
			}
		}
		delete_option('cfsnip_snippets');
	}

	/**
	 * Just set the DB option for the current version of the plugin
	 */
	protected function upgrade_to_31() {
		$this->set_version('3.1');
	}

	protected function upgrade_to_32() {
		// Convert all snippets to use post_content instead of meta value.
		// We're using a paginated loop to meet requirements from WordPress.com VIP
		
		$loop_complete = false;
		$paged = 1;
		
		while (!$loop_complete) {
			$query = new WP_Query(array(
				'post_type' => '_cf_snippet',
				'post_status' => 'any',
				'posts_per_page' => 100,
				'paged' => $paged,
			));
			
			if ($query->have_posts()) {
				foreach ($query->posts as $post_obj) {
					$old_content = get_post_meta($post_obj->ID, '_cfsp_content', true);
					if (!empty($old_content) && empty($post_obj->post_content)) {
						$post_update = array(
							'ID' => $post_obj->ID,
							'post_content' => $old_content,
						);
						
						$result = wp_update_post($post_update);
						
						if (!is_wp_error($result)) {
							delete_post_meta($post_obj->ID, '_cfsp_content');
						}
					}
				}
			}
			else {
				$loop_complete = true;
			}
			
			++$paged;
		}
		
		$this->set_version('3.2');
	}

	protected function set_version($ver_string) {
		update_option('cfsnip_version', $ver_string);
	}
}
