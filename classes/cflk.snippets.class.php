<?php

class cfsp_link extends cflk_link_base {
	private $type_display = '';
	function __construct() {
		$this->type_display = __('Snippets', 'cf-links');
		parent::__construct('snippet', $this->type_display);
		if (is_admin()) {
			$this->show_new_window_field = false;
			$this->show_title_field = false;
		}
	}
	
	/**
	 * Front end display
	 *
	 * @param array $data 
	 * @return string html
	 */
	function display($data) {
		if (!class_exists('CF_Snippet')) { return parent::display($data); }
		if (!empty($data['cflk-snippet-id']) || !empty($data['link'])) {
			$id = '';
			if (!empty($data['cflk-snippet-id'])) {
				$id = $data['cflk-snippet-id'];
			}
			else if (!empty($data['link'])) {
				$id = $data['link'];
			}
			
			if (!empty($id)) {
				$cf_snippet = new CF_Snippet();
				$data['link'] = '';
				$data['title'] = $cf_snippet->get($id, false, false);
			}
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
		$title = $description = $details = '';
		$cf_snippet = new CF_Snippet();
		
		if (!empty($data['cflk-wordpress-id'])) {
			$details = $cf_snippet->get_meta($data['cflk-snippet-id']);
		}
		if (!empty($data['link'])) {
			$details = $cf_snippet->get_meta($data['link']);
		}
		
		if (is_array($details) && !empty($details['description'])) {
			$description = $details['description'];
		}
		
		if (!empty($data['title'])) {
			$title = $data['title'];
		}
		else {
			$title = $description;
		}
		
		return array(
			'title' => $title,
			'description' => $description
		);
	}
	
	function admin_form($data) {
		$args = array(
			'echo' => false,
			'id' => 'cflk-dropdown-snippets',
			'name' => 'cflk-snippet-id',
			'selected' => (!empty($data['cflk-snippet-id']) ? intval($data['cflk-snippet-id']) : 0),
			'class' => 'elm-select'
		);
		return '
			<div class="elm-block">
				<label>'.__('Snippets', 'cfsp').'</label>
				'.$this->dropdown($args).'
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
		
		$html = '<select name="'.$name.'" id="'.$id.'" class="'.$class.'">';
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