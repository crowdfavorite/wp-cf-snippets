<div class="cfsp">
	<div class="cfsp-edit-snip">
		<table class="form-table" border="0">
			<tr>
				<th style="width:50px;"><?php _e('Key'); ?></th>
				<td><input type="text" name="cfsp-key" id="cfsp-key" value="<?php echo esc_attr($key); ?>" disabled="disabled" class="widefat" /></td>
			</tr>
			<tr>
				<th style="width:50px;"><?php _e('Description'); ?></th>
				<td><input type="text" name="cfsp-description" id="cfsp-description" value="<?php echo esc_attr($description); ?>" class="widefat" /></td>
			</tr>
			<tr>
				<th style="width:50px;"><?php _e('Content'); ?></th>
				<td><textarea name="cfsp-content" id="cfsp-content" class="widefat cfsp-popup-edit-content" cols="50" rows="8"><?php echo esc_textarea($content); ?></textarea></td>
			</tr>
		</table>
	</div>
</div>
<p>
	<input type="hidden" name="cfsp-id" id="cfsp-id" value="<?php echo esc_attr($id); ?>" />
	<input type="button" class="button-primary cfsp-popup-submit" value="Save" />
	<input type="button" class="button cfsp-popup-cancel" value="Cancel" />
</p>
