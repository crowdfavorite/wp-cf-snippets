<tr id="cfsp-<?php echo $key; ?>">
	<td class="cfsp-key" style="vertical-align:middle;">
		<?php echo $key; ?>
	</td>
	<td class="cfsp-description">
		<span class="cfsp-description-content"><?php echo esc_html($description); ?></span>
		<div id="<?php echo $key; ?>-showhide" class="cfsp-tags-showhide">
			<?php _e('Show: ', 'cfsp'); ?> <a href="#" rel="<?php echo $key; ?>-shortcode-template"><?php _e('Template Tag &amp; Shortcode', 'cfsp'); ?></a>
		</div>
		<div id="<?php echo $key; ?>-shortcode-template" class="cfsp-shortcode-template">
			<?php _e('Shortcode: ', 'cfsp'); ?><code>[cfsp key="<?php echo $key; ?>"]</code><br />
			<?php _e('Template Tag: ', 'cfsp'); ?><code>&lt;?php if (function_exists(&#x27;cfsp_content&#x27;)) { cfsp_content(&#x27;<?php echo $key; ?>&#x27;); } ?&gt;</code>
		</div>
	</td>
	<td class="cfsp-buttons" style="vertical-align:middle; text-align:center;">
		<input type="button" value="Edit" class="button cfsp-edit-button" id="<?php echo $key; ?>-edit-button" />
		<input type="button" value="Preview" class="button cfsp-preview-button" id="<?php echo $key; ?>-preview-button" />
		<input type="button" value="Delete" class="button cfsp-delete-button" id="<?php echo $key; ?>-delete-button" />
	</td>
</tr>
