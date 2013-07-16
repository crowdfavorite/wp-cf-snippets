<?php
/**
 *
 * @package cfsp_snippet
 */
class CF_Snippet {
	
	private $post_type = '_cf_snippet';
	
	public function __construct() {
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
		$key = sanitize_title($key);
		
		$snippet = $this->get_snippet($key);
		
		if (!empty($snippet)) {
			$content = get_post_meta($snippet->ID, '_cfsp_content', true);
			return do_shortcode(apply_filters('cfsp-get-content', $content, $key));
		}
		else if (!empty($default) && $create) {
			if (empty($description)) {
				$description = ucwords(str_replace(array('-','_'), ' ', $key));
			}
			$this->save($key, $default, $description);
			return $this->get($key);
		}
	}
	
	/**
	 * This function returns a snippet with a matching key, creates one if none are found
	 * 
	 * @param string $key Key to get
	 * @param string $default Data to use for the content if the key does not exist
	 * @return stdObj A snippet with matching key, note that this does not return the content
	 */

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
			$this->save($key, $default, $description);
			return $this->get($key);
		}
	}
	
	/**
	 * This function provides the extra meta data for a particular snippet
	 *
	 * @param string $key Key for the snippet to get the meta data for.
	 * @return array Meta for the snippet
	 */
	public function get_meta($key) {
		$key = sanitize_title($key);
		$meta = array();
		$post_id = $this->get_id($key);
		if ($post_id) {
			$all_meta = get_post_custom($post_id);
			foreach ($all_meta as $meta_key => $meta_value) {
				if (strpos($meta_key, '_cfsp_') !== false && $meta_key != '_cfsp_content') {
					$meta[$meta_key] = $meta_value;
				}
			}
		}
		return $meta;
	}
	
	/**
	 * This function gets all of the keys available and passes them back as an array
	 *
	 * @return array - Array of keys
	 */
	public function get_keys() {
		$snippets = $this->get_all();
		$keys = array();
		foreach ($snippets as $snippet) {
			$keys[] = $snippet->post_name;
		}
		return $keys;
	}
	
	/**
	 * This function gets all of the data and returns it
	 *
	 * @return array - Array of content
	 */
	public function get_all() {
		$posts = get_posts( array(
			'post_type' => $this->post_type,
			'numberposts' => -1,
		) );
		return $posts;
	}
	
	/**
	 * 	This function gets a snippet (post) based on its key (title)
	 * 
	 * @param string $key key to search snippets for
	 * @return stdObj a snippet (post) object without meta (including content)
	 */
	public function get_snippet($key) {
		$key = sanitize_title($key);
		if (function_exists('wpcom_vip_get_page_by_path')) {
			return wpcom_vip_get_page_by_path( $key, OBJECT, $this->post_type );
		}
		else {
			return get_page_by_path($key, OBJECT, $this->post_type);
		}
	}
	
	/**
	 * This function checks to see if a specific key exists, and returns true if it does and false if it doesn't
	 *
	 * @param string $key - Key to search for
	 * @return bool - Result of wether the key exists or not
	 */
	public function exists($key) {
		$key = sanitize_title($key);
		$snippet = $this->get_snippet($key);
		if (!empty($snippet)) {
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

		$snippet = $this->get_snippet($key);
		$description = $snippet->post_title;
		$content = get_post_meta($snippet->ID, '_cfsp_content', true);

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
						<td><textarea name="cfsp-content" id="cfsp-content" class="widefat cfsp-popup-edit-content" cols="50" rows="8">'.htmlspecialchars($content).'</textarea></td>
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
						<th style="width:50px;">'.__('Key', 'cfsp').'</th>
						<td><input type="text" name="cfsp-key" id="cfsp-key" value="" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Description', 'cfsp').'</th>
						<td><input type="text" name="cfsp-description" id="cfsp-description" value="" class="widefat" /></td>
					</tr>
					<tr>
						<th style="width:50px;">'.__('Content', 'cfsp').'</th>
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

		$snippet = $this->get_snippet($key);
		$description = $snippet->post_title;

		// Escape the key once instead of multiple times
		$key = esc_attr($key);
		
		$html = '
		<tr id="cfsp-'.$key.'">
			<td class="cfsp-key" style="vertical-align:middle;">
				'.$key.'
			</td>
			<td class="cfsp-description">
				<span class="cfsp-description-content">'.esc_html($description).'</span>
				<div id="'.$key.'-showhide" class="cfsp-tags-showhide">
					'.__('Show: ', 'cfsp').' <a href="#" rel="'.$key.'-shortcode-template">'.__('Template Tag &amp; Shortcode', 'cfsp').'</a>
				</div>
				<div id="'.$key.'-shortcode-template" class="cfsp-shortcode-template">
					Shortcode: <code>[cfsp key="'.$key.'"]</code><br />
					Template Tag: <code>&lt;?php if (function_exists(&#x27;cfsp_content&#x27;)) { cfsp_content(&#x27;'.$key.'&#x27;); } ?&gt;</code>
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
			foreach ($snippets as $snippet) {
				$key = $snippet->post_name;
				$description = $snippet->post_title;
				$select .= '<option value="'.$key.'"'.selected($selected, $key, false).'>'.esc_html($description).'</option>';
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
			foreach ($snippets as $snippet) {
				$key = $snippet->post_name;
				$description = $snippet->post_title;

				if (!empty($description)) {
					if ($links) {
						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.esc_html($description).'</a>';
					}
					else {
						$description = esc_html($description);
					}
				}
				else if (!empty($key)) {
					if ($links) {
						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.esc_html($key).'</a>';
					}
					else {
						$description = esc_html($key);
					}
				}
				
				if (!empty($description)) {
					$list .= '<li>'.$description.'</li>';
				}
			}
		}
		
		if (!empty($list)) {
			$list = '<ul class="cfsp-list">'.$list.'</ul>';
		}
		
		return $list;
	}
	
	## Database Interaction Functions
	
	/**
	 * This function takes a key, content and description and saves them to the database
	 *
	 * @param string $key - Key to update
	 * @param string $content - Content to update
	 * @param string $description - Description to update
	 * @return bool - Result of the save
	 */
	public function save($key, $content, $description, $args = array()) {
		// Check to make sure we don't have any variable name conflicts
		unset($args['key'], $args['content'], $args['description'], $args['snippet'], $args['post_id'], $args['mod_cap']);
		
		$defaults = array();
		
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		
		$key = sanitize_title($key);
		if (empty($key)) { return false; }
		$post_id = $this->get_id($key);
		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_status' => 'publish',
			'post_title' => $description,
			'ID' => $post_id,
		);
		$post_id = wp_insert_post($snippet);
		
		if (!$post_id) { 
			return false; 
		}
		else if (!update_post_meta($post_id, '_cfsp_content', $content)) {
			return false;
		}
		
		foreach ($args as $arg_key => $arg_value) {
			if (!update_post_meta($post_id, '_cfsp_'.$arg_key, $arg_value)) { 
				return false;	
			}
		}
		
		return $true;
	}
	
	/**
	 * This function takes a key, content and description and adds them to the database.  The key is checked to make
	 * sure that another key doesn't exists with the same value, and updates it if it does
	 *
	 * @param string $key - Key to save
	 * @param string $content - Content to save
	 * @param string $description - Description to save
	 * @return bool - Result of the add
	 */
	public function add($key, $content, $description, $args = array()) {
		// Check to make sure we don't have any variable name conflicts
		unset($args['key'], $args['content'], $args['description'], $args['snippet'], $args['post_id'], $args['mod_cap']);

		$defaults = array(
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		
		//  Make sure key is valid
		$key = sanitize_title($key);
		// Check the key to see if one exists, fix if so
		$key = $this->check_key($key);
		if (empty($key)) { return false; }
		if (empty($description)) {
			$description = $key;
		}
		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_status' => 'publish',
			'post_title' => $description,
		);
		
		$post_id = wp_insert_post($snippet);
	
		if (!$post_id) {
			 return false; 
		}
		else if (!update_post_meta($post_id, '_cfsp_content', $content)) {
			return false;
		}

		foreach ($args as $arg_key => $arg_value) {
			if (!update_post_meta($post_id, '_cfsp_'.$arg_key, $arg_value)) { 
				return false; 
			}
		}

		return $key;
	}
		
	/**
	 * This function takes a key, and removes that post with matching key (post_name) from the database
	 *
	 * @param string $key - Key to remove
	 * @return bool - Result of the remove
	 */
	public function remove($key) {
		$key = sanitize_title($key);
		$post_id = $this->get_id($key);
		return wp_delete_post($post_id, true);
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
		//  Make sure key is valid
		$key = sanitize_title($key);
		$i = 0;
		while(1) {
			$check_key = $key.'-'.$i;
			if (!$this->exists($check_key)) { break; }
			$i++;
		}
		
		return $check_key;
	}
	
	/**
	 *  This function gets a post ID based on a key (post_name)
	 * 
	 * @param string $key - Key to check
	 * @return int ID of a post, 0 otherwise
	 */
	public function get_id($key) {
		//  Make sure key is valid
		$key = sanitize_title($key);
		$snippet = $this->get_snippet($key);
		if ($snippet) {
			return $snippet->ID;
		}
		return 0;
	}
	
	/**
	 * This function registers a custom post type where we store the snippets
	 * 
	 * @return void
	 */
	public function register_post_type() {
		$args = array(
			'public' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'can_export' => false,
		  );

		register_post_type($this->post_type, $args);
	}
}

?>
