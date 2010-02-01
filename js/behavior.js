;(function($) {
	$(function() {
		$(".cfsp-new-button").click(function() {
			$.post("index.php", {
				cf_action:"cfsp_new"
			}, function(r) {
				cfsp_popup(r, 984);
			});
		});
		
		$('.cfsp-edit-button').live('click', function() {
			cfsp_ajax_edit_button($(this).attr('id').replace('-edit-button', ''));
		});

		$('.cfsp-preview-button').live('click', function() {
			cfsp_ajax_preview_button($(this).attr('id').replace('-preview-button', ''));
		});

		$('.cfsp-delete-button').live('click', function() {
			cfsp_ajax_delete_button($(this).attr('id').replace('-delete-button', ''));
		});				

		cfsp_delete_snippet = function(id) {
			$.post("index.php", {
				cf_action:"cfsp_delete",
				cfsp_key:id,
				cfsp_delete_confirm:"yes"
			}, function(r) {
				cfsp_popup(r, 500);
			});
			$("#cfsp-"+id).remove();
			return false;
		};
		
		cfsp_new_snippet = function(key, description, content) {
			$.post("index.php", {
				cf_action:"cfsp_new_add",
				cfsp_key:key,
				cfsp_description:description,
				cfsp_content:content
			}, function(r) {
				$("#cfsp-display tbody").append(r);
			});
			return false;
		};
		
		cfsp_save_snippet = function(key, description, content) {
			$.post("index.php", {
				cf_action:"cfsp_save",
				cfsp_key:key,
				cfsp_description:description,
				cfsp_content:content
			}, function(r) {
				$("#cfsp-"+key+" td.cfsp-description").html(description);
			});
			return false;
		};
		
		cfsp_ajax_edit_button = function(id) {
			$.post("index.php", {
				cf_action:"cfsp_edit",
				cfsp_key:id
			}, function(r) {
				cfsp_popup(r, 984);
			});
			return false;
		};
		
		cfsp_ajax_preview_button = function(id) {
			$.post("index.php", {
				cf_action:"cfsp_preview",
				cfsp_key:id
			}, function(r) {
				cfsp_popup(r, 984);
			});
			return false;
		};
		
		cfsp_ajax_delete_button = function(id) {
			$.post("index.php", {
				cf_action:"cfsp_delete",
				cfsp_key:id
			}, function(r) {
				cfsp_popup(r, 984);
			});
			return false;
		};
	});
})(jQuery);
