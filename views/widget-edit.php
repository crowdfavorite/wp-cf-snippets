<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cfsp'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('list_key'); ?>"><?php _e('Snippet: ', 'cfsp'); ?></label>
	<select id="<?php echo $this->get_field_id('list_key'); ?>" name="<?php echo $this->get_field_name('list_key'); ?>" class="widefat">
		<option value="0"><?php _e('--Select Snippet--', 'cfsp'); ?></option>
		<?php echo $select; ?>
	</select>
</p>