<?php

class cfsp_link extends cflk_link_base {
	function __construct() {
		parent::__construct('snippet', __('Snippets', 'cfsp'));
		$this->show_new_window_field = false;
		$this->show_title_field = false;
	}
	
	/**
	 * Front end display
	 *
	 * @param array $data 
	 * @return string html
	 */
	function display($data) {
		if (!class_exists('CF_Snippet')) { return parent::display($data); }
		if (!empty($data['cflk-snippet-id'])) {
			$cf_snippet = new CF_Snippet();
			$data['link'] = '';
			$data['title'] = $cf_snippet->get($data['cflk-snippet-id'], false, false);
		}
		else {
			$data['link'] = '';
			$data['title'] = __('Unknown Snippet', 'cfsp');
		}
		return parent::display($data);
	}
	
	/**
	 * Admin info display
	 *
	 * @param array $data 
	 * @return string html
	 */
	function admin_display($data) {
		if (!class_exists('CF_Snippet')) { return; }
		if (!empty($data['cflk-snippet-id'])) {
			$cf_snippet = new CF_Snippet();
			$meta = $cf_snippet->get_meta($data['cflk-snippet-id']);
			$title = $meta['description'];
		}
		else {
			$title = __('Unknown Snippet', 'cfsp');
		}
		return '
			<div>
				'.__('Snippet:', 'cfsp').' <span class="link">'.esc_html($title).'</span>
			</div>
			';
	}
	
	function admin_form($data) {
		$args = array(
			'echo' => false,
			'id' => 'cflk-dropdown-snippets',
			'name' => 'cflk-snippet-id',
			'selected' => (!empty($data['cflk-snippet-id']) ? intval($data['cflk-snippet-id']) : 0) 
		);
		$snippets = $this->dropdown($args);
		return '
			<div>
				'.__('Snippets: ', 'cfsp').$snippets.'
			</div>
			';
	}
	
	function update($data) {
		$data['link'] = $data['cflk-snippet-id'];
		return $data;
	}
	
	function dropdown($args = array()) {
		$defaults = array(
			'echo' => 1, 'selected' => 0, 'name' => 'user', 
			'class' => '', 'id' => ''
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$post_snips = '';
		$main_snips = '';
		
		$html = '<select name="'.$name.'" id="'.$id.'">';
		if (!class_exists('CF_Snippet')) { return; }
		$cf_snippet = new CF_Snippet();
		$keys = $cf_snippet->get_keys();
		if (is_array($keys) && !empty($keys)) {
			foreach ($keys as $key) {
				$meta = $cf_snippet->get_meta($key);
				if ($meta['post_id']) {
					$post_snips .= '<option value="'.esc_attr($key).'"'.selected($selected, $key, false).'>'.esc_attr($meta['description']).'</option>';
				}
				else {
					$main_snips .= '<option value="'.esc_attr($key).'"'.selected($selected, $key, false).'>'.esc_attr($meta['description']).'</option>';
				}
			}
		}
		$html .= $main_snips.$post_snips;
		$html .= '</select>';
		return $html;
	}
}

?>