<?php

/**
 *
 *	Keeps the old style widget in place so the settings may be copied to the new widgets
 *
 */

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

	echo $before_widget;
		if($title != '') {
			echo $before_title . $title . $after_title;
		} 
		// Do stuff for this widget, drawing data from $options[$number]
		$content = do_shortcode(cfsnip_get_snippet_content($options[$number]['snippet-name']));
		echo $content;
	echo $after_widget;
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

	$widget_ops = array('classname' => 'cfsnip_widgets', 'description' => __('Embed your snippets in widget sidebars. (Version 1.0. Upgrade to 2.0 version to continue functionality)'));
	$control_ops = array('width' => 250, 'height' => 350, 'id_base' => 'cfsnip-widgets');
	$name = __('CF Snippets 1.0');

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


?>