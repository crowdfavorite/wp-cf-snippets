<?php

class cfsp_link extends cflk_link_base {
	private $type_display = '';
	function __construct() {
		$this->type_display = __('CF Snippet', 'cf-links');
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
		$title = $description = $details = $key = '';
		$cf_snippet = new CF_Snippet();
		
		if (!empty($data['cflk-snippet-id'])) {
			$key = $data['cflk-snippet-id'];
		}
		if (!empty($data['link'])) {
			$key = $data['link'];
		}
		
		if (!empty($key)) {
			$details = $cf_snippet->get_meta($key);
		}
		
		if (!is_array($details) || empty($details)) {
			return array(
				'title' => __('Snippet: ', 'cfsp').$key,
				'description' => __('Snippet does not exist', 'cfsp')
			);
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
		$key = 0;
		if (is_array($data) && !empty($data) && !empty($data['cflk-snippet-id'])) {
			$key = $data['cflk-snippet-id'];
		}
		else if (is_array($data) && !empty($data) && !empty($data['link'])) {
			$key = $data['link'];
		}
		
		
		$args = array(
			'echo' => false,
			'id' => 'cflk-dropdown-snippets',
			'name' => 'cflk-snippet-id',
			'selected' => $key,
			'class' => 'elm-select'
		);
		return '
			<div class="elm-block">
				<label>'.__('CF Snippet', 'cfsp').'</label>
				'.$this->dropdown($args).'
			</div>
		';
	}
	
	function type_display() {
		return $this->type_display;
	}
	
	function update($data) {
		$data['link'] = $data['cflk-snippet-id'];
		return $data;
	}
	
	function dropdown($args = array()) {
		$html = '';
		$defaults = array(
			'echo' => 1, 'selected' => 0, 'name' => 'user', 
			'class' => '', 'id' => ''
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$post_snips = '';
		$main_snips = '';
		
		if (!class_exists('CF_Snippet')) { return; }
		$cf_snippet = new CF_Snippet();
		$keys = $cf_snippet->get_keys();
		if (is_array($keys) && !empty($keys)) {
			$html = '<select name="'.$name.'" id="'.$id.'" class="'.$class.'">';
			foreach ($keys as $key) {
				$meta = $cf_snippet->get_meta($key);
				if ($meta['post_id']) {
					$post_snips .= '<option value="'.esc_attr($key).'"'.selected($selected, $key, false).'>'.esc_attr($meta['description']).'</option>';
				}
				else {
					$main_snips .= '<option value="'.esc_attr($key).'"'.selected($selected, $key, false).'>'.esc_attr($meta['description']).'</option>';
				}
			}
			$html .= $main_snips.$post_snips;
			$html .= '</select>';
		}
		else {
			$html = __('No snippets have been created.  Please create snippets on the CF Snippets settings page to proceed.', 'cfsp');
			$html .= '<input type="hidden" name="'.$name.'" id="'.$id.'" class="'.$class.'" value="'.$selected.'" />';
		}
		return $html;
	}
}

?>