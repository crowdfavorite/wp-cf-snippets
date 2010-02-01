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
	
	## Display Functions
	
	/**
	 * This function gets a specific key and will create one with data passed in if the key does not exist
	 *
	 * @param string $key - Key to get
	 * @param string $default - Data to use for the content if the key does not exist
	 * @return void - Content for the key passed in
	 */
	public function get($key, $default = '') {
		$snippets = get_option('cfsnip_snippets');
		
		if (!empty($snippets[$key]['content'])) {
			return do_shortcode(apply_filters('cfsp-get-content', stripslashes($snippets[$key]['content'], $key)));
		}
		else if (!empty($default)) {
			$description = ucwords(str_replace(array('-','_'), ' ', $key));
			$this->save($key, $default, $description);
			return $this->get($key);
		}
	}
	
	/**
	 * This function gets all of the keys available and passes them back as an array
	 *
	 * @return array - Array of keys
	 */
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
	
	/**
	 * This function gets all of the data and returns it
	 *
	 * @return array - Array of content
	 */
	public function get_all() {
		return get_option('cfsnip_snippets');
	}
	
	/**
	 * This function checks to see if a specific key exists, and returns true if it does and false if it doesn't
	 *
	 * @param string $key - Key to search for
	 * @return bool - Result of wether the key exists or not
	 */
	public function exists($key) {
		$snippets = get_option('cfsnip_snippets');
		if (is_array($snippets) && !empty($snippets) && array_key_exists($key, $snippets)) {
			return true;
		}
		return false;
	}
	

	## Admin Display Functions
	
	/**
	 * This function builds a display to edit the content with the specified key
	 *
	 * @param string $key - Key to edit
	 * @return void
	 */
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
	
	/**
	 * This function builds a display to add a new item
	 *
	 * @return void
	 */
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
	
	/**
	 * This function display basic information about the key passed in.  Including the Key and description, along with edit, preview and delete buttons
	 *
	 * @param string $key - Key to display
	 * @return void
	 */
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
	
	
	## Database Interaction Functions
	
	/**
	 * This function takes a key, content and description and adds them to the database.  The key is checked to make
	 * sure that another key doesn't exists with the same value, and updates it if it does
	 *
	 * @param string $key - Key to save
	 * @param string $content - Content to save
	 * @param string $description - Description to save
	 * @return void
	 */
	public function add($key, $content, $description) {
		if (!is_array($data) || empty($data) || empty($data['key'])) { return false; }
		
		$key = $this->check_key(stripslashes($data['key']));
		$description = stripslashes($data['description']);
		$content = stripslashes($data['content']);
		
		$snippets = get_option('cfsnip_snippets');
		$snippets[$key]['content'] = $content;
		$snippets[$key]['description'] = $description;
		update_option('cfsnip_snippets', $snippets);
	}
	
	/**
	 * This function takes a key, content and description and saves them to the database
	 *
	 * @param string $key - Key to update
	 * @param string $content - Content to update
	 * @param string $description - Description to update
	 * @return void
	 */
	public function save($key, $content, $description) {
		$snippets = get_option('cfsnip_snippets');
		$key = sanitize_title($key);
		$snippets[$key]['content'] = $content;
		$snippets[$key]['description'] = $description;
		update_option('cfsnip_snippets', $snippets);
	}
	
	/**
	 * This function takes a key, and removes that key from the database
	 *
	 * @param string $key - Key to remove
	 * @return void
	 */
	public function remove($key) {
		$snippets = get_option('cfsnip_snippets');
		$key = sanitize_title($key);
		unset($snippets[$key]);
		update_option('cfsnip_snippets', $snippets);
	}
	

	## Auxiliary Functions
	
	/**
	 * This function checks to see if a key exists, and increments the value if it does and returns
	 *
	 * @param string $key - Key to check
	 * @param string $i - Increment value currently at
	 * @return string - Updated key value of a non-conflicting key
	 */
	public function check_key($key, $i = 0) {
		if (!$this->exists($key)) { return $key; }
		return $this->check_key($key.'-'.$i, $i++);
	}
	
	/**
	 * This function adds the DB option with an empty array and an autoload value of no so it doesn't get auto loaded every time
	 *
	 * @return void
	 */
	public function install() {
		add_option('cfsnip_snippets', array(), '', 'no');
	}

}

?>