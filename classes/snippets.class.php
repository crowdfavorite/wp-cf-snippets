<?php
/**
 *
 * @package cfsp_snippet
 */
class CF_Snippet {
	public function __construct() {
		$snippets = $this->get_all();
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
	public function get($key, $default = '', $create = true, $args = array()) {
		$defaults = array(
			'description' => '',
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		
		$snippets = $this->get_all();
		$key = sanitize_title($key);
		
		if (!empty($snippets[$key]['content'])) {
			return do_shortcode(apply_filters('cfsp-get-content', stripslashes($snippets[$key]['content']), $key));
		}
		else if (!empty($default) && $create) {
			if (empty($description)) {
				$description = ucwords(str_replace(array('-','_'), ' ', $key));
			}
			$this->save($key, $default, stripslashes($description));
			return $this->get($key);
		}
	}
	
	public function get_info($key, $default = '', $create = true, $args = array()) {
		$defaults = array(
			'description' => '',
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		$snippets = $this->get_all();
		$key = sanitize_title($key);
		
		if (!empty($snippets[$key])) {
			return do_shortcode(apply_filters('cfsp-get-info', $snippets[$key], $key));
		}
		else if (!empty($default) && $create) {
			if (empty($description)) {
				$description = ucwords(str_replace(array('-','_'), ' ', $key));
			}
			$this->save($key, $default, stripslashes($description));
			return $this->get($key);
		}
	}
	
	/**
	 * This function gets all of the keys available and passes them back as an array
	 *
	 * @return array - Array of keys
	 */
	public function get_keys() {
		$snippets = $this->get_all();
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
		$snippets = $this->get_all();
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

		$snippets = $this->get_all();
		$description = $snippets[$key]['description'];
		$content = $snippets[$key]['content'];

		$html = '
		<div class="cfsp">
			<div class="cfsp-edit-snip">
				<table class="form-table" border="0">
					<tr>
						<th style="width:50px;">'.__('Description').'</th>
						<td><input type="text" name="cfsp-description" id="cfsp-description" value="'.esc_attr($description).'" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Content').'</th>
						<td><textarea name="cfsp-content" id="cfsp-content" class="widefat cfsp-popup-edit-content" cols="50" rows="8">'.esc_attr($content).'</textarea></td>
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
		
		$snippets = $this->get_all();
		$description = $snippets[$key]['description'];

		// Escape the key once instead of multiple times
		$key = esc_attr($key);
		
		$html = '
		<tr id="cfsp-'.$key.'">
			<td class="cfsp-key" style="vertical-align:middle;">
				'.$key.'
			</td>
			<td class="cfsp-description">
				<span class="cfsp-description-content">'.htmlentities($description).'</span>
				<div id="'.$key.'-showhide" class="cfsp-tags-showhide">
					'.__('Show: ', 'cfsp').' <a href="#" rel="'.$key.'-template">'.__('Template Tag', 'cfsp').'</a>&nbsp;|&nbsp;<a href="#" rel="'.$key.'-shortcode">'.__('Shortcode', 'cfsp').'</a>
				</div>
				<div id="'.$key.'-shortcode" class="cfsp-shortcode">
					<code>[cfsp key="'.$key.'"]</code>
				</div>
				<div id="'.$key.'-template" class="cfsp-template-tag">
					<code>&lt;?php if (function_exists(&#x27;cfsp_content&#x27;)) { cfsp_content(&#x27;'.$key.'&#x27;); } ?&gt;</code>
				</div>
			</td>
			<td class="cfsp-buttons" style="vertical-align:middle; text-align:center;">
				<input type="button" value="Edit" class="button cfsp-edit-button" id="'.$key.'-edit-button" />
				<input type="button" value="Preview" class="button cfsp-preview-button" id="'.$key.'-preview-button" />
				<input type="button" value="Delete" class="button cfsp-delete-button" id="'.$key.'-delete-button" />
			</td>
		</tr>
		';
		return $html;
	}
	
	/**
	 * This function will return a list of select options with each of the items keys as the option and description as the display.  A 
	 * selected key can be passed in to select the proper key.
	 *
	 * @param string $selected - Item key to be selected
	 * @return string - Options with the key as the value and description as the display
	 */
	public function select_display($selected = '') {
		$snippets = $this->get_all();
		$select = '';
		if (is_array($snippets) && !empty($snippets)) {
			foreach ($snippets as $key => $snippet) {
				$select .= '<option value="'.$key.'"'.selected($selected, $key, false).'>'.htmlentities($snippet['description']).'</option>';
			}
		}
		return $select;
	}
	
	/**
	 * This function will return an unordered list of descriptions.  An option is available to make a link with the key as the rel of that link
	 *
	 * @param bool $links - Wether to make the list items links
	 * @return string - Unordered list of links
	 */
	public function list_display($links = true) {
		$snippets = $this->get_all();
		$list = '';
		if (is_array($snippets) && !empty($snippets)) {
			foreach ($snippets as $key => $snippet) {
				$description = '';
				if ($links) {
					$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.htmlentities($snippet['description']).'</a>';
				}
				else {
					$description = htmlentities($snippet['description']);
				}
				$list .= '<li>'.$description.'</li>';
			}
		}
		
		if (!empty($list)) {
			$list = '<ul class="cfsp-list">'.$list.'</ul>';
		}
		
		return $list;
	}
	
	## Database Interaction Functions
	
	/**
	 * This function takes a key, content and description and adds them to the database.  The key is checked to make
	 * sure that another key doesn't exists with the same value, and updates it if it does
	 *
	 * @param string $key - Key to save
	 * @param string $content - Content to save
	 * @param string $description - Description to save
	 * @return bool - Result of the add
	 */
	public function add($key, $content, $description) {
		if (empty($key)) { return false; }
		
		// Check the key to see if one exists, fix if so
		$key = $this->check_key(stripslashes($key));
		if (empty($key)) { return false; }
		
		$snippets = $this->get_all();
		$snippets[$key]['content'] = stripslashes($content);
		$snippets[$key]['description'] = stripslashes($description);
		if ($this->update_option($snippets)) {
			return $key;
		}
		return false;
	}
	
	/**
	 * This function takes a key, content and description and saves them to the database
	 *
	 * @param string $key - Key to update
	 * @param string $content - Content to update
	 * @param string $description - Description to update
	 * @return bool - Result of the save
	 */
	public function save($key, $content, $description) {
		$snippets = $this->get_all();
		$key = sanitize_title($key);
		$snippets[$key]['content'] = stripslashes($content);
		$snippets[$key]['description'] = stripslashes($description);
		return $this->update_option($snippets);
	}
	
	/**
	 * This function takes a key, and removes that key from the database
	 *
	 * @param string $key - Key to remove
	 * @return bool - Result of the remove
	 */
	public function remove($key) {
		$snippets = $this->get_all();
		$key = sanitize_title($key);
		unset($snippets[$key]);
		return $this->update_option($snippets);
	}
	
	/**
	 * This function updates the DB option with the inserted value
	 *
	 * @param array $value - New value for the options table
	 * @return bool - Result of the update
	 */
	public function update_option($value) {
		return update_option('cfsnip_snippets', $value);
	}

	## Auxiliary Functions
	
	/**
	 * This function checks to see if a key exists, and increments the value if it does and returns
	 *
	 * @param string $key - Key to check
	 * @param string $i - Increment value currently at
	 * @return string - Updated key value of a non-conflicting key
	 */
	public function check_key($key) {
		if (!$this->exists($key)) { return $key; }
		
		$i = 0;
		while(1) {
			$check_key = $key.'-'.$i;
			if (!$this->exists($check_key)) { break; }
			$i++;
		}
		
		return $check_key;
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