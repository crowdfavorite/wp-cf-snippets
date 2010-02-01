<?php 
/*
Plugin Name: CF Snippets
Plugin URI: http://crowdfavorite.com
Description: Lets admins define html snippets for use in template, content, or widgets.
Version: 2.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

// Constants
define('CFSP_VERSION', '2.0');
define('CFSP_DIR', trailingslashit(realpath(dirname(__FILE__))));
define('CFSP_DIR_URL', trailingslashit(get_bloginfo('wpurl')).trailingslashit(PLUGINDIR).trailingslashit(basename(dirname(__FILE__))));

// Includes
include('classes/snippets.class.php');
include('classes/message.class.php');

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

load_plugin_textdomain('cfsp');

## Admin Functionality

function cfsp_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfsp_admin_css':
				cfsp_admin_css();
				die();
				break;
			case 'cfsp_admin_js':
				cfsp_admin_js();
				die();
				break;
			case 'cfsp_iframe_preview':
				if (!empty($_GET['cfsp_key'])) {
					cfsp_iframe_preview($_GET['cfsp_key']);
				}
				die();
				break;
			case 'cfsp-dialog':
				cfsnip_dialog();
				die();
				break;
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfsp_new':
				cfsp_ajax_new();
				die();
				break;
			case 'cfsp_new_add':
				if (!empty($_POST['cfsp_key']) || !empty($_POST['cfsp_description'])) {
					cfsp_add_new($_POST['cfsp_key'], $_POST['cfsp_description'], $_POST['cfsp_content']);
				}
				die();
				break;
			case 'cfsp_save':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_save($_POST['cfsp_key'], $_POST['cfsp_description'], $_POST['cfsp_content']);
				}
				die();
				break;
			case 'cfsp_edit':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_edit($_POST['cfsp_key']);
				}
				die();
				break;
			case 'cfsp_preview':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_preview($_POST['cfsp_key']);
				}
				die();
				break;
			case 'cfsp_delete':
				if (!empty($_POST['cfsp_key'])) {
					if (!empty($_POST['cfsp_delete_confirm']) && $_POST['cfsp_delete_confirm'] == 'yes') {
						cfsp_ajax_delete($_POST['cfsp_key'], true);
					}
					else {
						cfsp_ajax_delete($_POST['cfsp_key'], false);
					}
				}
				die();
				break;
		}
	}
	
	// Setup the class object
	if ((!empty($_GET['page']) && $_GET['page'] == 'cf-snippets')) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
			$cf_snippet = new CF_Snippet();
		}
	}
}
add_action('init', 'cfsp_request_handler');

function cfsp_admin_css() {
	header('Content-type: text/css');
	do_action('cfsp-admin-css');
	echo file_get_contents(CFSP_DIR.'css/content.css');
	
	die();
}

function cfsp_admin_js() {
	header('Content-type: text/javascript');
	do_action('cfsp-admin-js');
	echo file_get_contents(CFSP_DIR.'js/behavior.js');
	echo file_get_contents(CFSP_DIR.'js/jquery.DOMWindow.js');
	echo file_get_contents(CFSP_DIR.'js/json2.js');
	echo file_get_contents(CFSP_DIR.'js/popup.js');
	die();
}

if (!empty($_GET['page']) && $_GET['page'] == 'cf-snippets') {
	wp_enqueue_script('jquery');
	wp_enqueue_script('cfsp-admin-js', trailingslashit(get_bloginfo('url')).'?cf_action=cfsp_admin_js', array('jquery'), CFSP_VERSION);
	wp_enqueue_style('cfsp-admin-css',	trailingslashit(get_bloginfo('url')).'?cf_action=cfsp_admin_css', array(), CFSP_VERSION, 'screen');
}

function cfsp_admin_menu() {
	add_options_page(
		__('CF Snippets', 'cfsp'),
		__('CF Snippets', 'cfsp'),
		10,
		'cf-snippets',
		'cfsp_options'
	);
}
add_action('admin_menu', 'cfsp_admin_menu');

function cfsp_options() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	?>
	<div class="wrap">
		<?php echo screen_icon().'<h2>CF Snippets</h2>'; ?>
		<p><a href="#instructions" class="show-hide"><?php _e('Show Instructions', 'cfsp'); ?></a> &nbsp;|&nbsp; <a href="'.get_bloginfo('wpurl').'/wp-admin/widgets.php"><?php _e('Edit Widgets &raquo;', 'cfsp'); ?></a></p>
		<div id="instructions" style="">
			<p><?php _e('Paste in HTML content for a snippet and give it a name. The name will be automatically "sanitized:" lowercased and all spaces converted to dashes.', 'cfsp'); ?></p>
			<p><?php _e('To insert a snippet in your template, type <code>&lt;?php cfsp_content(\'my-snippet-name\'); ?></code><br /> Use the shortcode syntax: <code>[cfsp name="my-snippet-name"]</code> in post or page content to insert your snippet there.', 'cfsp'); ?></p>
			<p><?php _e('Or use snippet widgets wherever widgets can be used.', 'cfsp'); ?></p>
			<p><?php _e('To access files in your current theme template directory <em>from within a snippet</em>, type <code>{cfsp_template_url}</code>. That will be replaced with, for example, <code>http://example.com/wordpress/wp-content/themes/mytheme/</code>.', 'cfsp'); ?></p>
		</div>
		
		<?php
		$keys = $cf_snippet->get_keys();
		if (is_array($keys) && !empty($keys)) {
		?>
		<table id="cfsp-display" class="widefat">
			<thead>
				<tr>
					<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
					<th><?php _e('Description', 'cfsp'); ?></th>
					<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($keys as $key) {
					echo $cf_snippet->admin_display($key);
				}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
					<th><?php _e('Description', 'cfsp'); ?></th>
					<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
				</tr>
			</tfoot>
		</table>
		<p>
			<input type="button" class="button-primary cfsp-new-button" value="Add New Snippet" />
		</p>
		<?php
		}
		else {
			echo 'None exist';
		}
}

function cfsp_ajax_new() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	?>
	<div id="cfsp-popup" class="cfsp-popup">
		<div class="cfsp-popup-head">
			<span class="cfsp-popup-close">
				<a href="#close">Close</a>
			</span>
			<h2>Create New Snippet:</h2>
		</div>
		<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
			<?php echo $cf_snippet->add_display(); ?>
		</div>
	</div>
	<?php
	die();
}

function cfsp_ajax_edit($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close">Close</a>
				</span>
				<h2>Snippet: "<?php echo $key; ?>"</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<?php echo $cf_snippet->edit($key); ?>
			</div>
		</div>
		<?php
	}
	else {
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close">Close</a>
				</span>
				<h2>Error</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p>Whoops! No Key Found, try again.</p>
			</div>
		</div>
		<?php
	}
	die();
}

function cfsp_ajax_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close">Close</a>
				</span>
				<h2>Snippet: "<?php echo $key; ?>"</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<iframe src ="index.php?cf_action=cfsp_iframe_preview&cfsp_key=<?php echo $key; ?>" width="100%" height="300">
				  <p>Your browser does not support iframes.</p>
				</iframe>
				<p>
					<input type="button" class="button cfsp-popup-cancel" value="Close" />
				</p>
			</div>
		</div>
		<?php
	}
	else {
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close">Close</a>
				</span>
				<h2>Error</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p>Whoops! No Key Found, try again.</p>
			</div>
		</div>
		<?php
	}
	die();
}

function cfsp_ajax_delete($key, $confirm = false) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		// If the delete has been confirmed, remove the key and return
		if ($confirm) { 
			$cf_snippet->remove($key); 
		}
		else {
			?>
			<div id="cfsp-popup" class="cfsp-popup">
				<div class="cfsp-popup-head">
					<span class="cfsp-popup-close">
						<a href="#close">Close</a>
					</span>
					<h2>Are you sure you want to delete the "<?php echo $key; ?>" snippet?</h2>
				</div>
				<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
					<iframe src ="index.php?cf_action=cfsp_iframe_preview&cfsp_key=<?php echo $key; ?>" width="100%" height="300">
					  <p>Your browser does not support iframes.</p>
					</iframe>
					<p>
						<input type="hidden" id="cfsp-key" value="<?php echo esc_attr($key); ?>" />
						<input type="hidden" id="cfsp-delete-confirm" value="yes" />
						<input type="button" class="button-primary cfsp-popup-delete" value="Delete" />
						<input type="button" class="button cfsp-popup-cancel" value="Cancel" />
					</p>
				</div>
			</div>
			<?php
		}
	}
	else {
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close">Close</a>
				</span>
				<h2>Error</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p>Whoops! No Key Found, try again.</p>
			</div>
		</div>
		<?php
	}
	die();
}

function cfsp_add_new($key = '', $description = '', $content = '') {
	if (empty($key)) {
		$key = $description;
		if (strlen($key) > 20) {
			$key = substr($key, 0, 20);
		}
		$key = sanitize_title($key);
	}
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);
	
	$new_key = $cf_snippet->add($key, $content, $description);
	// Now that we have inserted, get the row to insert into the table
	echo $cf_snippet->admin_display($new_key);
}

function cfsp_save($key, $description = '', $content = '') {
	if (empty($key)) { return false; }
	
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$cf_snippet->save($key, $content, $description);
}

function cfsp_iframe_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		echo $cf_snippet->get($key);
	}
}

## Display Functionality

function cfsp_content($key, $default = false, $create = true) {
	echo cfsp_get_content($key, $default, $create);
}

function cfsp_get_content($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsp_shortcode($attrs, $content=null) {
	if (is_array($attrs)) {
		$key = '';
		if (!empty($attrs['name'])) {
			$key = $attrs['name'];
		}
		else if (!empty($attrs['key'])) {
			$key = $attrs['key'];
		}
		
		if (empty($key)) { return ''; }
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		return $cf_snippet->get($key, false, false);
	}
	return '';
}
add_shortcode('cfsp', 'cfsp_shortcode');

## Deprecated Display Functionality

function cfsnip_get_snippets() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get_all();
}

function cfsnip_snippet($key, $default = false, $create = true) {
	echo cfsnip_get_snippet($key, $default, $create);
}

function cfsnip_snippet_content($key, $default = false, $create = true) {
	echo cfsnip_get_snippet_content($key, $default, $create);
}

function cfsnip_get_snippet($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsnip_get_snippet_content($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsnip_filter_content($content, $key) {
	return str_replace(array('{cfsnip_template_url}', '{cfsp_template_url}'), get_bloginfo('template_url'), $content);
}
add_filter('cfsp-get-content', 'cfsnip_filter_content', 10, 2);

function cfsnip_snippet_exists($key) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->exists($key);
}

function cfsnip_handle_shortcode($attrs, $content=null) {
	if (is_array($attrs) && !empty($attrs['name'])) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		return $cf_snippet->get($attrs['name'], false, false);
	}
	return '';
}
add_shortcode('cfsnip', 'cfsnip_handle_shortcode');

## Widget Functionality

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class cfsnip_Widget extends WP_Widget {
	function cfsnip_Widget() {
		$widget_ops = array('classname' => 'cfsnip-widget', 'description' => 'Widget for displaying selected CF Snippets (2.0 version)');
		$this->WP_Widget('cfsnip-widget', 'CF Snippets', $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		// Get the snippet content
		$content = $cf_snippet->get($instance['list_key']);
		// If we don't have anything to display, no need to proceed
		if (empty($content)) { return; }
		$title = esc_attr($instance['title']);
		
		echo $before_widget;
		if (!empty($title)) {
			echo $before_title . $title . $after_title;
		}
		echo $content;
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_key'] = strip_tags($new_instance['list_key']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'list_key' => ''));
		
		$title = esc_attr($instance['title']);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		$select = $cf_snippet->select_display($instance['list_key']);
		
		if (!empty($select)) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cfsp'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('list_key'); ?>"><?php _e('Snippet: ', 'cfsp'); ?></label>
				<select id="<?php echo $this->get_field_id('list_key'); ?>" name="<?php echo $this->get_field_name('list_key'); ?>" class="widefat">
					<option value="0"><?php _e('--Select Snippet--', 'cfsp'); ?></option>
					<?php echo $select; ?>
				</select>
			</p>
			<p>
				<a href="<?php bloginfo('wpurl') ?>/wp-admin/options-general.php?page=cf-snippets"><?php _e('Edit Snippets','cfsp') ?></a>
			</p>
			
			<?php
		}
		else {
			?>
			<p>
				<?php _e('No Snippets have been setup.  Please <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-snippets">setup a snippet</a> before proceeding.', 'cfsp'); ?>
			</p>
			<?php
		}
	}
}
add_action('widgets_init', create_function('', "register_widget('cfsnip_Widget');"));

## TinyMCE Functionality

function cfsnip_dialog() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	$list = $cf_snippet->list_display(true);
	
?>
<html>
	<head>
		<title><?php _e('Select Snippet', 'cfsp'); ?></title>
		<script type="text/javascript" src="<?php bloginfo('url'); ?>/wp-includes/js/jquery/jquery.js"></script>
		<script type="text/javascript" src="<?php bloginfo('url'); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
		<script type='text/javascript' src='<?php bloginfo('url'); ?>/wp-includes/js/quicktags.js'></script>
		<script type="text/javascript">
			;(function($) {
				$(function() {
					$(".cfsp-list-link").live('click', function() {
						var key = $(this).attr('rel');
						cfsp_insert(key);
					});
				});
			})(jQuery);
		
			function cfsp_insert(key) {
				tinyMCEPopup.execCommand("mceBeginUndoLevel");
				tinyMCEPopup.execCommand('mceInsertContent', false, '[cfsp key="'+key+'"]');
				tinyMCEPopup.execCommand("mceEndUndoLevel");
				tinyMCEPopup.close();
				return false;
			}
		</script>
		<style type="text/css">
			.cfsp-list {
				padding-left:10px;
			}
		</style>
	</head>
	<body id="cfsnippet">
		<?php
		if (!empty($list)) {
			echo '<p>'.__('Click on the Snippet below to add the shortcode to the content of the post.', 'cfsp').'</p>';
			echo '<p>'.$list.'</p>';
		}
		else {
			echo '<p>'.__('No Snippets have been setup.  Please <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-snippets">setup a snippet</a> before proceeding.', 'cfsp').'</p>';
		}
		?>
	</body>
</html>
<?php
}

function cfsnip_addtinymce() {
	// Don't bother doing this stuff if the current user lacks permissions
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) { return; }

	// Add only in Rich Editor mode
	if (get_user_option('rich_editing') == 'true') {
		add_filter("mce_external_plugins", "add_cfsnip_tinymce_plugin");
		add_filter('mce_buttons', 'register_cfsnip_button');
	}
}
add_action('init', 'cfsnip_addtinymce');
 
function register_cfsnip_button($buttons) {
	array_push($buttons, '|', "cfsnip_Btn");
	return $buttons;
}

function add_cfsnip_tinymce_plugin($plugin_array) {
	$plugin_array['cfsnippets'] = CFSP_DIR_URL.'js/editor_plugin.js';
	return $plugin_array;
}

## Auxillary Functionality

/**
 * JSON ENCODE and DECODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode & json_decode
 * Uses the Pear Class Services_JSON - http://pear.php.net/package/Services_JSON
 */ 
if (!function_exists('json_encode') && !class_exists('Services_JSON')) {
	require_once('classes/external/JSON.php');
}	

/**
 * cfsp_json_encode
 *
 * @param array/object $json 
 * @return string json
 */
function cfsp_json_encode($data) {
	if (function_exists('json_encode')) {
		return json_encode($data);
	}
	else {
		global $cfsp_json_object;
		if (!($cfsp_json_object instanceof Services_JSON)) {
			$cfsp_json_object = new Services_JSON();
		}
		return $cfsp_json_object->encode($data);
	}
}

/**
 * cfsp_json_decode
 *
 * @param string $json 
 * @param bool $array - toggle true to return array, false to return object  
 * @return array/object
 */
function cfsp_json_decode($json,$array) {
	if (function_exists('json_decode')) {
		return json_decode($json,$array);
	}
	else {
		global $cfsp_json_object;
		if (!($cfsp_json_object instanceof Services_JSON)) {
			$cfsp_json_object = new Services_JSON();
		}
		$cfsp_json_object->use = $array ? SERVICES_JSON_LOOSE_TYPE : 0;
		return $cfsp_json_object->decode($json);
	}
}


?>