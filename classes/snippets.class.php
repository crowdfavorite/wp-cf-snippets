<?php
/**
 *
 * @package cfsp_snippet
 */
class CF_Snippet {
	public function __construct() {
		$snippets = get_option('cfsnip_snippets');
		if (!is_array($snippets)) {
			$this->install();
		}
	}
	
	public function get($key, $default = '') {
		$snippets = get_option('cfsnip_snippets');
		
		if (!empty($snippets[$key]['content'])) {
			return do_shortcode(apply_filters('cfsp-get-content', stripslashes($snippets[$key]['content'])));
		}
		else if (!empty($default)) {
			$description = ucwords(str_replace(array('-','_'), ' ', $key));
			$this->save($key, $default, $description);
			return $this->get($key);
		}
	}
	
	public function save($key, $content, $description) {
		$snippets = get_option('cfsnip_snippets');
		$key = sanitize_title($key);
		$snippets[$key]['content'] = $content;
		$snippets[$key]['description'] = $description;
		update_option('cfsnip_snippets', $snippets);
	}

	public function remove($key) {
		$snippets = get_option('cfsnip_snippets');
		$key = sanitize_title($key);
		unset($snippets[$key]);
		update_option('cfsnip_snippets', $snippets);
	}

	public function edit($key) {
		if (!$this->exists($key)) { return ''; }

		$snippets = get_option('cfsnip_snippets');
		$description = $snippets[$key]['description'];
		$content = $snippets[$key]['content'];

		$html = '
		<div class="cfsp">
			<div class="cfsp-edit-snip">
				<table class="form-table" border="0">
					<tr>
						<th style="width:50px;">'.__('Description').'</th>
						<td><input type="text" name="cfsp-description" id="cfsp-description" value="'.stripslashes($description).'" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Content').'</th>
						<td><textarea name="cfsp-content" id="cfsp-content" class="widefat cfsp-popup-edit-content" cols="50" rows="8">'.stripslashes($content).'</textarea></td>
					</tr>
				</table>
			</div>
		</div>
		<p>
			<input type="hidden" name="cfsp-key" id="cfsp-key" value="'.esc_attr($key).'" />
			<input type="button" class="button-primary cfsp-popup-submit" value="Save" />
			<input type="button" class="button cfsp-popup-cancel" value="Cancel" />
		</p>
		';
		return $html;
	}
	
	public function add_display() {
		$html = '
		<div class="cfsp">
			<div class="cfsp-new-snip">
				<table class="form-table" border="0">
					<tr>
						<th style="width:50px;">'.__('Key').'</th>
						<td><input type="text" name="cfsp-key" id="cfsp-key" value="" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Description').'</th>
						<td><input type="text" name="cfsp-description" id="cfsp-description" value="" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Content').'</th>
						<td><textarea name="cfsp-content" id="cfsp-content" class="widefat cfsp-popup-edit-content" cols="50" rows="8"></textarea></td>
					</tr>
				</table>
			</div>
		</div>
		<p>
			<input type="button" class="button-primary cfsp-popup-new-submit" value="Submit" />
			<input type="button" class="button cfsp-popup-cancel" value="Cancel" />
		</p>
		';
		return $html;
	}
	
	public function add($data) {
		if (!is_array($data) || empty($data) || empty($data['key'])) { return false; }
		
		$key = $this->check_key(stripslashes($data['key']));
		$description = stripslashes($data['description']);
		$content = stripslashes($data['content']);
		
		$snippets = get_option('cfsnip_snippets');
		$snippets[$key]['content'] = $content;
		$snippets[$key]['description'] = $description;
		update_option('cfsnip_snippets', $snippets);
	}
	
	public function check_key($key, $i = 0) {
		if (!$this->exists($key)) { return $key; }
		return $this->check_key($key.'-'.$i, $i++);
	}
	
	public function admin_display($key) {
		if (!$this->exists($key)) { return ''; }

		$snippets = get_option('cfsnip_snippets');
		$description = $snippets[$key]['description'];
		
		$html = '
		<tr id="cfsp-'.esc_attr($key).'">
			<td class="cfsp-key">
				'.$key.'
			</td>
			<td class="cfsp-description">
				'.$description.'
			</td>
			<td class="cfsp-buttons" style="text-align:center;">
				<input type="button" value="Edit" class="button cfsp-edit-button" id="'.sanitize_title($key).'-edit-button" />
				<input type="button" value="Preview" class="button cfsp-preview-button" id="'.sanitize_title($key).'-preview-button" />
				<input type="button" value="Delete" class="button cfsp-delete-button" id="'.sanitize_title($key).'-delete-button" />
			</td>
		</tr>
		';
		return $html;
	}
	
	public function get_keys() {
		$snippets = get_option('cfsnip_snippets');
		$keys = array();
		if (is_array($snippets) && !empty($snippets)) {
			foreach ($snippets as $key => $content) {
				$keys[] = $key;
			}
		}
		return $keys;
	}
	
	public function exists($key) {
		$snippets = get_option('cfsnip_snippets');
		if (is_array($snippets) && !empty($snippets) && array_key_exists($key, $snippets)) {
			return true;
		}
		return false;
	}
	
	public function install() {
		add_option('cfsnip_snippets', array(), '', 'no');
	}
}

?>