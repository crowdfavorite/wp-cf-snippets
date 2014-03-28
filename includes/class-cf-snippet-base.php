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

	/**
	 * Central function to control the mangement of snippets
	 */
	protected function user_can_admin_snippets() {
		
		if (! current_user_can('manage_options')) {
			include(CFSP_DIR . 'views/ajax-delete-error.php');
			die();

			return false;
		}

		return true;
	}

}

