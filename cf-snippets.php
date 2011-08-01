<?php 
/*
Plugin Name: CF Snippets
Plugin URI: http://crowdfavorite.com
Description: Provides admin level users the ability to define html snippets for use in templates, content, or widgets.
Version: 2.1.5
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1'); ini_set('error_reporting', E_ALL);

// Constants
define('CFSP_VERSION', '2.1.5');
define('CFSP_DIR', plugin_dir_path(__FILE__));
//plugin_dir_url seems to be broken for including in theme files
if (file_exists(trailingslashit(get_template_directory()).'plugins/'.basename(dirname(__FILE__)))) {
	define('CFSP_DIR_URL', trailingslashit(trailingslashit(get_bloginfo('template_url')).'plugins/'.basename(dirname(__FILE__))));
}
else {
	define('CFSP_DIR_URL', trailingslashit(plugins_url(basename(dirname(__FILE__)))));	
}
define('CFSP_SHOW_POST_COUNT', 10);

// Includes
include('classes/snippets.class.php');
include('classes/message.class.php');

// Include the Deprecated File to update the old Items
include ('deprecated.php');

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR','wp-content/plugins');
}

load_plugin_textdomain('cfsp');


## Admin Functionality

function cfsp_request_handler() {
	if (!empty($_GET['cf_action'])) {
		switch ($_GET['cf_action']) {
			case 'cfsp_iframe_preview':
				if (!empty($_GET['cfsp_key'])) {
					cfsp_iframe_preview(stripslashes($_GET['cfsp_key']));
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
					cfsp_add_new(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				die();
				break;
			case 'cfsp_save':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_save(stripslashes($_POST['cfsp_key']), stripslashes($_POST['cfsp_description']), stripslashes($_POST['cfsp_content']));
				}
				die();
				break;
			case 'cfsp_edit':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_edit(stripslashes($_POST['cfsp_key']));
				}
				die();
				break;
			case 'cfsp_preview':
				if (!empty($_POST['cfsp_key'])) {
					cfsp_ajax_preview(stripslashes($_POST['cfsp_key']));
				}
				die();
				break;
			case 'cfsp_delete':
				if (!empty($_POST['cfsp_key'])) {
					if (!empty($_POST['cfsp_delete_confirm']) && $_POST['cfsp_delete_confirm'] == 'yes') {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), true);
					}
					else {
						cfsp_ajax_delete(stripslashes($_POST['cfsp_key']), false);
					}
				}
				die();
				break;
			case 'cfsp_post_items_paged':
				if (!empty($_POST['cfsp_page'])) {
					cfsp_ajax_post_items_paged(stripslashes($_POST['cfsp_page']));
				}
				die();
				break;
		}
	}
	
	// Setup the class object
	if (!empty($_GET['page']) && strpos($_GET['page'], 'cf-snippets') !== false) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
	}
}
add_action('init', 'cfsp_request_handler');

function cfsp_resources() {
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
			case 'cfsp_post_css':
				cfsp_post_css();
				die();
				break;
			case 'cfsp_post_js':
				cfsp_post_js();
				die();
				break;
		}
	}
}
add_action('init', 'cfsp_resources', 1);

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

function cfsp_post_css() {
	header('Content-type: text/css');
	do_action('cfsp-post-css');
	echo file_get_contents(CFSP_DIR.'css/post.css');
	die();
}

function cfsp_post_js() {
	header('Content-type: text/javascript');
	do_action('cfsp-post-js');
	echo file_get_contents(CFSP_DIR.'js/post.js');
	die();
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	$table_display = '';
	$message_display = '';
	$post_table_display = '';
	$post_message_display = '';
	$count = 0;
	$post_count = 0;
	$show_post_count = CFSP_SHOW_POST_COUNT;
	$total_post_count = 0;
	$total_post_page_count = 0;
	
	$table_content = '';
	$post_table_content = '';

	$keys = $cf_snippet->get_keys();
	if (is_array($keys) && !empty($keys)) {
		foreach ($keys as $key) {
			$meta = $cf_snippet->get_meta($key);
			if ($meta['post_id']) {
				$total_post_count++;
				if ($post_count >= $show_post_count) { continue; }
				$post_table_content .= $cf_snippet->admin_display($key);
				$post_count++;
				$post_message_display = ' style="display:none;"';
			}
			else {
				$table_content .= $cf_snippet->admin_display($key);
				$count++;
				$message_display = ' style="display:none;"';
			}
		}
	}
	else {
		$table_display = ' style="display:none;"';
		$post_table_display = ' style="display:none;"';
	}
	
	$total_post_page_count = ceil($total_post_count/CFSP_SHOW_POST_COUNT);
	
	?>
	<div class="wrap">
		<?php echo screen_icon().'<h2>CF Snippets</h2>'; ?>
		<p>
			<a href="#" rel="cfsp-instructions" class="cfsp-instructions"><span class="cfsp-instructions-show"><?php _e('Show', 'cfsp'); ?></span><span class="cfsp-instructions-hide" style="display:none;"><?php _e('Hide', 'cfsp'); ?></span><?php _e(' Instructions', 'cfsp'); ?></a> &nbsp;|&nbsp; <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('Edit Widgets &raquo;', 'cfsp'); ?></a></p>
		<div id="cfsp-instructions" style="display:none;">
			<p><?php _e('Paste in HTML content for a snippet and give it a name. The name will be automatically "sanitized:" lowercased and all spaces converted to dashes.', 'cfsp'); ?></p>
			<p><?php _e('To insert a snippet in your template, type <code>&lt;?php cfsp_content(\'my-snippet-name\'); ?></code><br /> Use the shortcode syntax: <code>[cfsp name="my-snippet-name"]</code> in post or page content to insert your snippet there.', 'cfsp'); ?></p>
			<p><?php _e('Or use snippet widgets wherever widgets can be used.', 'cfsp'); ?></p>
			<p><?php _e('To access files in your current theme template directory <em>from within a snippet</em>, type <code>{cfsp_template_url}</code>. That will be replaced with, for example, ', 'cfsp'); ?><code><?php echo get_template_directory_uri(); ?></code>.</p>
		</div>
		<?php if ($count == 0 && $post_count == 0) { ?>
		<div class="cfsp-message">
			<p>
				<?php _e('No Snippets have been created.  Click the "Add New Snippet" button to proceed', 'cfsp'); ?>
			</p>
		</div>
		<?php } ?>
		<table id="cfsp-display" class="widefat"<?php echo $table_display; ?>>
			<thead>
				<tr>
					<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
					<th><?php _e('Description', 'cfsp'); ?></th>
					<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $table_content; ?>
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
		<?php if ($post_count > 0) { ?>
		<h3><?php _e('Post Created Snippets', 'cfsp'); ?></h3>
		<table id="cfsp-post-display" class="widefat"<?php echo $post_table_display; ?>>
			<thead>
				<tr>
					<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
					<th><?php _e('Description', 'cfsp'); ?></th>
					<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $post_table_content; ?>
			</tbody>
			<tfoot>
				<tr>
					<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
					<th><?php _e('Description', 'cfsp'); ?></th>
					<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
				</tr>
				<?php if ($total_post_page_count > 1) { ?>
				<tr>
					<td style="text-align:left;">
					</td>
					<td style="text-align:center">
						<?php echo __('Page 1 of ', 'cfsp').$total_post_page_count; ?>
					</td>
					<td style="text-align:right;">
						<button class="cfsp-post-next button"><?php _e('Next Page of CF Snippets', 'cfsp'); ?> &raquo;</button>
						<input type="hidden" id="cfsp-post-page-displayed" value="1" />
					</td>
				</tr>
				<?php } ?>
			</tfoot>
		</table>
		<?php } ?>
	</div>
	<?php
}

function cfsp_ajax_post_items_paged($page) {
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	
		$ids = split(',',$ids_displayed);
		$keys = cfsp_get_post_snippet_keys();
		$offset = (CFSP_SHOW_POST_COUNT*($page-1));
		$post_table_content = '';
		$total_pages = ceil(count($keys)/CFSP_SHOW_POST_COUNT);

		if (is_array($keys) && !empty($keys)) {
			for ($i = $offset; $i < $offset+CFSP_SHOW_POST_COUNT; $i++) {
				$post_table_content .= $cf_snippet->admin_display($keys[$i]);
			}
		}
	
		?>
		<thead>
			<tr>
				<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
				<th><?php _e('Description', 'cfsp'); ?></th>
				<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo $post_table_content; ?>
		</tbody>
		<tfoot>
			<tr>
				<th width="20%"><?php _e('Snippet Key', 'cfsp'); ?></th>
				<th><?php _e('Description', 'cfsp'); ?></th>
				<th width="20%" style="text-align:center;"><?php _e('Actions', 'cfsp'); ?></th>
			</tr>
			<tr>
				<td style="text-align:left;">
					<?php if ($page > 1) { ?>
					<button class="cfsp-post-prev button">&laquo; <?php _e('Previous Page of CF Snippets', 'cfsp'); ?></button>
					<?php }?>
				</td>
				<td style="text-align:center">
					<?php echo __('Page ', 'cfsp').$page.__(' of ', 'cfsp').$total_pages; ?>
				</td>
				<td style="text-align:right;">
					<?php if ($page < $total_pages) { ?>
					<button class="cfsp-post-next button"><?php _e('Next Page of CF Snippets', 'cfsp'); ?> &raquo;</button>
					 <?php }?>
					<input type="hidden" id="cfsp-post-page-displayed" value="<?php echo $page; ?>" />
				</td>
			</tr>
		</tfoot>
		<?php
	}
}

function cfsp_get_post_snippet_keys() {
	$snippet_keys = array();
	
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();

		$keys = $cf_snippet->get_keys();
		if (is_array($keys) && !empty($keys)) {
			foreach ($keys as $key) {
				$meta = $cf_snippet->get_meta($key);
				if ($meta['post_id']) {
					$snippet_keys[] = $key;
				}
			}
		}
	}
	return $snippet_keys;
}

function cfsp_ajax_new() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	?>
	<div id="cfsp-popup" class="cfsp-popup">
		<div class="cfsp-popup-head">
			<span class="cfsp-popup-close">
				<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
			</span>
			<h2><?php _e('Create New Snippet:', 'cfsp'); ?></h2>
		</div>
		<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
			<div class="cfsp-popup-error" style="display:none">
				<p><strong><?php _e('Error: ', 'cfsp'); ?></strong><?php _e('A new snippet requires either a key or description, please fill one of these fields', 'cfsp'); ?></p>
			</div>
			<?php echo $cf_snippet->add_display(); ?>
		</div>
	</div>
	<?php
	die();
}

function cfsp_ajax_edit($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
				</span>
				<h2><?php _e('Snippet: ', 'cfsp'); ?>"<?php echo $key; ?>"</h2>
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
					<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
				</span>
				<h2><?php _e('Error', 'cfsp'); ?></h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p><?php _e('Whoops! No Key Found, try again.', 'cfsp'); ?></p>
			</div>
		</div>
		<?php
	}
	die();
}

function cfsp_ajax_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		?>
		<div id="cfsp-popup" class="cfsp-popup">
			<div class="cfsp-popup-head">
				<span class="cfsp-popup-close">
					<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
				</span>
				<h2><?php _e('Snippet: ', 'cfsp'); ?>"<?php echo $key; ?>"</h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<iframe src ="index.php?cf_action=cfsp_iframe_preview&cfsp_key=<?php echo $key; ?>" width="100%" height="300">
				  <p><?php _e('Your browser does not support iframes.', 'cfsp'); ?></p>
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
					<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
				</span>
				<h2><?php _e('Error', 'cfsp'); ?></h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p><?php _e('Whoops! No Key Found, try again.', 'cfsp'); ?></p>
			</div>
		</div>
		<?php
	}
	die();
}

function cfsp_ajax_delete($key, $confirm = false) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
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
						<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
					</span>
					<h2><?php _e('Are you sure you want to delete the "', 'cfsp'); echo $key; _e('" snippet?', 'cfsp'); ?></h2>
				</div>
				<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
					<iframe src ="index.php?cf_action=cfsp_iframe_preview&cfsp_key=<?php echo $key; ?>" width="100%" height="300">
					  <p><?php _e('Your browser does not support iframes.', 'cfsp'); ?></p>
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
					<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
				</span>
				<h2><?php _e('Error', 'cfsp'); ?></h2>
			</div>
			<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
				<p><?php _e('Whoops! No Key Found, try again.', 'cfsp'); ?></p>
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}

	// Make sure the key is a valid key
	$key = sanitize_title($key);

	$cf_snippet->save($key, $content, $description);
}

function cfsp_iframe_preview($key) {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	
	if (!empty($key) && $cf_snippet->exists($key)) { 
		echo $cf_snippet->get($key);
	}
}

## Post Functionality

function cfsp_post_admin_head() {
	// Get the post types so we can add snippets to all needed
	$post_types = get_post_types();
	$post_type_excludes = apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item'));
	
	if (is_array($post_types) && !empty($post_types)) {
		foreach ($post_types as $type) {
			if (!in_array($type, $post_type_excludes)) {
				add_meta_box('cfsp', __('CF Snippets', 'cfsp'), 'cfsp_post_edit', $type, 'advanced', 'high');
			}
		}
	}
}

function cfsp_post_edit() {
	global $post;
	$keys = get_post_meta($post->ID, '_cfsp-keys', true);
	$cf_snippet = new CF_Snippet();
	?>
	<div id="cfsp-description">
		<p>
			<?php _e('The CF Snippets plugin adds the ability to create new CF Snippets on the fly for each post. These CF Snippets can be reused anywhere on the site that the code is needed. Any changes to these snippets will be lost unless this post is saved. To delete a snippet completely, go to the CF Snippets settings screen and click the Delete button on the snippet to be removed.  Clicking the remove button on a snippet on this screen will only remove it from this post.', 'cfsp'); ?>
		</p>
	</div>
	<div id="cfsp-current">
		<?php
		if (is_array($keys) && !empty($keys)) {
			foreach ($keys as $key) {
				if (!$cf_snippet->exists($key)) { continue; }
				$item = str_replace('cfsp-'.$post_id.'-', '', $key);
				?>
				<div id="cfsp-item-<?php echo esc_attr($item); ?>" class="cfsp-item">
					<div id="cfsp-title-<?php echo esc_attr($item); ?>" class="cfsp-title">
						<span class="cfsp-name"><?php echo $key; ?></span>
						<span class="cfsp-add-content"><button id="cfsp-add-content-link-<?php echo esc_attr($item); ?>" class="button cfsp-add-content-link"><?php _e('Add to Content', 'cfsp'); ?></button></span>
						<span class="cfsp-remove"><button id="cfsp-remove-link-<?php echo esc_attr($item); ?>" class="button cfsp-remove-link"><?php _e('Remove Snippet from this Post', 'cfsp'); ?></button></span>
						<span class="cfsp-hide"><button id="cfsp-hide-link-<?php echo esc_attr($item); ?>" class="button cfsp-hide-link" style="display:none;"><?php _e('Hide Snippet', 'cfsp'); ?></button><button id="cfsp-show-link-<?php echo esc_attr($item); ?>" class="button cfsp-show-link"><?php _e('Show Snippet', 'cfsp'); ?></button></span>
					</div>
					<div id="cfsp-content-<?php echo esc_attr($item); ?>" class="cfsp-content" style="display:none;">
						<textarea id="<?php echo esc_attr($item); ?>" name="cfsp[<?php echo esc_attr($item); ?>][content]" class="cfsp-content-input widefat" rows="10"><?php echo htmlentities($cf_snippet->get_edit_content($key, false, false)); ?></textarea>
					</div>
					<input type="hidden" name="cfsp[<?php echo esc_attr($item); ?>][name]" id="cfsp-name-<?php echo esc_attr($item); ?>" value="<?php echo esc_attr($key); ?>" />
					<input type="hidden" name="cfsp[<?php echo esc_attr($item); ?>][postid]" id="cfsp-postid-<?php echo esc_attr($item); ?>" value="<?php echo esc_attr($post_id); ?>" />
					<input type="hidden" name="cfsp[<?php echo esc_attr($item); ?>][id]" id="cfsp-id-<?php echo esc_attr($item); ?>" value="<?php echo esc_attr($item); ?>" />
				</div>
				<?php
			}
		}
		?>
	</div>
	<div class="cfsp-add">
		<button id="cfsp-add-new" class="button"><?php _e('Add New Snippet', 'cfsp'); ?></button>
	</div>
	<div id="cfsp-new-item-default" style="display:none">
		<div id="cfsp-item-###SECTION###" class="cfsp-item">
			<div id="cfsp-title-###SECTION###" class="cfsp-title">
				<span class="cfsp-name">###SECTIONNAME###</span>
				<span class="cfsp-add-content"><button id="cfsp-add-content-link-###SECTION###" class="button cfsp-add-content-link"><?php _e('Add to Content', 'cfsp'); ?></button></span>
				<span class="cfsp-remove"><button id="cfsp-remove-link-###SECTION###" class="button cfsp-remove-link"><?php _e('Remove Snippet from this Post', 'cfsp'); ?></button></span>
				<span class="cfsp-hide"><button id="cfsp-hide-link-###SECTION###" class="button cfsp-hide-link"><?php _e('Hide Snippet', 'cfsp'); ?></button><button id="cfsp-show-link-###SECTION###" class="button cfsp-show-link" style="display:none;"><?php _e('Show Snippet', 'cfsp'); ?></button></span>
			</div>
			<div id="cfsp-content-###SECTION###" class="cfsp-content">
				<textarea id="###SECTION###" name="cfsp[###SECTION###][content]" class="cfsp-content-input widefat" rows="10"></textarea>
			</div>
			<input type="hidden" name="cfsp[###SECTION###][name]" id="cfsp-name-###SECTION###" value="###SECTIONNAME###" />
			<input type="hidden" name="cfsp[###SECTION###][postid]" id="cfsp-postid-###SECTION###" value="###POSTID###" />
			<input type="hidden" name="cfsp[###SECTION###][id]" id="cfsp-id-###SECTION###" value="###SECTION###" />
		</div>
	</div>
	<?php
}

function cfsp_save_post($post_id, $post) {
	$post_type_excludes = apply_filters('cfsp_post_type_excludes', array('revision', 'attachment', 'safecss', 'nav_menu_item'));
	if ($post->post_status == 'inherit' || in_array($post->post_type, $post_type_excludes)) { return; }
	if (!empty($_POST) && is_array($_POST) && !empty($_POST['cfsp']) && is_array($_POST['cfsp'])) {
		unset($_POST['cfsp']['###SECTION###']);
		
		$postkeys = array();

		foreach ($_POST['cfsp'] as $id => $item) {
			$name = $item['name'];
			$content = $item['content'];
			if (strpos($id, 'cfsp-'.$post_id.'-') === false) {
				$key = 'cfsp-'.$post_id.'-'.$id;
			}
			else {
				$key = $id;
			}
			
			
			// Make sure the key is a valid key
			$key = sanitize_title($key);

			$cf_snippet = new CF_Snippet();
			
			$args = array(
				'post_id' => $post_id,
			);
			
			if ($cf_snippet->check_key(stripslashes($key))) {
				$description = 'Post Snippet created for Post ID: '.$post_id.' with a unique ID of: '.$id;
				$cf_snippet->save($key, $content, $description, $args);
			}
			else {
				$description = 'Post Snippet created for Post ID: '.$post_id.' with a unique ID of: '.$id;
				$key = $cf_snippet->add($key, $content, $description, $args);
			}
			
			if (!in_array($key, $postkeys)) {
				$postkeys[] = $key;
			}
		}

		update_post_meta($post_id, '_cfsp-keys', $postkeys);
	}
}
add_action('save_post', 'cfsp_save_post', 10, 2);

## JS/CSS Addition

// Add the JS/CSS to the CF Snippets Settings Page
if (!empty($_GET['page']) && strpos($_GET['page'], 'cf-snippets') !== false) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('cfsp-admin-js', admin_url('?cf_action=cfsp_admin_js'), array('jquery'), CFSP_VERSION);
	wp_enqueue_style('cfsp-admin-css', admin_url('?cf_action=cfsp_admin_css'), array(), CFSP_VERSION, 'screen');
}
// Add the JS/CSS to the Post New/Post Edit screens
if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false) {
	add_action('admin_head', 'cfsp_post_admin_head');
	wp_enqueue_script('jquery');
	wp_enqueue_script('cfsp-post-js', admin_url('?cf_action=cfsp_post_js'), array('jquery'), CFSP_VERSION);
	wp_enqueue_style('cfsp-post-css', admin_url('?cf_action=cfsp_post_css'), array(), CFSP_VERSION, 'screen');
}

## Display Functionality

function cfsp_get_snippet_info($key, $default = false, $create = true, $args = array()) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get_info($key, $default, $create, $args);
}

function cfsp_content($key, $default = false, $create = true, $args = array()) {
	echo cfsp_get_content($key, $default, $create, $args);
}

function cfsp_get_content($key, $default = false, $create = true, $args = array()) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create, $args);
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
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
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
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get_all();
}

function cfsnip_snippet($key, $default = false, $create = true) {
	echo cfsnip_get_snippet_content($key, $default, $create);
}

function cfsnip_snippet_content($key, $default = false, $create = true) {
	echo cfsnip_get_snippet_content($key, $default, $create);
}

function cfsnip_get_snippet($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get_info($key, $default, $create);
}

function cfsnip_get_snippet_content($key, $default = false, $create = true) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->get($key, $default, $create);
}

function cfsnip_filter_content($content, $key) {
	return str_replace(array('{cfsnip_template_url}', '{cfsp_template_url}'), get_stylesheet_directory_uri(), $content);
}
add_filter('cfsp-get-content', 'cfsnip_filter_content', 10, 2);

function cfsnip_snippet_exists($key) {
	if (empty($key)) { return ''; }
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	return $cf_snippet->exists($key);
}

function cfsnip_handle_shortcode($attrs, $content=null) {
	if (is_array($attrs) && !empty($attrs['name'])) {
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
			$cf_snippet = new CF_Snippet();
		}
		return $cf_snippet->get($attrs['name'], false, false);
	}
	return '';
}
add_shortcode('cfsnip', 'cfsnip_handle_shortcode');

## Widget Functionality

class cfsnip_Widget extends WP_Widget {
	function cfsnip_Widget() {
		$widget_ops = array('classname' => 'cfsnip-widget', 'description' => 'Widget for displaying selected CF Snippets (2.0 version)');
		$this->WP_Widget('cfsnip-widget', 'CF Snippets', $widget_ops);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
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

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_key'] = strip_tags($new_instance['list_key']);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array('title' => '', 'list_key' => ''));
		
		$title = esc_attr($instance['title']);
		global $cf_snippet;
		if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
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
				<a href="<?php echo admin_url('options-general.php?page=cf-snippets'); ?>"><?php _e('Edit Snippets','cfsp') ?></a>
			</p>
			
			<?php
		}
		else {
			?>
			<p>
				<?php _e('No Snippets have been setup.  Please <a href="'.admin_url('options-general.php?page=cf-snippets').'">setup a snippet</a> before proceeding.', 'cfsp'); ?>
			</p>
			<?php
		}
	}
}
add_action('widgets_init', create_function('', "register_widget('cfsnip_Widget');"));

## TinyMCE Functionality

function cfsnip_dialog() {
	global $cf_snippet;
	if (class_exists('CF_Snippet') && !($cf_snippet instanceof CF_Snippet)) {
		$cf_snippet = new CF_Snippet();
	}
	$list = $cf_snippet->list_display(true);
?>
<html>
	<head>
		<title><?php _e('Select Snippet', 'cfsp'); ?></title>
		<script type="text/javascript" src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
		<script type="text/javascript" src="<?php echo includes_url('js/tinymce/tiny_mce_popup.js'); ?>"></script>
		<script type='text/javascript' src='<?php echo includes_url('js/quicktags.js'); ?>'></script>
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
			echo '<p>'.__('No Snippets have been setup.  Please setup a snippet before proceeding.', 'cfsp').'</p>';
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


## CF Readme Addition

if (function_exists('cfreadme_enqueue')) {
	function cfsp_add_readme() {
		cfreadme_enqueue('cf-snippets', 'cfsp_readme');
	}
	add_action('admin_init', 'cfsp_add_readme');
	
	function cfsp_readme() {
		$file = CFSP_DIR.'README.txt';
		if (is_file($file) && is_readable($file)) {
			$markdown = file_get_contents($file);
			$markdown = preg_replace('|!\[(.*?)\]\((.*?)\)|', '![$1]('.CFSP_DIR.'/$2)', $markdown);
			return $markdown;
		}
		return null;
	}
}

## Auxillary Functionality

/**
 * Add some information to the "Right Now" section of the WP Admin Dashboard.  This will make it easier to
 * get into the Snippets edit screen.
 */
