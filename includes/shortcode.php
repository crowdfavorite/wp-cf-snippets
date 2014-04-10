<?php


function cfsp_shortcode($attrs, $content=null) {
	if (is_array($attrs)) {
		$key = '';
		if (!empty($attrs['name'])) {
			$key = $attrs['name'];
		}
		else if (!empty($attrs['key'])) {
			$key = $attrs['key'];
		}

		if (empty($key)) { return ''; }
		global $cf_snippet;
		if (class_exists('CF_Snippet_Manager') && !($cf_snippet instanceof CF_Snippet_Manager)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		return $cf_snippet->get($key, false, false);
	}
	return '';
}
add_shortcode('cfsp', 'cfsp_shortcode');

