<?php
/**
 * cf-snippets template functions
 *
 * @package cf-snippets
 */


/**
 * Template API - echo snippet content
 */
function cfsp_content($key, $default = false, $create = true, $args = array()) {
	echo cfsp_get_content($key, $default, $create, $args);
}

/**
 * Template API - get snippet content
 */
function cfsp_get_content($key, $default = false, $create = true, $args = array()) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create, $args);
}

