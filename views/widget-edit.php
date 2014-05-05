<p>
	<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'cfsp'); ?></label>
	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
</p>
<p>
	<label for="<?php echo esc_attr($this->get_field_id('list_key')); ?>"><?php _e('Snippet: ', 'cfsp'); ?></label>
	<input type="text" class="widget-snippet-typeahead widefat" name="<?php echo esc_attr($this->get_field_name('list_key')); ?>" value="<?php echo esc_attr($instance['list_key']); ?>" />
<br>
<br>
	<button class="button cfsp-clear-snippet"><?php _e('Clear'); ?></button>
</p>

<hr>
