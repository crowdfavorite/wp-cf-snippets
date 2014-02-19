;(function($) {
	var cfsp_add_to_content = function (cfspID) {
		var shortcode = '[cfsp key="'+cfspID+'"]';

		if (typeof tinyMCE != 'undefined' && typeof tinyMCE.editors.content != 'undefined' && !tinyMCE.editors.content.isHidden()) {
			tinyMCE.execCommand('mceFocus', false, 'content');
			tinyMCE.execCommand('mceInsertContent', false, shortcode);
		}
		else if (edCanvas) {
			edInsertContent(edCanvas, shortcode);
		}
	};

	$(function() {
		var $editBox = $("#cfsp-meta-edit-window"),
			$selectBox = $("#sel-cfsp-select-snippet"),
			ajaxRequest = null,
			setupEditBox = function(data) {
				$editBox.find('[name="snippet_ID"]').val(data.ID).end()
					.find('[name="snippet_post_name"]').val(data.post_name).end()
					.find('[name="snippet_post_title"]').val(data.post_title).end()
					.find('[name="snippet_post_content"]').val(data.post_content).end()
					.find('div.message').html('').hide();
				if (data.ID > 0) {
					// No changing the snippet name of an established snippet.
					$editBox.find('[name="snippet_post_name"]').attr('disabled', 'disabled');
				}
				else {
					$editBox.find('[name="snippet_post_name"]').removeAttr('disabled');
				}
				return $editBox;
			};

		$selectBox.change(function(e) {
			if ($editBox.is(":visible")) {
				$("#cfsp-edit-snippet").click();
			}
		});

		$("#cfsp-add-snippet").click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			cfsp_add_to_content($selectBox.val());
		});

		$("#cfsp-new-snippet").click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			setupEditBox({"ID": "", "post_name": "", "post_title": "", "post_content": ""}).show();
		});

		$("#cfsp-edit-snippet").click(function(e) {
			// TODO Create AJAX call to get snippet post and populate
			var post = {"ID": "", "post_name": "", "post_title": "", "post_content": ""};
			e.preventDefault();
			e.stopPropagation();
			if (typeof ajaxRequest != "undefined" && ajaxRequest !== null) {
				ajaxRequest.abort();
				ajaxRequest = null;
			}
			ajaxRequest = $.get(
				ajaxurl, // Defined by WordPress
				{"action": "cfsp_get_snippet", "key": $selectBox.val() },
				function (data) {
					var decoded = $.parseJSON(data);
					if (decoded.result == "success") {
						setupEditBox(decoded.data).show();
					}
				}
			);
		});

		$("#cfsp-save-snippet").click(function(e) {
			// TODO Create AJAX call to save snippet. Should return updated list content for select box and updated post information on success.
			var params = {"action": "cfsp_save_snippet"};
			e.preventDefault();
			e.stopPropagation();
			$editBox.find('input, textarea').each(function() {
				var $input = $(this);
				params[$input.attr('name').replace(/^snippet_/, '')] = $input.val();
			});
			if (typeof ajaxRequest != "undefined" && ajaxRequest !== null) {
				ajaxRequest.abort();
				ajaxRequest = null;
			}
			$.post(
				ajaxurl, // Defined by WordPress
				params,
				function(data) {
					var decoded = $.parseJSON(data);
					if (decoded.result == "success") {
						setupEditBox(decoded.data.snippet);
						$editBox.find('div.message').html('Snippet saved').show();
						if (decoded.data.keys.length > 0) {
							$selectBox.children().remove();
							decoded.data.keys.forEach(function(val, index, array) {
								var $option = $("<option value=\"" + val + "\">" + val + "</option>");
								if (val == decoded.data.snippet.post_name) {
									$option.attr("selected", true);
								}
								$selectBox.append($option);
							});
						}
					}
				}
			);
		});

		$('#cfsp-close-edit-window').click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			$editBox.hide();
		});

		$editBox.hide();
	});
})(jQuery);
