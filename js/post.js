;(function($) {
	$(function() {
		$("#cfsp-add-new").live('click', function() {
			var postID = $("#post_ID").val();
			var id = new Date().valueOf();
			var section = id.toString();
			var length = section.length;
			section = section.substring(Math.ceil(length/2), length);
			
			var html = $("#cfsp-new-item-default").html().replace(/###SECTION###/g, section).replace(/###SECTIONNAME###/g, 'cfsp-'+postID+'-'+section).replace(/###POSTID###/g, postID);
			$("#cfsp-current").append(html);
			return false;
		});
		
		$(".cfsp-remove-link").live('click', function() {
			var id = $(this).attr('id').replace('cfsp-remove-link-', '');
			if (confirm('Are you sure you want to remove the snippet from this post?')) {
				$("#cfsp-item-"+id).remove();
			}
			return false;
		});
		
		$(".cfsp-hide-link").live('click', function() {
			var id = $(this).attr('id').replace('cfsp-hide-link-', '');
			$("#cfsp-content-"+id).slideUp();
			$(this).hide();
			$("#cfsp-show-link-"+id).show();
			return false;
		});

		$(".cfsp-show-link").live('click', function() {
			var id = $(this).attr('id').replace('cfsp-show-link-', '');
			$("#cfsp-content-"+id).slideDown();
			$(this).hide();
			$("#cfsp-hide-link-"+id).show();
			return false;
		});

		$(".cfsp-add-content-link").live('click', function() {
			var id = $(this).attr('id').replace('cfsp-add-content-link-', '');
			var cfspID = $("#cfsp-name-"+id).val();
			cfsp_add_to_content(cfspID);
			return false;
		});
	});
	
	var cfsp_add_to_content = function (cfspID) {
		var shortcode = '[cfsp key="'+cfspID+'"]';
		
		if (typeof tinyMCE != 'undefined' && typeof tinyMCE.editors.content != 'undefined' && !tinyMCE.editors.content.isHidden()) {
			tinyMCE.execCommand('mceFocus', false, 'content');
			tinyMCE.execCommand('mceInsertContent', false, shortcode);
		}
		else if (edCanvas) {
			edInsertContent(edCanvas, shortcode);
		}
	}
})(jQuery);