function cfsp_rightnow_end() {
	$cf_snippet = new CF_Snippet();
	$count = count($cf_snippet->get_keys());
	$link = admin_url('options-general.php?page=cf-snippets');
	?>
	<tr>
		<td class="first b b-tags"><a href="<?php echo $link; ?>"><?php echo $count; ?></a></td>
		<td class="t tags"><a href="<?php echo $link; ?>"><?php _e('CF Snippet', 'cfsp'); echo ($count == 1) ? '' : 's'; ?></a></td>
	</tr>
	<?php
}
add_action('right_now_content_table_end', 'cfsp_rightnow_end');

/**
 * JSON ENCODE and DECODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode & json_decode
 * Uses the Pear Class Services_JSON - http://pear.php.net/package/Services_JSON
 */ 
function cfsp_include_json() {
	global $wp_version;
	if (!function_exists('json_encode') && !class_exists('Services_JSON') && version_compare($wp_version, '3.0', '<')) {
		require_once('classes/external/JSON.php');
	}
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
		cfsp_include_json();
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
		cfsp_include_json();
		global $cfsp_json_object;
		if (!($cfsp_json_object instanceof Services_JSON)) {
			$cfsp_json_object = new Services_JSON();
		}
		$cfsp_json_object->use = $array ? SERVICES_JSON_LOOSE_TYPE : 0;
		return $cfsp_json_object->decode($json);
	}
}

## Integration with the CF Links Plugin

function cfsp_cflk_integration() {
	if (function_exists('cflk_register_link')) {
		include('classes/cflk.snippets.class.php');
		cflk_register_link('cfsp_link');
	}
}
add_action('plugins_loaded', 'cfsp_cflk_integration', 99999);

## Integration with CF Revision Manager

	function cfsp_register_revisions() {
		if (function_exists('cfr_register_metadata')) {
			cfr_register_metadata('_cfsp-keys');
		}
	}
	add_action('init', 'cfsp_register_revisions');

?>