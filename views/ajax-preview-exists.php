<div id="cfsp-popup" class="cfsp-popup">
	<div class="cfsp-popup-head">
		<span class="cfsp-popup-close">
			<a href="#close"><?php _e('Close', 'cfsp'); ?></a>
		</span>
		<h2><?php _e('Snippet: ', 'cfsp'); ?>"<?php echo $key; ?>"</h2>
	</div>
	<div class="cfsp-popup-content" style="overflow:auto; max-height:500px;">
		<iframe src ="index.php?cf_action=cfsp_iframe_preview&cfsp_key=<?php echo $key; ?>" width="100%" height="300">
		  <p><?php _e('Your browser does not support iframes.', 'cfsp'); ?></p>
		</iframe>
		<p>
			<input type="button" class="button cfsp-popup-cancel" value="Close" />
		</p>
	</div>
</div>