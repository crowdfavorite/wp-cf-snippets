<html>
	<head>
		<title><?php _e('Select Snippet', 'cfsp'); ?></title>
		<script type="text/javascript" src="<?php echo includes_url('js/jquery/jquery.js'); ?>"></script>
		<script type="text/javascript" src="<?php echo includes_url('js/tinymce/tiny_mce_popup.js'); ?>"></script>
		<script type='text/javascript' src='<?php echo includes_url('js/quicktags.js'); ?>'></script>
		<script type="text/javascript">
			;(function($) {
				$(function() {
					$(".cfsp-list-link").on('click', function(e) {
						var key = $(this).attr('rel');
						cfsp_insert(key);
						e.preventDefault();
					});
				});
			})(jQuery);
		
			function cfsp_insert(key) {
				tinyMCEPopup.execCommand("mceBeginUndoLevel");
				tinyMCEPopup.execCommand('mceInsertContent', false, '[cfsp key="'+key+'"]');
				tinyMCEPopup.execCommand("mceEndUndoLevel");
				tinyMCEPopup.close();
				return false;
			}
		</script>
		<style type="text/css">
			.cfsp-list {
				padding-left:10px;
			}
		</style>
	</head>
	<body id="cfsnippet">
		<?php
		if (!empty($list)) {
			echo '<p>'.__('Click on the Snippet below to add the shortcode to the content of the post.', 'cfsp').'</p>';
			echo '<p>'.$list.'</p>';
		}
		else {
			echo '<p>'.__('No Snippets have been setup.  Please setup a snippet before proceeding.', 'cfsp').'</p>';
		}
		?>
	</body>
</html>