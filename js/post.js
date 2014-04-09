;(function($) {
	var cfsp_add_to_content = function (cfspID) {
		var shortcode = '[cfsp key="'+cfspID+'"]';
		
		if (!cfspID || !cfspID.length || cfspID.length == 0) {
			return;
		}

		if (typeof tinyMCE != 'undefined' && typeof tinyMCE.editors.content != 'undefined' && !tinyMCE.editors.content.isHidden()) {
			tinyMCE.execCommand('mceFocus', false, 'content');
			tinyMCE.execCommand('mceInsertContent', false, shortcode);
		}
		else if (edCanvas) {
			edInsertContent(edCanvas, shortcode);
		}
	},
	TypeAhead = function(input) {
		var $input = $(input),
		$extension = {},
		_ajaxRequest = undefined;
		
		$.extend($extension, $input, {
			"_typeaheadResults":
				$("<div></div>")
				.hide()
				.insertAfter($input),
			"typeaheadHasChanged": false,
			"val": function(newVal) {
				if (newVal !== undefined) {
					this.data("value", newVal);
					this.hasChanged = true;
					this.trigger("change");
					return this;
				}
				return this.data("value");
			},
			"text": function(newText) {
				$inp = $(this);
				if (newText !== undefined) {
					$inp.val(newText);
					return this;
				}
				return $inp.val();
			},
			"clearTypeahead": function() {
				this._typeaheadResults.html("").hide();
			},
			"updateTypeahead": function(resultsArray) {
				var myWidth = this.outerWidth(),
					myHeight = this.outerHeight(),
					myPosition = this.position();
				this._typeaheadResults.css({
					"position": "absolute",
					"top": myHeight + myPosition.top,
					"left": myPosition.left,
					"minWidth": myWidth,
					"border": "1px solid black",
					"backgroundColor": "white"
				});
				if (resultsArray && resultsArray.length > 0) {
					for (var result in resultsArray) {
						this._typeaheadResults.append("<div></div>").data("value", resultsArray[result].value).html(resultsArray[result].text);
					}
				}
				else {
					this._typeaheadResults.append($("<div>No Results Found</div>").css({"color": "red"}));
				}
				this._typeaheadResults.show();
			}
		});
		
		$extension._typeaheadResults.on("click", (function($this) {
			return function(e) {
				var $target = $(e.target);
				if ($target.data("value")) {
					$this.val($target.data("value"));
					$this.text($target.html())
				}
				else {
					$this.val(null);
				}
				$this.clearTypeahead();
			};
		})($extension));
		
		$extension.on("keyup focus ready", (function($this) {
			return function() {
				var searchString = $extension.text();
				$extension.val(null);
				if (_ajaxRequest !== undefined) {
					_ajaxRequest.abort();
				}
				if (searchString.length < 2) {
					return;
				}
				_ajaxRequest = $.get(
					ajaxurl, // Defined by WordPress
					{"action": "cfsp_typeahead_key", "snippet_key": searchString, "security": snippetKey},
					function(data) {
						data = $.parseJSON(data);
						$this.clearTypeahead();
						if (data && data.result == "success" && data.data) {
							$this.updateTypeahead(data.data);
						}
					}
				);
			}
		})($extension))
		.on("change", (function($this) {
			return function(e) {
				if (!$this.hasChanged) {
					e.preventDefault();
					e.stopPropagation();
				}
				else {
					$this.hasChanged = false;
				}
			};
		})($extension));
		
		return $extension;
	};
	
	window.snippetKey = window.snippetKey || false; // Just used to prevent an error. If this happens, AJAX requests won't validate anyway.

	$(function() {
		var $editBox = $("#cfsp-meta-edit-window"),
			typeAhead = new TypeAhead("#inp-cfsp-typeahead-key"),
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
			
		function updateButtons() {
			if (typeAhead.val()) {
				
			}
		}

		typeAhead.on("change", function(e) {
			if (typeAhead.val()) {
				$("#cfsp-add-snippet, #cfsp-edit-snippet").prop("disabled", "").show();
			}
			else {
				$("#cfsp-add-snippet, #cfsp-edit-snippet").prop("disabled", "disabled").hide();
			}
			$editBox.hide();
		});
		
		$("body").on("click", function() {
			typeAhead.clearTypeahead();
		})

		$("#cfsp-add-snippet").click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			$(this).parent().trigger("click"); // Allow it to bubble without that initial action
			cfsp_add_to_content(typeAhead.val());
		}).prop("disabled", "disabled").hide();

		$("#cfsp-new-snippet").click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			$(this).parent().trigger("click"); // Allow it to bubble without that initial action
			setupEditBox({"ID": "", "post_name": typeAhead.text(), "post_title": "", "post_content": ""}).show();
		});

		$("#cfsp-edit-snippet").click(function(e) {
			// TODO Create AJAX call to get snippet post and populate
			var post = {"ID": "", "post_name": "", "post_title": "", "post_content": ""};
			e.preventDefault();
			e.stopPropagation();
			$(this).parent().trigger("click"); // Allow it to bubble without that initial action
			if (!typeAhead.val()) {
				return;
			}
			if (typeof ajaxRequest != "undefined" && ajaxRequest !== null) {
				ajaxRequest.abort();
				ajaxRequest = null;
			}
			ajaxRequest = $.get(
				ajaxurl, // Defined by WordPress
				{"action": "cfsp_get_snippet", "key": typeAhead.val(), "security": snippetKey },
				function (data) {
					var decoded = $.parseJSON(data);
					if (decoded.result == "success") {
						setupEditBox(decoded.data).show();
					}
				}
			);
		}).prop("disabled", "disabled").hide();

		$("#cfsp-save-snippet").click(function(e) {
			// TODO Create AJAX call to save snippet. Should return updated list content for select box and updated post information on success.
			var params = {"action": "cfsp_save_snippet", "security": snippetKey};
			e.preventDefault();
			e.stopPropagation();
			$(this).parent().trigger("click"); // Allow it to bubble without that initial action
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
						/*if (decoded.data.keys.length > 0) {
							$selectBox.children().remove();
							decoded.data.keys.forEach(function(val, index, array) {
								var $option = $("<option value=\"" + val + "\">" + val + "</option>");
								if (val == decoded.data.snippet.post_name) {
									$option.attr("selected", true);
								}
								$selectBox.append($option);
							});
						}*/
					}
				}
			);
		});

		$('#cfsp-close-edit-window').click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			$(this).parent().trigger("click"); // Allow it to bubble without that initial action
			$editBox.hide();
		});

		$editBox.hide();
	});
})(jQuery);
