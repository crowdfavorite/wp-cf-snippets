<div id="cfsp-popup" class="cfsp-popup">
	<div class="cfsp-popup-head">
		<span class="cfsp-popup-close">
			<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
		</span>
		<h2><?php _e('Create New Snippet:', 'cfsp'); ?></h2>
	</div>
	<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
		<div class="cfsp-popup-error" style="display:none">
			<p><strong><?php _e('Error: ', 'cfsp'); ?></strong><?php _e('A new snippet requires either a key or description, please fill one of these fields', 'cfsp'); ?></p>
		</div>
		<?php echo $cf_snippet->add_display(); ?>
	</div>
</div>