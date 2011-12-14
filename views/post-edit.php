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
			$item = str_replace('cfsp-'.get_the_ID().'-', '', $key);
			?>
			<div id="cfsp-item-<?php echo esc_attr($item); ?>" class="cfsp-item">
				<div id="cfsp-title-<?php echo esc_attr($item); ?>" class="cfsp-title">
					<span class="cfsp-name"><?php echo $key; ?></span>
					<span class="cfsp-add-content"><button id="cfsp-add-content-link-<?php echo esc_attr($item); ?>" class="button cfsp-add-content-link"><?php _e('Add to Content', 'cfsp'); ?></button></span>
					<span class="cfsp-remove"><button id="cfsp-remove-link-<?php echo esc_attr($item); ?>" class="button cfsp-remove-link"><?php _e('Remove Snippet from this Post', 'cfsp'); ?></button></span>
					<span class="cfsp-hide"><button id="cfsp-hide-link-<?php echo esc_attr($item); ?>" class="button cfsp-hide-link" style="display:none;"><?php _e('Hide Snippet', 'cfsp'); ?></button><button id="cfsp-show-link-<?php echo esc_attr($item); ?>" class="button cfsp-show-link"><?php _e('Show Snippet', 'cfsp'); ?></button></span>
				</div>
				<div id="cfsp-content-<?php echo esc_attr($item); ?>" class="cfsp-content" style="display:none;">
					<textarea id="<?php echo esc_attr($item); ?>" name="cfsp[<?php echo esc_attr($item); ?>][content]" class="cfsp-content-input widefat" rows="10"><?php echo esc_textarea($cf_snippet->get($key, false, false)); ?></textarea>
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