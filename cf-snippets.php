<?php 
/*
Plugin Name: CF Snippets
Plugin URI: 
Description: Lets admins define html snippets for use in template, content, or widgets. Snippets are not recursive (snippets defined inside other snippets will not be expanded).
Version: 1.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// the prefix to use before {my-snippet-name} in post content to indicate a snippet replacement
$cfsnip_escape_seq = 'cfsnip';

wp_enqueue_script('jquery');
if (!function_exists('wp_prototype_before_jquery')) {
	function wp_prototype_before_jquery( $js_array ) {
		if ( false === $jquery = array_search( 'jquery', $js_array ) )
			return $js_array;
		if ( false === $prototype = array_search( 'prototype', $js_array ) )
			return $js_array;
		if ( $prototype < $jquery )
			return $js_array;
		unset($js_array[$prototype]);
		array_splice( $js_array, $jquery, 0, 'prototype' );
		return $js_array;
	}
    add_filter( 'print_scripts_array', 'wp_prototype_before_jquery' );
}

$cfsnip_snippets = array();
$cfsnip_snippets_fetched = false;
function cfsnip_get_snippets() {
	global $cfsnip_snippets, $cfsnip_snippets_fetched;
	if (!$cfsnip_snippets_fetched) {
		$cfsnip_snippets = get_option('cfsnip_snippets');
		$cfsnip_snippets_fetched = true;
	}
	if (!$cfsnip_snippets) $cfsnip_snippets = array();
	return $cfsnip_snippets;
}

function cfsnip_snippet($snippet_name) {
	echo cfsnip_get_snippet_content($snippet_name);
}

function cfsnip_get_snippet($snippet_name) {
	$snippets = cfsnip_get_snippets();
	return $snippets[$snippet_name];
}

function cfsnip_get_snippet_content($snippet_name) {
	$snippet = cfsnip_get_snippet($snippet_name);
	return str_replace('{cfsnip_template_url}', get_bloginfo('template_url'), stripslashes($snippet['content']));
}

function cfsnip_snippet_content($snippet_name) {
	echo cfsnip_get_snippet_content($snippet_name);
}
function cfsnip_snippet_exists($snippet_name) {
	$snippets = cfsnip_get_snippets();
	return isset($snippets[$snippet_name]);
}
function cfsnip_request_handler() {
	if (isset($_POST['cfsnip_action']) && $_POST['cfsnip_action'] == 'update_settings') {
		if (current_user_can('manage_options')) {
			$snippets = array();
			foreach ($_POST as $k => $v) {
				if (strpos($k, 'cfsnip_name_') === 0 && $k != 'cfsnip_name__n_') {
					$snip_num = (int)substr($k, 12);
					$snippets[sanitize_title($v)] = array(
						'content' => $_POST['cfsnip_content_'.$snip_num],
						'description' => $_POST['cfsnip_description_'.$snip_num],
					);
				}
			}
			update_option('cfsnip_snippets', $snippets);
			header('Location: '.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=cf-snippets.php&updated=true');
		}
		else {
			wp_die('You are not allowed to manage options.');
		}
	}
	if (isset($_GET['cfsnip_action'])) {
		switch($_GET['cfsnip_action']) {
			case 'admin_js':
			header("Content-type: text/javascript");
?>
jQuery(document).ready(function() {
	if (jQuery('ol.cfsnip_snippet_list').size() == 0) {
		return;
	}
	var cfsnip_nSnippets = jQuery('ol.cfsnip_snippet_list li').size();

	jQuery('#cfsnip_add_snippet').click(function() { cfsnip_addSnippet(++cfsnip_nSnippets); });

	var cfsnip_addSnippet = function(idNum) {
		var itemHTML = jQuery('#cfsnip_snippet_item_prototype').html().replace(/_n_/g, idNum);
		var nCurrentSnippets = jQuery('ol.cfsnip_snippet_list li').size();
		var zebraClass = (nCurrentSnippets % 2 ? ' odd' : '');
		jQuery('ol.cfsnip_snippet_list').append('<li class="cfsnip_empty_input' + zebraClass + '" id="cfsnip_snippet_item_' + idNum + '" style="display:none;">' + itemHTML + '</li>');
		jQuery('#cfsnip_snippet_item_' + idNum + ' span.cfsnip_number').html(nCurrentSnippets);
		cfsnip_addItemBehaviors(
			jQuery('#cfsnip_snippet_item_' + idNum).show('fast')
		);
		
	}
	
	var cfsnip_addItemBehaviors = function(jqItems) {
		
		// default text behavior
		jQuery('input, textarea', jqItems).focus(function() {
			var i = jQuery(this);
			if (i.hasClass('cfsnip_empty_input') && i.val().length) {
				i.get(0).cfsnip_defaultText = i.val();
			}
			if (i.val() == i.get(0).cfsnip_defaultText) {
				i.val('');
				i.removeClass('cfsnip_empty_input');
			}
		}).blur(function() {
			var i = jQuery(this);
			if (i.val().length == 0) {
				if ('cfsnip_defaultText' in i.get(0)) {
					i.val(i.get(0).cfsnip_defaultText);
					i.addClass('cfsnip_empty_input');
				}
			}
		});
				
		// remove
		jqItems.each(function(){
			var item = jQuery(this);
			jQuery('.cfsnip_remove_snippet', item).click(function() {
				item.hide('fast', function() { 
					item.remove();
					// note that we don't need to bother renumbering ids, etc.					
					cfsnip_renumberItemDisplay();
				});
				
			});
		});
	}
	
	var cfsnip_renumberItemDisplay = function() {
		var n = 1;
		jQuery('ol.cfsnip_snippet_list li span.cfsnip_number').each(function() {
			jQuery(this).html(n++);
		});
		jQuery('ol.cfsnip_snippet_list li').removeClass('odd').filter(':odd').addClass('odd');
	}
	
	if (cfsnip_nSnippets == 0) {
		cfsnip_addSnippet(0);
	}
	
	// default-text behavior
	cfsnip_addItemBehaviors(jQuery('li.cfsnip_snippet_item'));
});
<?php
			die();
			
			case 'css_admin':
			header("Content-type: text/css");
			print('
.cfsnip_empty_input {
	color:#999;
}

ol.cfsnip_snippet_list {
	
}

ol.cfsnip_snippet_list li {
	list-style:none;
	padding:10px;
}
ol.cfsnip_snippet_list li.odd {
	background: #f8f8f8;
}
ol.cfsnip_snippet_list li span.cfsnip_number {
	color:#888;
	font-size:20px;
	font-weight:bold;	
	position:relative;
	top: -75px
}
ol.cfsnip_snippet_list li input[type=text]{
	position:relative;
	top: -75px
}
.cfsnip_remove_snippet {
	color:#777;
	position:relative;
	top: -75px;
}
.cfsnip_remove_snippet:hover {
	color:#f77;
	cursor:pointer;
}
#cfsnip_add_snippet {
	float:right;
	cursor:pointer;
}
#cfsnip_add_snippet:hover {
	color:#d54e21;
}
			');
			die();
			
			case 'css_published':
			header("Content-type: text/css");
			print('
			');
			die();
			case 'dialog':
				cfsnip_dialog();
				die();
		}
	}
}
add_action('init', 'cfsnip_request_handler');

function cfsnip_css_published() {
	print('<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?cfsnip_action=css_published" type="text/css" media="screen" />');
}
//add_action('wp_head', 'cfsnip_css_published', 10);

function cfsnip_admin_head() {
	$plugin_path = get_bloginfo('wpurl').'/'.PLUGINDIR;
	print('<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?cfsnip_action=css_admin" type="text/css" media="screen" />');
	print('<script type="text/javascript" src="'.get_bloginfo('wpurl').'/index.php?cfsnip_action=admin_js"></script>');
}
add_action('admin_head', 'cfsnip_admin_head', 10);

function cfsnip_menu_items() {
	if (current_user_can('manage_options')) {
		add_options_page(
			'CF Snippets Options'
			, 'CF Snippets'
			, 10
			, basename(__FILE__)
			, 'cfsnip_options_form'
		);
	}
}
add_action('admin_menu', 'cfsnip_menu_items');

function cfsnip_options_form() {
	global $cfsnip_escape_seq;
	print('
		<div class="wrap">
			<h2>Snippets | <a href="'.get_bloginfo('url').'/wp-admin/widgets.php">'.__('Edit Widgets').'</a></h2>
			<p>Paste in HTML content for a snippet and give it a name. The name will be automatically "sanitized:" lowercased and all 
			spaces converted to dashes.</p>
			<p>To insert a snippet in your template, type <blockquote><code>&lt;?php cfsnip_snippet(\'my-snippet-name\'); ?></code></blockquote> Use 
			the shortcode syntax:
			<blockquote><code>[cfsnip name="my-snippet-name"]</code></blockquote> in post or page content to insert your snippet there.</p>
			
			<p>Or use snippet widgets wherever widgets can be used.</p>
			
			<p>To access files in your current theme template directory <em>from within a snippet</em>, type <code>{cfsnip_template_url}</code>. 
			That will be replaced with, for example, <code>http://example.com/wordpress/wp-content/themes/mytheme/</code>.</p>
			<form action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php" method="post">
				<ol style="display:none;">
					<li id="cfsnip_snippet_item_prototype">
						<span class="cfsnip_number">_n_</span>
						<input class="cfsnip_empty_input" id="cfsnip_name__n_" name="cfsnip_name__n_" type="text" value="Name" />
						<input class="cfsnip_empty_input" id="cfsnip_description__n_" name="cfsnip_description__n_" type="text" value="Description" />
						<textarea class="cfsnip_empty_input" rows="8" cols="50" id="cfsnip_content__n_" name="cfsnip_content__n_" >Content</textarea>
						<span class="cfsnip_remove_snippet">[x] Remove</span>
					</li>
				</ol>
				<ol class="cfsnip_snippet_list">
	');
	$snippets = cfsnip_get_snippets();
	
	$n = 0;
	$snip_class = '';
	foreach ($snippets as $key => $snippet) {
		$zebra_class = ($n % 2 ? ' odd' : '');
		print('
					<li id="cfsnip_snippet_item_'.$n.'" class="cfsnip_snippet_item'.$zebra_class.'">
						<span class="cfsnip_number">'.($n + 1).'</span>
						<input '.$snip_class.' id="cfsnip_name_'.$n.'" name="cfsnip_name_'.$n.'" type="text" value="'.$key.'" />
						<input '.$snip_class.'  id="cfsnip_description_'.$n.'" name="cfsnip_description_'.$n.'" type="text" value="'.stripslashes($snippet['description']).'" />
						<textarea  '.$snip_class.' rows="8" cols="50" id="cfsnip_content_'.$n.'" name="cfsnip_content_'.$n.'" >'.stripslashes($snippet['content']).'</textarea>
						<span class="cfsnip_remove_snippet">[x] Remove</span>
					</li>
		');
		$n++;
	}
	print('
				</ol>
				<span id="cfsnip_add_snippet">[+] Add Snippet</span>
				<div class="clear"></div>
				<p class="submit">
					<input type="hidden" name="cfsnip_action" value="update_settings" />
					<input type="submit" name="submit" value="Update CF Snippets" />
				</p>
			</form>			
		</div>
	');
}

function cfsnip_handle_shortcode($attrs, $content=null) {
	if (is_array($attrs) && isset($attrs['name'])) {
		$snippets = cfsnip_get_snippets();		
		return do_shortcode(stripslashes($snippets[$attrs['name']]['content']));
	}
	return '';
}
add_shortcode('cfsnip', 'cfsnip_handle_shortcode');

// for widgets ... pattern copied from wp-includes/widgets.php

// Displays widget on blag
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function cfsnip_widgets( $args, $widget_args = 1 ) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('cfsnip_widgets');
	if ( !isset($options[$number]) )
		return;
	
	$title = $options[$number]['title'];
	
	echo '<div class="'.$options[$number]['snippet-name'].' widget widget_links">';
	
	if($title != '') {
		echo $before_title . $title . $after_title;
	} 
	
	// Do stuff for this widget, drawing data from $options[$number]
	$content = do_shortcode(cfsnip_get_snippet_content($options[$number]['snippet-name']));
	echo $content;

	echo '</div>';
}



// Displays form for a particular instance of the widget.  Also updates the data after a POST submit
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function cfsnip_widgets_control( $widget_args = 1 ) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('cfsnip_widgets');
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'cfsnip_widgets' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "cfsnip-widgets-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "cfsnip-widgets-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['cfsnip-widgets'] as $widget_number => $widgets_instance ) {
			// compile data from $widgets_instance
			if ( !isset($widgets_instance['snippet-name']) && isset($options[$widget_number]) ) // user clicked cancel
				continue;
			$something = wp_specialchars( $widgets_instance['snippet-name'] );
			$title = wp_specialchars( $widgets_instance['title'] );
			$options[$widget_number] = array( 'snippet-name' => $something, 'title' => $title );  // Even simple widgets should store stuff in array, rather than in scalar
		}
		update_option('cfsnip_widgets', $options);

		$updated = true; // So that we don't go through this more than once
	}

	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$something = '';
		$title = '';
		$number = '%i%';
	} else {
		$something = attribute_escape($options[$number]['snippet-name']);
		$title = attribute_escape($options[$number]['title']);
	}

	$snippets = cfsnip_get_snippets();

	// The form has inputs with names like cfsnip-widgets[$number][snippet-name] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['cfsnip-widgets'][$number]
?>
		<p>
			<label for="cfsnip-widgets-snippet-title-<?php echo $number; ?>">Title:</label>
			<input id="cfsnip-widgets-snippet-title-<?php echo $number; ?>" name="cfsnip-widgets[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="cfsnip-widgets-snippet-select">Snippet:</label>
			<select onchange="jQuery('#cfsnip-widgets-snippet-name-<?php echo $number; ?>').val(jQuery(this).val());" id="cfsnip-widgets-snippet-select">
				<option value="">Select Snippet</option>			
				<?php
				foreach ($snippets as $name => $snippet) {
					if ($name == $something) {
						$selected = ' selected="selected"';
					}
					else {
						$selected = '';
					}
					print('<option value="'.$name.'"'.$selected.'>'.$name.'</option>');
				}
				?>
			</select>
			<input id="cfsnip-widgets-snippet-name-<?php echo $number; ?>" name="cfsnip-widgets[<?php echo $number; ?>][snippet-name]" type="hidden" value="<?php echo $something; ?>" />
			<input type="hidden" id="cfsnip-widgets-submit-<?php echo $number; ?>" name="cfsnip-widgets[<?php echo $number; ?>][submit]" value="1" />
		</p>
		<p>
			<a href="<?php bloginfo('url') ?>/wp-admin/options-general.php?page=cf-snippets.php"><?php _e('Edit Snippets') ?></a>
		</p>
<?php
}

// Registers each instance of our widget on startup
function cfsnip_widgets_register() {
	if ( !$options = get_option('cfsnip_widgets') )
		$options = array();

	$widget_ops = array('classname' => 'cfsnip_widgets', 'description' => __('Embed your snippets in widget sidebars.'));
	$control_ops = array('width' => 250, 'height' => 350, 'id_base' => 'cfsnip-widgets');
	$name = __('CF Snippets');

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['snippet-name']) ) // we used 'something' above in our exampple.  Replace with with whatever your real data are.
			continue;

		// $id should look like {$id_base}-{$o}
		$id = "cfsnip-widgets-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'cfsnip_widgets', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'cfsnip_widgets_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'cfsnip-widgets-1', $name, 'cfsnip_widgets', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'cfsnip-widgets-1', $name, 'cfsnip_widgets_control', $control_ops, array( 'number' => -1 ) );
	}

}

// This is important
add_action( 'widgets_init', 'cfsnip_widgets_register' );

function cfsnip_dialog() {
?>
<script type='text/javascript' src='<?php print(get_bloginfo('url')); ?>/wp-includes/js/quicktags.js'></script>
<script type="text/javascript">
	function snippet_settext(text) {
		text = '<p>[cfsnip name="' + text + '"]</p>';

		parent.window.tinyMCE.execCommand("mceBeginUndoLevel");
		parent.window.tinyMCE.execCommand('mceInsertContent', false, '<p>'+text+'</p>');
	 	parent.window.tinyMCE.execCommand("mceEndUndoLevel");
	}
</script>
<?
	$snippets = get_option('cfsnip_snippets');

	foreach ($snippets as $key => $snippet) {
		?>
			<li>
				<a href="#" onclick="snippet_settext('<?php print($key); ?>');">
					<?php print(stripslashes($snippet['description'])); ?>
				</a>
			</li>
		<?
	}
}

function cfsnip_addtinymce() {
   // Don't bother doing this stuff if the current user lacks permissions
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "add_cfsnip_tinymce_plugin");
     add_filter('mce_buttons', 'register_cfsnip_button');
   }
}
 
function register_cfsnip_button($buttons) {
   array_push($buttons, '|', "cfsnip_Btn");
   return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_cfsnip_tinymce_plugin($plugin_array) {
   $plugin_array['cfsnippets'] = get_bloginfo('wpurl') . '/wp-content/plugins/cf-snippets/js/editor_plugin.js';
   return $plugin_array;
}
// init process for button control
add_action('init', 'cfsnip_addtinymce');
?>