<?php
/**
 * class CF_Snippet_Base
 * Shared utility functions for other CF snippet classes
 *
 * @package cf-snippets
 */

abstract class CF_Snippet_Base {
	/**
	 * Changing this in a subclass will result in probably broken behavior.
	 */
	protected $post_type = '_cf_snippet';
	protected $required_permission = null;

	/**
	 * Central function to control the mangement of snippets
	 */
	protected function user_can_admin_snippets() {
		if (!$this->required_permission) {
			$this->required_permission = apply_filters('cfsp_admin_permission', 'manage_options');
		}
		return current_user_can($this->required_permission);
	}

}

