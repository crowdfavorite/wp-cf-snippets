<?php
/**
 * class CF_Snippet_Manager
 * Handles CRUD operations for snippets
 *
 * @package cf-snippets
 *
 */
class CF_Snippet_Manager extends CF_Snippet_Base {
	
	public function __construct() {
	}
	
	## Display Functions
	
	/**
	 * This function gets a specific key and will create one with data passed in if the key does not exist
	 *
	 * @param string $key - Key to get
	 * @param string $default - Data to use for the content if the key does not exist
	 * @return string - Content for the key passed in
	 */
	public function get($key, $default = '', $create = true, $args = array()) {
		// Make sure the key is in a proper format
		$key = sanitize_title($key);
		// Try to get the snippet
		$snippet = $this->get_snippet($key);
		// If we have a snippet, return it back
		if ($snippet) {
			return do_shortcode(apply_filters('cfsp-get-content', $snippet['content'], $key));
		}
		// If we didn't have a snippet, but the create option is set, allow the snippet to be created
		else if ($create && !empty($default)) {
			$defaults = array(
				'description' => ''
			);
			extract(wp_parse_args($args, $defaults), EXTR_SKIP);
			
			if (empty($description)) {
				$description = ucwords(str_replace(array('-', '_'), ' ', $key));
			}
			
			$this->add($key, $default, $description);
			return do_shortcode(apply_filters('cfsp-get-content', $default, $key));
		}
		else {
			return false;
		}
	}
	
	/**
	 * This function returns a snippet with a matching key, creates one if none are found
	 * 
	 * @param string $key Key to get
	 * @param string $default Data to use for the content if the key does not exist
	 * @return array All of the data about the snippet for the key passed in
	 */
	public function get_info($key, $default = '', $create = true, $args = array()) {
		// Make sure the key is in a proper format
		$key = sanitize_title($key);
		// Try to get the snippet
		$snippet = $this->get_snippet($key);
		// If we have a snippet, return it back
		if ($snippet) {
			$snippet = array_merge($snippet, $this->get_meta($key));
			return apply_filters('cfsp-get-info', $snippet, $key);
		}
		// If we didn't have a snippet, but the create option is set, allow the snippet to be created
		else if ($create && !empty($default)) {
			$defaults = array(
				'description' => ''
			);
			extract(wp_parse_args($args, $defaults), EXTR_SKIP);
			
			if (empty($description)) {
				$description = ucwords(str_replace(array('-', '_'), ' ', $key));
			}
			
			$this->add($key, $default, $description);
			
			$snippet = array_merge($this->get_snippet($key), $this->get_meta($key));
			return $snippet;
		}
		else {
			return false;
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
				if (strpos($meta_key, '_cfsp_') !== false) {
					$meta[$meta_key] = $meta_value;
				}
			}
		}
		return $meta;
	}
	
	/**
	 * This function checks to see if the snippet is attached to a parent or not
	 *
	 * @param string $key Key to check
	 * @return bool Result of the check
	 */
	public function has_parent($key) {
		$key = sanitize_title($key);
		$snippet = $this->get_snippet($key);
		
		if ($snippet && !empty($snippet['parent']) && $snippet['parent']) {
			return true;
		}
		return false;
	}
	
	/**
	 * This function gets all of the keys available and passes them back as an array
	 *
	 * @return array - Array of keys
	 */
	public function get_keys() {
		$snippets = $this->get_all();
		if (is_array($snippets) && !empty($snippets)) {
			$keys = array();
			foreach ($snippets as $snippet) {
				$keys[] = $snippet['key'];
			}
			return $keys;
		}
		return false;
	}
	
	/**
	 * This function gets all of the keys that have been created on a particular post
	 *
	 * @param int $post_id 
	 * @return array Keys associated with the post ID passed in
	 */
	public function get_keys_for_post($post_id) {
		if (!$post_id) { return false; }
		$snippets = new WP_Query(array(
			'post_type' => $this->post_type,
			'post_parent' => $post_id,
			'posts_per_page' => 100,
		));
		
		$data = array();
		
		global $post;
		$old_post = $post;
		if ($snippets->have_posts()) {
			while ($snippets->have_posts()) {
				$snippets->the_post();
				global $post;
				$data[] = $post->post_name;
			}
		}
		setup_postdata($old_post);
		// As of 3.4 setup_postdata does not set the global $post object.
		$post = $old_post;
		
		if (!is_array($data) || empty($data)) {
			return false;
		}
		return $data;
	}
