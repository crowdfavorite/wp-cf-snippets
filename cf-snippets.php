<?php 
/*
Plugin Name: CF Snippets
Plugin URI: http://crowdfavorite.com
Description: ::TODO::
Version: 2.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

// Constants
define('CFSP_VERSION', '2.0');
define('CFSP_DIR',trailingslashit(realpath(dirname(__FILE__))));

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
		}
	}
	if (!empty($_POST['cf_action'])) {
		switch ($_POST['cf_action']) {
			case 'cfsp_new':
				cfsp_ajax_new();
				die();
				break;
			case 'cfsp_new_add':
				if (!empty($_POST['cfsp_key'])) {
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
			?>
			<div id="cfsp-popup" class="cfsp-popup">
				<div class="cfsp-popup-head">
					<span class="cfsp-popup-close">
						<a href="#close">Close</a>
					</span>
					<h2>Snippet "<?php echo $key; ?>" Deleted</h2>
				</div>
				<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
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

function cfsp_add_new($key, $description = '', $content = '') {
	if (empty($key)) { return false; }
	
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !is_a('CF_Snippet', $cf_snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$data = array(
		'key' => $key,
		'description' => $description,
		'content' => $content
	);
	$cf_snippet->add($data);
	
	// Now that we have inserted, get the row to insert into the table
	echo $cf_snippet->admin_display($key);
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