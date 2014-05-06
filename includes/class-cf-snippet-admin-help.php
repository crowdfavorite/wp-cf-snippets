<?php
/**
 * class CF_Snippet_Admin_Help
 * @package cf-snippets
 */

class CF_Snippet_Admin_Help extends CF_Snippet_Base {
	public function __construct() {
		if (!is_admin()) {
			return;
		}
		$this->add_actions();
	}

	public function add_actions() {
		add_action('admin_head', array($this, 'cfsp_admin_help'));
		add_filter('cfsp-help-tab', array($this, 'cfsp_admin_help_description'));
		add_filter('cfsp-help-tab', array($this, 'cfsp_admin_help_theme'), 11);
		add_filter('cfsp-help-tab', array($this, 'cfsp_admin_help_shortcodes'), 12);
		add_filter('cfsp-help-tab', array($this, 'cfsp_admin_help_shortcode_support'), 13);
		add_filter('cfsp-help-tab', array($this, 'cfsp_admin_help_moreinfo'), 999999);
	}

	public function cfsp_admin_help() {

		$current_screen = get_current_screen();

		// Return early if we're not on the book post type.
		if ( '_cf_snippet' != $current_screen->post_type ) {
	    	return;
	    }

		// Let other parts of the plugin filter in content for the help
		$cfsp_help = apply_filters('cfsp-help-tab', array());

		if (is_array($cfsp_help) && !empty($cfsp_help) && is_admin()) {
			// Check to WordPress 3.3 support.  This is a much improved Help interface and makes it much easier to add Help content to.
			foreach ($cfsp_help as $key => $data) {
				if (!is_array($data) || empty($data['title']) || empty($data['description'])) { continue; }

				$current_screen->add_help_tab(array(
					'id' => 'cfsp-help-tab_'.sanitize_title($key),
					'title' => wp_kses($data['title'], ''),
					'content' => '<h2>CF Snippets Help</h2>'.$data['description']
				));
			}
		}
	}

	public function cfsp_admin_help_description($help = array()) {
		// If the "Description" tab hasn't been filled, add it
		if (empty($help['description'])) {
			$description = '
				<p>The <b>CF Snippets</b> plugin gives Admin users the ability to create chunks of content (including HTML content) to be inserted into posts, widgets and front end display with an easy to use Admin interface.</p>
				<p>This functionality gives the Admin users easy ability to edit the chunks of code without editing PHP/HTML files.  The plugin provides PHP functions for display of Snippets, as well as WordPress shortcodes.</p>
				<p>On the post edit screen, the plugin provides a TinyMCE button for easy insertion of Snippets shortcodes.</p>
				<p><small><b>** NOTE: Plugin versions 4.0 and up require WordPress 3.8 **</b></small></p>
				';
			$help['description'] = array(
				'title' => __('Description', 'cfsp'),
				'description' => $description
			);
		}
		return $help;
	}

	public function cfsp_admin_help_theme($help = array()) {
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

	public function cfsp_admin_help_shortcodes($help = array()) {
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

	public function cfsp_admin_help_shortcode_support($help = array()) {
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

	public function cfsp_admin_help_moreinfo($help = array()) {
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
}

