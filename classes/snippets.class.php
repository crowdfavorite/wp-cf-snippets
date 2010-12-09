<?php
/**
 *
 * @package cfsp_snippet
 */
class CF_Snippet {
	
	private $post_type = 'cf_snippet';
	
	public function __construct() {
	}
	
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
			return htmlspecialchars_decode(do_shortcode(apply_filters('cfsp-get-content', stripslashes($snippet->post_content), $key)));
		}
		else if (!empty($default) && $create) {
			if (empty($description)) {
				$description = ucwords(str_replace(array('-','_'), ' ', $key));
			}
			$this->save($key, $default, stripslashes($description));
			return $this->get($key);
		}
	}
	
/* WHATS THIS DO??? */
	public function get_info($key, $default = '', $create = true, $args = array()) {
		$defaults = array(
			'description' => '',
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
		
		$snippets = $this->get_all();
		$key = sanitize_title($key);
		
		if (!empty($snippets[$key])) {
			return htmlspecialchars_decode(do_shortcode(apply_filters('cfsp-get-info', $snippets[$key], $key)));
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
	 * This function provides the extra meta data for a particular snippet
	 *
	 * @param string $key | Key for the snippet to get the meta data for.
	 * @return array | Meta for the snippet (also returns description)
	 */
	public function get_meta($key) {
		$meta = array();
		$post_id = $this->get_id($key);
		if ($post_id) {
			$all_meta = get_post_custom($post_id);
			foreach ($all_meta as $key => $value) {
				if (strpos($key, '_cfsp_') !== false) {
					$meta[$key] = $value;
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
		) );
		return $posts;
	}
	
	/**
	 * 	This function gets a snippet (post) based on its key (title)
	 * 
	 * @param string $key key to search snippets for
	 * @return stdObj a snippet (post) object without meta
	 */
	public function get_snippet($key) {
		$posts = get_posts( array(
			'post_type' => $this->post_type,
			'name' => $key,
		) );
		if (is_array($posts) && !empty($posts)) {
			return $posts[0];
		}
		return false;
	}
	
	/**
	 *	This function gets the description of a snippet based on its key 
	 * 
	 * @param string $key key to search snippets for
	 * @return string Description of the snippet 
	 */
	public function get_description($key) {
		$post_id = $this->get_id($key);
		if ($post_id) {
			return get_post_meta($post_id, '_cfsp_description', true);
		}
		return false;
	}
	/**
	 * This function checks to see if a specific key exists, and returns true if it does and false if it doesn't
	 *
	 * @param string $key - Key to search for
	 * @return bool - Result of wether the key exists or not
	 */
	public function exists($key) {
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
		$description = $this->get_description($key);
		$content = $snippet->post_content;

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
		
		$snippet = $this->get_snippet($key);
		$description = $this->get_description($key);

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
				$description = $this->get_description($key);
				$select .= '<option value="'.$key.'"'.selected($selected, $key, false).'>'.htmlentities($description).'</option>';
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
				$description = $this->get_description($key);

				if (!empty($description)) {
					if ($links) {
						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.htmlentities($description).'</a>';
					}
					else {
						$description = htmlentities($description);
					}
				}
				else if (!empty($key)) {
					if ($links) {
						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.htmlentities($key).'</a>';
					}
					else {
						$description = htmlentities($key);
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
		unset($args['key'], $args['content'], $args['description'], $args['snippet'], $args['post_id']);
		
		$defaults = array(
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		if (empty($key)) { return false; }
		
		// Check the key to see if one exists, fix if so
		$key = $this->check_key(stripslashes($key));
		if (empty($key)) { return false; }
		
		$post_id = $this->get_id($key);
		
		global $current_user;
		get_currentuserinfo();
		
		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_content' => $content,
			'post_status' => 'publish',
			'author' => $current_user->user_ID,
			'post_id' => $post_id,
		);

		$post_id = wp_insert_post($snippet);
		
		if (!$post_id) { return false; }

		if (!update_post_meta($post_id, '_cfsp_description', $description)) { return false; }
		
		/*	foreach ($args as $arg_key => $arg_value) {
				if (!update_post_meta($post_id, '_cfsp_'.$arg_key, $arg_value)) { return false; }
			}*/
		
		return $key;
	}
	
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
		unset($args['key'], $args['content'], $args['description'], $args['snippet'], $args['post_id']);

		$defaults = array(
		);
		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		if (empty($key)) { return false; }

		// Check the key to see if one exists, fix if so
		$key = $this->check_key(stripslashes($key));
		if (empty($key)) { return false; }

		global $current_user;
		get_currentuserinfo();

		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_content' => $content,
			'post_status' => 'publish',
			'author' => $current_user->user_ID,
		);

		$post_id = wp_insert_post($snippet);

		if (!$post_id) { return false; }

		if (!update_post_meta($post_id, '_cfsp_description', $description)) { return false; }

	/*	foreach ($args as $arg_key => $arg_value) {
			if (!update_post_meta($post_id, '_cfsp_'.$arg_key, $arg_value)) { return false; }
		}*/

		return true;
	}
	
	/**
	 * This function takes a key, and removes that key from the database
	 *
	 * @param string $key - Key to remove
	 * @return bool - Result of the remove
	 */
	public function remove($key) {
		$key = sanitize_title($key);
		$post_id = $this->get_id($key);
		return wp_delete_post($post_id, true);
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
	
	public function get_id($key) {
		$snippet = $this->get_snippet($key);

		if ($snippet) {
			return $snippet->ID;
		}
		return 0;
	}

	
	/**
	 * This function adds the DB option with an empty array and an autoload value of no so it doesn't get auto loaded every time
	 *
	 * @return void
	 */
	public function install() {
	}

}

?>