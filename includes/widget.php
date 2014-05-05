<?php

## Widget Functionality

class cfsnip_Widget extends WP_Widget {
	function cfsnip_Widget() {
		$widget_ops = array('classname' => 'cfsnip-widget', 'description' => 'Widget for displaying selected CF Snippets');
		$this->WP_Widget('cfsnip-widget', 'CF Snippets', $widget_ops);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		// Get the snippet content
		$content = $cf_snippet->get($instance['list_key']);
		// If we don't have anything to display, no need to proceed
		if (empty($content)) { return; }
		$title = esc_html($instance['title']);

		echo $before_widget;
		if (!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		echo $content;
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_key'] = strip_tags($new_instance['list_key']);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'list_key' => ''));

		$title = $instance['title'];
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet_Manager();
		}
		$keys = $cf_snippet->get_key_count();
		
		if (!$keys < 1) {
			include(CFSP_DIR . 'views/widget-edit.php');
		}
		else {
			include(CFSP_DIR . 'views/widget-empty.php');
		}
	}
}
add_action('widgets_init', 'cfsnip_register_widget');

function cfsnip_register_widget() {
	register_widget('cfsnip_Widget');
}
