<div id="cfsp-popup" class="cfsp-popup">
	<div class="cfsp-popup-head">
		<span class="cfsp-popup-close">
			<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
		</span>
		<h2><?php _e('Snippet: ', 'cfsp'); ?>"<?php echo $key; ?>"</h2>
	</div>
	<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
		<?php echo $cf_snippet->edit($key); ?>
	</div>
</div>
