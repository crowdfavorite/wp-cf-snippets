// Mini popup framework
;(function($){
	// popup generic function
	cfsp_popup = function(html,width,height) {
		var t_html = "<div id=\"disposible-wapper\">"+html+"</div>";
		var w = width || 500;
		var h = height || 500;

		var opts = {
			windowSourceID:t_html,
			borderSize:0,
			windowBGColor:"transparent",
			windowPadding: 0,
			positionType:"centered",
			width:w,
			height:h,
			overlay:1,
			overlayOpacity:"65"
		};
		$.openDOMWindow(opts);
		$('#DOMWindow').css('overflow','visible');

		// fix the height on browsers that don't honor the max-height css directive
		var _contentdiv = $('#DOMWindow .cfsp-popup-content');
		if (_contentdiv.height() > height-20) {
			_contentdiv.css({'height':(height-20) + 'px'});
		}

		$(".cfsp-popup-close a").click(function(){
			$.closeDOMWindow();
			return false;
		});

		$(".cfsp-popup-cancel").click(function(){
			$.closeDOMWindow();
			return false;
		});

		$(".cfsp-popup-delete").click(function(){
			$.closeDOMWindow();
			var id = $("#cfsp-key").val();
			cfsp_delete_snippet(id);
			return false;
		});

		$(".cfsp-popup-new-submit").click(function(){
			var key = $("#cfsp-key").val();
			var description = $("#cfsp-description").val();
			var content = $("#cfsp-content").val();

			if (key || description) {
				$.closeDOMWindow();
				cfsp_new_snippet(key, description, content);
				return false;
			}
			else {
				$(".cfsp-popup-error").show();
				return false;
			}

			$.closeDOMWindow();
			cfsp_new_snippet(key, description, content);
			return false;
		});

		$(".cfsp-popup-submit").click(function() {
			var id = $('#cfsp-id').val(),
				key = $("#cfsp-key").val(),
				description = $("#cfsp-description").val(),
				content = $("#cfsp-content").val();

			$.closeDOMWindow();
			cfsp_save_snippet(id, key, description, content);
			return false;
		});

		return true;
	};

	// popup generic error function
	cfsp_popup_error = function(html,message) {
		alert("TEMPORARY FAILURE MESSAGE FORMAT: "+message);
		return true;
	};
})(jQuery);
