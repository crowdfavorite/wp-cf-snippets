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
	<tr>
		<td style="text-align:left;">
			<?php if ($page > 1) { ?>
			<button class="cfsp-post-prev button">&laquo; <?php _e('Previous Page of CF Snippets', 'cfsp'); ?></button>
			<?php }?>
		</td>
		<td style="text-align:center">
			<?php echo __('Page ', 'cfsp').$page.__(' of ', 'cfsp').$total_pages; ?>
		</td>
		<td style="text-align:right;">
			<?php if ($page < $total_pages) { ?>
			<button class="cfsp-post-next button"><?php _e('Next Page of CF Snippets', 'cfsp'); ?> &raquo;</button>
			 <?php }?>
			<input type="hidden" id="cfsp-post-page-displayed" value="<?php echo $page; ?>" />
		</td>
	</tr>
</tfoot>