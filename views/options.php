<div class="wrap">
	<?php echo screen_icon().'<h2>CF Snippets</h2>'; ?>
	<p><a href="#" rel="cfsp-instructions" class="cfsp-instructions"><span class="cfsp-instructions-show"><?php _e('Show', 'cfsp'); ?></span><span class="cfsp-instructions-hide" style="display:none;"><?php _e('Hide', 'cfsp'); ?></span><?php _e(' Instructions', 'cfsp'); ?></a> | <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('Edit Widgets &raquo;', 'cfsp'); ?></a></p>
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