//	
//	/**
//	 * This function returns a list of all of the keys for snippets that have a parent
//	 *
//	 * @param int $count Amount of keys to show (0 to show all)
//	 * @param int $offset Offset
//	 * @return array
//	 */
//	public function get_all_post_keys($count = 0, $offset = 0) {
//		$query = array(
//			'post_type' => $this->post_type,
//			'posts_per_page' => -1
//		);
//		if ($count && $offset) {
//			$query['offset'] = $offset;
//			$query['posts_per_page'] = $count;
//		}
//		else if ($count && !$offset) {
//			$query['posts_per_page'] = $count;
//		}
//		else if (!$count && $offset) {
//			$query['offset'] = $offset;
//			unset($query['posts_per_page']);
//		}
//		
//		add_filter('posts_where', array($this, 'get_all_post_keys_where'));
//		$snippets = new WP_Query($query);
//		remove_filter('posts_where', array($this, 'get_all_post_keys_where'));
//		
//		$data = array();
//		
//		if ($snippets->have_posts()) {
//			global $post;
//			$old_post = $post;
//			while ($snippets->have_posts()) {
//				$snippets->the_post();
//				global $post;
//				$data[] = $post->post_name;
//			}
//			setup_postdata($old_post);
//			// As of 3.4 setup_postdata does not set the global $post object.
//			$post = $old_post;
//		}
//		
//		if (!is_array($data) || empty($data)) {
//			return false;
//		}
//		return $data;
//	}
//	
	public function get_key_count() {
		add_filter('posts_fields', array($this, 'get_all_keys_fields'));
		$snippets = new WP_Query(array(
			'post_type' => $this->post_type,
			'posts_per_page' => 1,
		));
		remove_filter('posts_fields', array($this, 'get_all_keys_fields'));

//echo "<pre>|";
//var_dump($snippets);
//echo "|</pre>";
		
		if ($snippets->have_posts()) {
			return $snippets->found_posts;
		}
		return false;
	}
	
	/**
	 * Retrieve the count of Snippets that have a parent
	 *
	 * @return int|bool Count of snippets, or false if there are none
	 */
	public function get_post_key_count() {
		add_filter('posts_where', array($this, 'get_all_post_keys_where'));
		add_filter('posts_fields', array($this, 'get_all_keys_fields'));
		$snippets = new WP_Query(array(
			'post_type' => $this->post_type,
			'posts_per_page' => 1
		));
		remove_filter('posts_fields', array($this, 'get_all_keys_fields'));
		remove_filter('posts_where', array($this, 'get_all_post_keys_where'));
		
		if ($snippets->have_posts()) {
			return $snippets->found_posts;
		}
		return false;
	}
	
	/**
	 * This function adds more info to the posts_where filter so it will only show
	 * posts that have a parent
	 *
	 * @param string $where 
	 * @return string Modified where clause
	 */
	public function get_all_post_keys_where($where) {
		$where .= ' AND post_parent > 0';
		return $where;
	}
	
	/**
	 * This function limits the fields that are retrieved in the query to check how
	 * many snippets have parents
	 *
	 * @param string $fields 
	 * @return string
	 */
	public function get_all_keys_fields($fields) {
		$fields = 'ID';
		return $fields;
	}
	
	/**
	 * This function gets all of the data for all of the snippets and returns it as an array
	 *
	 * @return array - Array of content
	 */
	public function get_all() {
		$snippets = new WP_Query(array(
			'post_type' => $this->post_type,
			'orderby' => 'ID',
			'order' => 'ASC',
			'posts_per_page' => 500
		));
		
		$data = array();
		
		if ($snippets->have_posts()) {
			foreach ($snippets->posts as $snippet_post) {
				$id = $snippet_post->ID;
				$key = $snippet_post->post_name;
				$description = $title = get_the_title($snippet_post->ID);
				$content = $snippet_post->post_content;
				$parent = $snippet_post->post_parent;
				$data[] = compact('id', 'key', 'description', 'title', 'content', 'parent');
			}
		}
		
		if (!is_array($data) || empty($data)) {
			return false;
		}
		return $data;
	}
	
	/**
	 * 	This function gets a snippet (post) based on its key (name)
	 * 
	 * @param string $key key to search snippets for
	 * @return stdObj a snippet (post) object without meta (including content)
	 */
	public function get_snippet($key) {
		$key = sanitize_title($key);
		
		$snippet = new WP_Query(array(
			'post_type' => $this->post_type,
			'name' => $key,
			'posts_per_page' => -1
		));
		
		$data = array();
		
		global $post;
		$old_post = $post;
		if ($snippet->have_posts()) {
			while ($snippet->have_posts()) {
				$snippet->the_post();
				$id = get_the_ID();
				$key = $post->post_name;
				$description = $title = the_title('', '', false);
				$content = get_the_content($id);
				$parent = $post->post_parent;
				// Compile all of the data for this snippet
				$data = compact('id', 'key', 'description', 'title', 'content', 'parent');
			}
		}
		if (!empty($old_post)) {
			setup_postdata($old_post);
		}
		// As of 3.4 setup_postdata does not set the global $post object.
		$post = $old_post;
		
		if (!is_array($data) || empty($data)) {
			return false;
		}
		return $data;
	}
	
	public function get_snippet_post_by_key($key) {
		$post = get_posts(array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'name' => $key,
			'no_found_rows' => true,
			'posts_per_page' => 1)
		);
		if (!is_array($post) || empty($post)) {
			return $post;
		}
		else {
			return $post[0];
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
		
		$snippet = new WP_Query(array(
			'post_type' => $this->post_type,
			'name' => $key,
			'posts_per_page' => -1
		));
		
		if ($snippet->have_posts()) {
			return true;
		}
		return false;
	}

	## Admin Display Functions
	
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
				$key = $snippet['key'];
				$description = $snippet['title'];
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
//	public function list_display($links = true) {
//		$snippets = $this->get_all();
//		$list = '';
//		if (is_array($snippets) && !empty($snippets)) {
//			foreach ($snippets as $snippet) {
//				$key = $snippet['key'];
//				$description = $snippet['title'];
//
//				if (!empty($description)) {
//					if ($links) {
//						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.esc_html($description).'</a>';
//					}
//					else {
//						$description = esc_html($description);
//					}
//				}
//				else if (!empty($key)) {
//					if ($links) {
//						$description = '<a href="#" class="cfsp-list-link" rel="'.esc_attr($key).'">'.esc_html($key).'</a>';
//					}
//					else {
//						$description = esc_html($key);
//					}
//				}
//				
//				if (!empty($description)) {
//					$list .= '<li>'.$description.'</li>';
//				}
//			}
//		}
//		
//		if (!empty($list)) {
//			$list = '<ul class="cfsp-list">'.$list.'</ul>';
//		}
//		
//		return $list;
//	}
	
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
		extract(wp_parse_args($args, $defaults), EXTR_SKIP);

		if (empty($key)) { return false; }
		// Make sure the key is in a valid format
		$key = sanitize_title($key);
		$post_id = $this->get_id($key);

		if (!$post_id) {
			$key = $this->check_key($key);
		}
		
		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_status' => 'publish',
			'post_title' => $description,
			'post_content' => $content,
		);
		
		if ($post_id) {
			$snippet['ID'] = $post_id;
		}
		
		if (!empty($args['post_parent'])) {
			$snippet['post_parent'] = intval($args['post_parent']);
		}
		
		$post_id = wp_insert_post($snippet);
		
		if (!$post_id) { 
			return false; 
		}
		
		foreach ($args as $arg_key => $arg_value) {
			if (!update_post_meta($post_id, '_cfsp_'.$arg_key, $arg_value)) { 
				return false;	
			}
		}
		
		return true;
	}
	
	public function save_snippet_post($post_arr) {
		$post_arr = array_merge(array(
			'ID' => null,
			'post_name' => 'cfsp-new-snippet',
			'post_title' => 'New Snippet',
			'post_content' => '',
			'post_parent' => 0,
		), $post_arr);
		$post_arr['post_type'] = $this->post_type;
		$post_arr['post_status'] = 'publish';
		if (!empty($post_arr['ID'])) {
			$result = wp_update_post($post_arr);
		}
		else {
			$result = wp_insert_post($post_arr);
		}
		return $result;
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
		if ($this->user_can_admin_snippets() == false) {
			return;
		}

		// Check to make sure we don't have any variable name conflicts
		unset($args['key'], $args['content'], $args['description'], $args['snippet'], $args['post_id'], $args['mod_cap']);
		
		$defaults = array();
		extract(wp_parse_args($args, $defaults), EXTR_SKIP);

		if (empty($key)) { return false; }
		// Make sure the key is in a valid format
		$key = sanitize_title($key);
		$key = $this->check_key($key);
		
		$snippet = array(
			'post_type' => $this->post_type,
			'post_name' => $key,
			'post_status' => 'publish',
			'post_title' => $description,
			'post_content' => $content,
		);
		
		if (!empty($post_parent)) {
			$snippet['post_parent'] = intval($post_parent);
		}
		
		$post_id = wp_insert_post($snippet);
		
		if (!$post_id) { 
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
		if ($this->user_can_admin_snippets() == false) {
			return;
		}

		$key = sanitize_title($key);
		$post_id = $this->get_id($key);
		if ($post_id) {
			return wp_delete_post($post_id, true);
		}
		return false;
	}
	
	/**
	 * This function will remove the Post Parent for a particular snippet
	 *
	 * @param string $key 
	 * @return bool Result
	 */
	public function remove_from_parent($key) {
		if (empty($key)) { return false; }
		// Make sure the key is in a valid format
		$key = sanitize_title($key);
		$post_id = $this->get_id($key);
		
		if (!$post_id) { return; }
		
		$info = $this->get_info($key);
		
		$snippet = array(
			'ID'			=> $post_id,
			'post_type'		=> $this->post_type,
			'post_name'		=> $key,
			'post_status'	=> 'publish',
			'post_title'	=> $info['title'],
			'post_parent'	=> 0
		);
		
		$post_id = wp_insert_post($snippet);
		
		if (!$post_id) { 
			return false; 
		}
		return true;
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
		$i = 1;
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
			return $snippet['id'];
		}
		return 0;
	}
	
}

?>
