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


	}
}
