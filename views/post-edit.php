<div id="cfsp-description">
	<p>
		<?php _e('The CF Snippets plugin adds the ability to create new CF Snippets on the fly for each post. These CF Snippets can be reused anywhere on the site that the code is needed. Any changes to these snippets will be lost unless this post is saved. To delete a snippet completely, go to the CF Snippets settings screen and click the Delete button on the snippet to be removed.  Clicking the remove button on a snippet on this screen will only remove it from this post.', 'cfsp'); ?>
	</p>
</div>
<?php if (is_array($keys) && !empty($keys)) { ?>
<div id="cfsp-select-snippet">
<select id="sel-cfsp-select-snippet" name="sel_snippet">
<?php
foreach ($keys as $key) {
	if (!$cf_snippet->exists($key)) { continue; }
?>
	<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
<?php
}
?>
</select>
<span class="cfsp-add-snippet"><button id="cfsp-add-snippet" class="button cfsp-add-snippet"><?php _e('Add to Content', 'cfsp'); ?></button>
<span class="cfsp-edit-snippet"><button id="cfsp-edit-snippet" class="button cfsp-edit-snippet"><?php _e('Edit Snippet', 'cfsp'); ?></button>
<span class="cfsp-new-snippet"><button id="cfsp-new-snippet" class="button cfsp-new-snippet"><?php _e('Add New Snippet', 'cfsp'); ?></button>
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
<?php } ?>