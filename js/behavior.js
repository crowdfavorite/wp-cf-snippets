;(function($) {
	$(function() {
		$(".cfsp-new-button").click(function() {
			$.post(
				ajaxurl,
				{
					action:"cfsp_new",
					_ajax_nonce: nonces.cfsp_new
				},
				function(r) {
					cfsp_popup(r, 984);
				}
			);
		});

		$("body").on("click", ".cfsp-edit-button", function() {
			cfsp_ajax_edit_button($(this).attr("id").replace("-edit-button", ""));
		}).on("click", ".cfsp-preview-button", function() {
			cfsp_ajax_preview_button($(this).attr("id").replace("-preview-button", ""));
		}).on("click", ".cfsp-delete-button", function() {
			cfsp_ajax_delete_button($(this).attr("id").replace("-delete-button", ""));
		}).on("click", ".cfsp-tags-showhide a", function() {
			$("#"+$(this).attr("rel")).slideToggle();
			return false;
		}).on("click", ".cfsp-post-next", function() {
			var page = parseInt($("#cfsp-post-page-displayed").val(), 10);
			page += 1;
			cfsp_ajax_display_post_items(page);
			return false;
		}).on("click", ".cfsp-post-prev", function() {
			var page = parseInt($("#cfsp-post-page-displayed").val(), 10);
			page -= 1;
			cfsp_ajax_display_post_items(page);
			return false;
		}).on("click", ".cfsp-instructions", function() {
			$("#"+$(this).attr('rel')).slideToggle();
			$(".cfsp-instructions-show").toggle();
			$(".cfsp-instructions-hide").toggle();
			return false;
		});

		cfsp_new_snippet = function(key, description, content) {
			$.post(
				ajaxurl,
				{
					action:"cfsp_new_add",
					key:key,
					description:description,
					content:content,
					_ajax_nonce: nonces.cfsp_new_add
				},
				function(r) {
					$("#cfsp-display tbody").append(r);
					$(".cfsp-message").hide();
					$("#cfsp-display").show();
				}
			);
			return false;
		};

		cfsp_save_snippet = function(id, key, description, content) {
			$.post(
				ajaxurl,
				{
					action: "cfsp_save",
					id: id,
					key: key,
					description: description,
					content: content,
					_ajax_nonce: nonces.cfsp_save
				},
				function(r) {
					$("#cfsp-"+key+" span.cfsp-description-content").html(description);
				}
			);
			return false;
		};

		cfsp_ajax_edit_button = function(id) {
			$.post(
				ajaxurl,
				{
					action: "cfsp_edit",
					key: id,
					_ajax_nonce: nonces.cfsp_edit
				},
				function(r) {
					cfsp_popup(r, 984);
				}
			);
			return false;
		};

		cfsp_ajax_preview_button = function(id) {
			$.post(
				ajaxurl,
				{
					action: "cfsp_preview",
					key: id,
					_ajax_nonce: nonces.cfsp_preview
				},
				function(r) {
					cfsp_popup(r, 984);
				}
			);
			return false;
		};

		cfsp_ajax_delete_button = function(id) {
			if (confirm('Are you sure?')) {
				$.post(
					ajaxurl,
					{
						action: "cfsp_delete",
						key: id,
						_ajax_nonce: nonces.cfsp_delete
					},
					function(r) {
						console.log(r);
						if (r == '0') {
							alert('There was an error removing this snippet.');
						} 
						else {
							$("#cfsp-"+id).remove();
						}
					}
				);
			}
			return false;
		};

		cfsp_ajax_display_post_items = function(page) {
			$.post(
				ajaxurl,
				{
					action: "cfsp_post_items_paged",
					page: page,
					_ajax_nonce: nonces.cfsp_post_items_paged
				},
				function(r) {
					$("#cfsp-post-display").html(r);
				}
			);
		};
	});
})(jQuery);
