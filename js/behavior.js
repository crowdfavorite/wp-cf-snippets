;(function($) {
	$(function() {
		$(".cfsp-new-button").click(function() {
			$.post("index.php", {
				cf_action:"cfsp_new"
			}, function(r) {
				cfsp_popup(r, 984);
			});
		});
		
		$("body").on("click", ".cfsp-edit-button" function() {
			cfsp_ajax_edit_button($(this).attr("id").replace("-edit-button", ""));
		}).on("click", ".cfsp-preview-button", function() {
			cfsp_ajax_preview_button($(this).attr("id").replace("-preview-button", ""));
		}).on("click", ".cfsp-delete-button", function() {
			cfsp_ajax_delete_button($(this).attr("id").replace("-delete-button", ""));
		}).on("click", ".cfsp-tags-showhide a", function() {
			$("#"+$(this).attr("rel")).slideToggle();
			return false;
		}).on("click", ".cfsp-post-next", function() {
			var page = parseInt($("#cfsp-post-page-displayed").val());
			page += 1;
			cfsp_ajax_display_post_items(page);
			return false;
		}).on("click", ".cfsp-post-prev", function() {
			var page = parseInt($("#cfsp-post-page-displayed").val());
			page -= 1;
			cfsp_ajax_display_post_items(page);
			return false;
		}).on("click", ".cfsp-instructions", function() {
			$("#"+$(this).attr('rel')).slideToggle();
			$(".cfsp-instructions-show").toggle();
			$(".cfsp-instructions-hide").toggle();
			return false;
		});

		cfsp_delete_snippet = function(id) {
			$.post("index.php", {
				cf_action:"cfsp_delete",
				cfsp_key:id,
				cfsp_delete_confirm:"yes"
			}, function(r) {
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
				$(".cfsp-message").hide();
				$("#cfsp-display").show();
			});
			return false;
		};
		
		cfsp_save_snippet = function(id, key, description, content) {
			$.post("index.php", {
				cf_action:"cfsp_save",
				cfsp_id:id,
				cfsp_key:key,
				cfsp_description:description,
				cfsp_content:content
			}, function(r) {
				$("#cfsp-"+key+" span.cfsp-description-content").html(description);
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

		cfsp_ajax_display_post_items = function(page) {
			$.post("index.php", {
				cf_action:"cfsp_post_items_paged",
				cfsp_page:page
			}, function(r) {
				$("#cfsp-post-display").html(r);
			});
		};
	});
})(jQuery);
