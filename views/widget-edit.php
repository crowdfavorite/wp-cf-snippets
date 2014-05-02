<p>
	<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'cfsp'); ?></label>
	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
</p>
<p>
	<label for="<?php echo esc_attr($this->get_field_id('list_key')); ?>"><?php _e('Snippet: ', 'cfsp'); ?></label>
	<select id="<?php echo esc_attr($this->get_field_id('list_key')); ?>" name="<?php echo esc_attr($this->get_field_name('list_key')); ?>" class="widefat">
		<option value="0"><?php _e('--Select Snippet--', 'cfsp'); ?></option>
		<?php echo $select; ?>
	</select>
</p>

<hr>

<?php if ($keys !== false) : ?>
<div>
	<input type="text" id="inp-cfsp-typeahead-key" />
	<span class="cfsp-add-snippet"><button id="cfsp-add-snippet" class="button cfsp-add-snippet"><?php _e('Add to Content', 'cfsp'); ?></button>
	<span class="cfsp-preview-snippet"><button id="cfsp-preview-snippet" class="button cfsp-preview-snippet"><?php _e('Preview Snippet', 'cfsp'); ?></button>
	<?php
	if ($cf_snippet->user_can_admin_snippets()) :
	?>
	<span class="cfsp-edit-snippet"><button id="cfsp-edit-snippet" class="button cfsp-edit-snippet"><?php _e('Edit Snippet', 'cfsp'); ?></button></span>
	<span class="cfsp-new-snippet"><button id="cfsp-new-snippet" class="button cfsp-new-snippet"><?php _e('Add New Snippet', 'cfsp'); ?></button></span>
	<?php endif; ?>
</div>
<div id="cfsp-meta-edit-window" style="border-top:thin solid #ccc; margin: 7px 0;">
	<fieldset>
		<input type="hidden" name="snippet_ID" value="" />
		<label><?php _e('Name:', 'cfsp'); ?><input type="text" name="snippet_post_name" value="" style="margin-left:1em;" /></label>
	</fieldset>
	<fieldset>
		<label for="cfsp-meta-edit-title" style="display:block;"><?php _e('Description:', 'cfsp'); ?></label>
		<input id="cfsp-meta-edit-title" name="snippet_post_title" class="widefat" style="width:75%;background-color:#FFFFFF;" /></label>
	</fieldset>
	<fieldset>
		<label for="cfsp-meta-edit-content" style="display:block;"><?php _e('Content:'); ?></label>
		<textarea id="cfsp-meta-edit-content" name="snippet_post_content" class="cfsp-content-input widefat" rows="10"></textarea>
	</fieldset>
	<div class="message"></div>
	<fieldset>
		<button id="cfsp-save-snippet" class="button cfsp-save-snippet"><?php _e('Save'); ?></button>
		<button id="cfsp-close-edit-window" class="button cfsp-close-edit-window"><?php _e('Close'); ?></button>
	</fieldset>
</div>
<?php endif; ?>
<div id="cfsp-meta-preview-window" style="border-top:thin solid #ccc; margin: 7px 0;">
	<h4>Snippet Preview</h4>
	<div id="cfsp-preview-area" style="border: thin solid #ccc; margin: 7px; padding: 3px; position: relative; overflow: hidden;"></div>
	<button id="cfsp-close-preview-window" class="button cfsp-close-preview-window"><?php _e('Close Preview'); ?></button>
</div>