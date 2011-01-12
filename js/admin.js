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
		
		$('.cfsp-tags-showhide a').live('click', function() {
			$("#"+$(this).attr('rel')).slideToggle();
			return false;
		});
		
		$('.cfsp-instructions').click(function() {
			$("#"+$(this).attr('rel')).slideToggle();
			$(".cfsp-instructions-show").toggle();
			$(".cfsp-instructions-hide").toggle();
			return false;
		});
		
		$('.cfsp-post-next').live('click', function() {
			var page = parseInt($('#cfsp-post-page-displayed').val());
			page += 1;
			cfsp_ajax_display_post_items(page);
			return false;
		});

		$('.cfsp-post-prev').live('click', function() {
			var page = parseInt($('#cfsp-post-page-displayed').val());
			page -= 1;
			cfsp_ajax_display_post_items(page);
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
		
		cfsp_save_snippet = function(key, description, content) {
			$.post("index.php", {
				cf_action:"cfsp_save",
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
(function($){
	
	//closeDOMWindow
	$.fn.closeDOMWindow = function(settings){
		
		if (!settings){settings={};}
		
		var run = function(passingThis){
			
			if (settings.anchoredClassName){
				var $anchorClassName = $('.'+settings.anchoredClassName);
				$anchorClassName.fadeOut('fast',function(){
					if ($.fn.draggable){
						$anchorClassName.draggable('destory').trigger("unload").remove();	
					}else{
						$anchorClassName.trigger("unload").remove();
					}
				});
				if (settings.functionCallOnClose){settings.functionCallAfterClose();}
			}else{
				var $DOMWindowOverlay = $('#DOMWindowOverlay');
				var $DOMWindow = $('#DOMWindow');
				$DOMWindowOverlay.fadeOut('fast',function(){
					$DOMWindowOverlay.trigger('unload').unbind().remove();																	  
				});
				$DOMWindow.fadeOut('fast',function(){
					if ($.fn.draggable){
						$DOMWindow.draggable("destroy").trigger("unload").remove();
					}else{
						$DOMWindow.trigger("unload").remove();
					}
				});
			
				$(window).unbind('scroll.DOMWindow');
				$(window).unbind('resize.DOMWindow');
				
				if ($.fn.openDOMWindow.isIE6){$('#DOMWindowIE6FixIframe').remove();}
				if (settings.functionCallOnClose){settings.functionCallAfterClose();}
			}	
		};
		
		if (settings.eventType){//if used with $().
			return this.each(function(index){
				$(this).bind(settings.eventType, function(){
					run(this);
					return false;
				});
			});
		}else{//else called as $.function
			run();
		}
		
	};
	
	//allow for public call, pass settings
	$.closeDOMWindow = function(s){$.fn.closeDOMWindow(s);};
	
	//openDOMWindow
	$.fn.openDOMWindow = function(instanceSettings){	
		
		var shortcut =  $.fn.openDOMWindow;
	
		//default settings combined with callerSettings////////////////////////////////////////////////////////////////////////
		
		shortcut.defaultsSettings = {
			anchoredClassName:'',
			anchoredSelector:'',
			borderColor:'#ccc',
			borderSize:'4',
			draggable:0,
			eventType:null, //click, blur, change, dblclick, error, focus, load, mousedown, mouseout, mouseup etc...
			fixedWindowY:100,
			functionCallOnOpen:null,
			functionCallOnClose:null,
			height:500,
			loader:0,
			loaderHeight:0,
			loaderImagePath:'',
			loaderWidth:0,
			modal:0,
			overlay:1,
			overlayColor:'#000',
			overlayOpacity:'85',
			positionLeft:0,
			positionTop:0,
			positionType:'centered', // centered, anchored, absolute, fixed
			width:500, 
			windowBGColor:'#fff',
			windowBGImage:null, // http path
			windowHTTPType:'get',
			windowPadding:10,
			windowSource:'inline', //inline, ajax, iframe
			windowSourceID:'',
			windowSourceURL:'',
			windowSourceAttrURL:'href'
		};
		
		var settings = $.extend({}, $.fn.openDOMWindow.defaultsSettings , instanceSettings || {});
		
		//Public functions
		
		shortcut.viewPortHeight = function(){ return self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;};
		shortcut.viewPortWidth = function(){ return self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;};
		shortcut.scrollOffsetHeight = function(){ return self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;};
		shortcut.scrollOffsetWidth = function(){ return self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;};
		shortcut.isIE6 = typeof document.body.style.maxHeight === "undefined";
		
		//Private Functions/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		var sizeOverlay = function(){
			var $DOMWindowOverlay = $('#DOMWindowOverlay');
			if (shortcut.isIE6){//if IE 6
				var overlayViewportHeight = document.documentElement.offsetHeight + document.documentElement.scrollTop - 4;
				var overlayViewportWidth = document.documentElement.offsetWidth - 21;
				$DOMWindowOverlay.css({'height':overlayViewportHeight +'px','width':overlayViewportWidth+'px'});
			}else{//else Firefox, safari, opera, IE 7+
				$DOMWindowOverlay.css({'height':'100%','width':'100%','position':'fixed'});
			}	
		};
		
		var sizeIE6Iframe = function(){
			var overlayViewportHeight = document.documentElement.offsetHeight + document.documentElement.scrollTop - 4;
			var overlayViewportWidth = document.documentElement.offsetWidth - 21;
			$('#DOMWindowIE6FixIframe').css({'height':overlayViewportHeight +'px','width':overlayViewportWidth+'px'});
		};
		
		var centerDOMWindow = function() {
			var $DOMWindow = $('#DOMWindow');
			if (settings.height + 50 > shortcut.viewPortHeight()){//added 50 to be safe
				$DOMWindow.css('left',Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindow.outerWidth())/2));
			}else{
				$DOMWindow.css('left',Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindow.outerWidth())/2));
				$DOMWindow.css('top',Math.round(shortcut.viewPortHeight()/2) + shortcut.scrollOffsetHeight() - Math.round(($DOMWindow.outerHeight())/2));
			}
		};
		
		var centerLoader = function() {
			var $DOMWindowLoader = $('#DOMWindowLoader');
			if (shortcut.isIE6){//if IE 6
				$DOMWindowLoader.css({'left':Math.round(shortcut.viewPortWidth()/2) + shortcut.scrollOffsetWidth() - Math.round(($DOMWindowLoader.innerWidth())/2),'position':'absolute'});
				$DOMWindowLoader.css({'top':Math.round(shortcut.viewPortHeight()/2) + shortcut.scrollOffsetHeight() - Math.round(($DOMWindowLoader.innerHeight())/2),'position':'absolute'});
			}else{
				$DOMWindowLoader.css({'left':'50%','top':'50%','position':'fixed'});
			}
			
		};
		
		var fixedDOMWindow = function(){
			var $DOMWindow = $('#DOMWindow');
			$DOMWindow.css('left', settings.positionLeft + shortcut.scrollOffsetWidth());
			$DOMWindow.css('top', + settings.positionTop + shortcut.scrollOffsetHeight());
		};
		
		var showDOMWindow = function(instance){
			if (arguments[0]){
				$('.'+instance+' #DOMWindowLoader').remove();
				$('.'+instance+' #DOMWindowContent').fadeIn('fast',function(){if (settings.functionCallOnOpen){settings.functionCallOnOpen();}});
				$('.'+instance+ '.closeDOMWindow').click(function(){
					$.closeDOMWindow();	
					return false;
				});
			}else{
				$('#DOMWindowLoader').remove();
				$('#DOMWindow').fadeIn('fast',function(){if (settings.functionCallOnOpen){settings.functionCallOnOpen();}});
				$('#DOMWindow .closeDOMWindow').click(function(){						
					$.closeDOMWindow();
					return false;
				});
			}
			
		};
		
		var urlQueryToObject = function(s){
			  var query = {};
			  s.replace(/b([^&=]*)=([^&=]*)b/g, function (m, a, d) {
				if (typeof query[a] != 'undefined') {
				  query[a] += ',' + d;
				} else {
				  query[a] = d;
				}
			  });
			  return query;
		};
			
		//Run Routine ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
		var run = function(passingThis){
			
			//get values from element clicked, or assume its passed as an option
			settings.windowSourceID = $(passingThis).attr('href') || settings.windowSourceID;
			settings.windowSourceURL = $(passingThis).attr(settings.windowSourceAttrURL) || settings.windowSourceURL;
			settings.windowBGImage = settings.windowBGImage ? 'background-image:url('+settings.windowBGImage+')' : '';
			var urlOnly, urlQueryObject;
			
			if (settings.positionType == 'anchored'){//anchored DOM window
				
				var anchoredPositions = $(settings.anchoredSelector).position();
				var anchoredPositionX = anchoredPositions.left + settings.positionLeft;
				var anchoredPositionY = anchoredPositions.top + settings.positionTop;
				
				$('body').append('<div class="'+settings.anchoredClassName+'" style="'+settings.windowBGImage+';background-repeat:no-repeat;padding:'+settings.windowPadding+'px;overflow:auto;position:absolute;top:'+anchoredPositionY+'px;left:'+anchoredPositionX+'px;height:'+settings.height+'px;width:'+settings.width+'px;background-color:'+settings.windowBGColor+';border:'+settings.borderSize+'px solid '+settings.borderColor+';z-index:10001"><div id="DOMWindowContent" style="display:none"></div></div>');		
				//loader
				if (settings.loader && settings.loaderImagePath !== ''){
					$('.'+settings.anchoredClassName).append('<div id="DOMWindowLoader" style="width:'+settings.loaderWidth+'px;height:'+settings.loaderHeight+'px;"><img src="'+settings.loaderImagePath+'" /></div>');
					
				}

				if ($.fn.draggable){
					if (settings.draggable){$('.' + settings.anchoredClassName).draggable({cursor:'move'});}
				}
				
				switch(settings.windowSource){
					case 'inline'://////////////////////////////// inline //////////////////////////////////////////
						$('.' + settings.anchoredClassName+" #DOMWindowContent").append($(settings.windowSourceID).children());
						$('.' + settings.anchoredClassName).unload(function(){// move elements back when you're finished
							$('.' + settings.windowSourceID).append( $('.' + settings.anchoredClassName+" #DOMWindowContent").children());				
						});
						showDOMWindow(settings.anchoredClassName);
					break;
					case 'iframe'://////////////////////////////// iframe //////////////////////////////////////////
						$('.' + settings.anchoredClassName+" #DOMWindowContent").append('<iframe frameborder="0" hspace="0" wspace="0" src="'+settings.windowSourceURL+'" name="DOMWindowIframe'+Math.round(Math.random()*1000)+'" style="width:100%;height:100%;border:none;background-color:#fff;" class="'+settings.anchoredClassName+'Iframe" ></iframe>');
						$('.'+settings.anchoredClassName+'Iframe').load(showDOMWindow(settings.anchoredClassName));
					break;
					case 'ajax'://////////////////////////////// ajax //////////////////////////////////////////	
						if (settings.windowHTTPType == 'post'){
							
							if (settings.windowSourceURL.indexOf("?") !== -1){//has a query string
								urlOnly = settings.windowSourceURL.substr(0, settings.windowSourceURL.indexOf("?"));
								urlQueryObject = urlQueryToObject(settings.windowSourceURL);
							}else{
								urlOnly = settings.windowSourceURL;
								urlQueryObject = {};
							}
							$('.' + settings.anchoredClassName+" #DOMWindowContent").load(urlOnly,urlQueryObject,function(){
								showDOMWindow(settings.anchoredClassName);
							});
						}else{
							if (settings.windowSourceURL.indexOf("?") == -1){ //no query string, so add one
								settings.windowSourceURL += '?';
							}
							$('.' + settings.anchoredClassName+" #DOMWindowContent").load(
								settings.windowSourceURL + '&random=' + (new Date().getTime()),function(){
								showDOMWindow(settings.anchoredClassName);
							});
						}
					break;
				}
				
			}else{//centered, fixed, absolute DOM window
				
				//overlay & modal
				if (settings.overlay){
					$('body').append('<div id="DOMWindowOverlay" style="z-index:10000;display:none;position:absolute;top:0;left:0;background-color:'+settings.overlayColor+';filter:alpha(opacity='+settings.overlayOpacity+');-moz-opacity: 0.'+settings.overlayOpacity+';opacity: 0.'+settings.overlayOpacity+';"></div>');
					if (shortcut.isIE6){//if IE 6
						$('body').append('<iframe id="DOMWindowIE6FixIframe"  src="blank.html"  style="width:100%;height:100%;z-index:9999;position:absolute;top:0;left:0;filter:alpha(opacity=0);"></iframe>');
						sizeIE6Iframe();
					}
					sizeOverlay();
					var $DOMWindowOverlay = $('#DOMWindowOverlay');
					$DOMWindowOverlay.fadeIn('fast');
					if (!settings.modal){$DOMWindowOverlay.click(function(){$.closeDOMWindow();});}
				}
				
				//loader
				if (settings.loader && settings.loaderImagePath !== ''){
					$('body').append('<div id="DOMWindowLoader" style="z-index:10002;width:'+settings.loaderWidth+'px;height:'+settings.loaderHeight+'px;"><img src="'+settings.loaderImagePath+'" /></div>');
					centerLoader();
				}

				//add DOMwindow
				$('body').append('<div id="DOMWindow" style="background-repeat:no-repeat;'+settings.windowBGImage+';overflow:auto;padding:'+settings.windowPadding+'px;display:none;height:'+settings.height+'px;width:'+settings.width+'px;background-color:'+settings.windowBGColor+';border:'+settings.borderSize+'px solid '+settings.borderColor+'; position:absolute;z-index:10001"></div>');
				
				var $DOMWindow = $('#DOMWindow');
				//centered, absolute, or fixed
				switch(settings.positionType){
					case 'centered':
						centerDOMWindow();
						if (settings.height + 50 > shortcut.viewPortHeight()){//added 50 to be safe
							$DOMWindow.css('top', (settings.fixedWindowY + shortcut.scrollOffsetHeight()) + 'px');
						}
					break;
					case 'absolute':
						$DOMWindow.css({'top':(settings.positionTop+shortcut.scrollOffsetHeight())+'px','left':(settings.positionLeft+shortcut.scrollOffsetWidth())+'px'});
						if ($.fn.draggable){
							if (settings.draggable){$DOMWindow.draggable({cursor:'move'});}
						}
					break;
					case 'fixed':
						fixedDOMWindow();
					break;
					case 'anchoredSingleWindow':
						var anchoredPositions = $(settings.anchoredSelector).position();
						var anchoredPositionX = anchoredPositions.left + settings.positionLeft;
						var anchoredPositionY = anchoredPositions.top + settings.positionTop;
						$DOMWindow.css({'top':anchoredPositionY + 'px','left':anchoredPositionX+'px'});
								
					break;
				}
				
				$(window).bind('scroll.DOMWindow',function(){
					if (settings.overlay){sizeOverlay();}
					if (shortcut.isIE6){sizeIE6Iframe();}
					if (settings.positionType == 'centered'){centerDOMWindow();}
					if (settings.positionType == 'fixed'){fixedDOMWindow();}
				});

				$(window).bind('resize.DOMWindow',function(){
					if (shortcut.isIE6){sizeIE6Iframe();}
					if (settings.overlay){sizeOverlay();}
					if (settings.positionType == 'centered'){centerDOMWindow();}
				});
				
				switch(settings.windowSource){
					case 'inline'://////////////////////////////// inline //////////////////////////////////////////
						$DOMWindow.append($(settings.windowSourceID).children());
						$DOMWindow.unload(function(){// move elements back when you're finished
							$(settings.windowSourceID).append($DOMWindow.children());				
						});
						showDOMWindow();
					break;
					case 'iframe'://////////////////////////////// iframe //////////////////////////////////////////
						$DOMWindow.append('<iframe frameborder="0" hspace="0" wspace="0" src="'+settings.windowSourceURL+'" name="DOMWindowIframe'+Math.round(Math.random()*1000)+'" style="width:100%;height:100%;border:none;background-color:#fff;" id="DOMWindowIframe" ></iframe>');
						$('#DOMWindowIframe').load(showDOMWindow());
					break;
					case 'ajax'://////////////////////////////// ajax //////////////////////////////////////////
						if (settings.windowHTTPType == 'post'){
							
							if (settings.windowSourceURL.indexOf("?") !== -1){//has a query string
								urlOnly = settings.windowSourceURL.substr(0, settings.windowSourceURL.indexOf("?"));
								urlQueryObject = urlQueryToObject(settings.windowSourceURL);
							}else{
								urlOnly = settings.windowSourceURL;
								urlQueryObject = {};
							}
							$DOMWindow.load(urlOnly,urlQueryObject,function(){
								showDOMWindow();
							});
						}else{
							if (settings.windowSourceURL.indexOf("?") == -1){ //no query string, so add one
								settings.windowSourceURL += '?';
							}
							$DOMWindow.load(
								settings.windowSourceURL + '&random=' + (new Date().getTime()),function(){
								showDOMWindow();
							});
						}
					break;
				}
				
			}//end if anchored, or absolute, fixed, centered
			
		};//end run()
		
		if (settings.eventType){//if used with $().
			return this.each(function(index){				  
				$(this).bind(settings.eventType,function(){
					run(this);
					return false;
				});
			});	
		}else{//else called as $.function
			run();
		}
		
	};//end function openDOMWindow
	
	//allow for public call, pass settings
	$.openDOMWindow = function(s){$.fn.openDOMWindow(s);};
	
})(jQuery);
/*
    http://www.JSON.org/json2.js
    2009-06-29

    Public Domain.

    NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

    See http://www.JSON.org/js.html

    This file creates a global JSON object containing two methods: stringify
    and parse.

        JSON.stringify(value, replacer, space)
            value       any JavaScript value, usually an object or array.

            replacer    an optional parameter that determines how object
                        values are stringified for objects. It can be a
                        function or an array of strings.

            space       an optional parameter that specifies the indentation
                        of nested structures. If it is omitted, the text will
                        be packed without extra whitespace. If it is a number,
                        it will specify the number of spaces to indent at each
                        level. If it is a string (such as '\t' or '&nbsp;'),
                        it contains the characters used to indent at each level.

            This method produces a JSON text from a JavaScript value.

            When an object value is found, if the object contains a toJSON
            method, its toJSON method will be called and the result will be
            stringified. A toJSON method does not serialize: it returns the
            value represented by the name/value pair that should be serialized,
            or undefined if nothing should be serialized. The toJSON method
            will be passed the key associated with the value, and this will be
            bound to the object holding the key.

            For example, this would serialize Dates as ISO strings.

                Date.prototype.toJSON = function (key) {
                    function f(n) {
                        // Format integers to have at least two digits.
                        return n < 10 ? '0' + n : n;
                    }

                    return this.getUTCFullYear()   + '-' +
                         f(this.getUTCMonth() + 1) + '-' +
                         f(this.getUTCDate())      + 'T' +
                         f(this.getUTCHours())     + ':' +
                         f(this.getUTCMinutes())   + ':' +
                         f(this.getUTCSeconds())   + 'Z';
                };

            You can provide an optional replacer method. It will be passed the
            key and value of each member, with this bound to the containing
            object. The value that is returned from your method will be
            serialized. If your method returns undefined, then the member will
            be excluded from the serialization.

            If the replacer parameter is an array of strings, then it will be
            used to select the members to be serialized. It filters the results
            such that only members with keys listed in the replacer array are
            stringified.

            Values that do not have JSON representations, such as undefined or
            functions, will not be serialized. Such values in objects will be
            dropped; in arrays they will be replaced with null. You can use
            a replacer function to replace those with JSON values.
            JSON.stringify(undefined) returns undefined.

            The optional space parameter produces a stringification of the
            value that is filled with line breaks and indentation to make it
            easier to read.

            If the space parameter is a non-empty string, then that string will
            be used for indentation. If the space parameter is a number, then
            the indentation will be that many spaces.

            Example:

            text = JSON.stringify(['e', {pluribus: 'unum'}]);
            // text is '["e",{"pluribus":"unum"}]'


            text = JSON.stringify(['e', {pluribus: 'unum'}], null, '\t');
            // text is '[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]'

            text = JSON.stringify([new Date()], function (key, value) {
                return this[key] instanceof Date ?
                    'Date(' + this[key] + ')' : value;
            });
            // text is '["Date(---current time---)"]'


        JSON.parse(text, reviver)
            This method parses a JSON text to produce an object or array.
            It can throw a SyntaxError exception.

            The optional reviver parameter is a function that can filter and
            transform the results. It receives each of the keys and values,
            and its return value is used instead of the original value.
            If it returns what it received, then the structure is not modified.
            If it returns undefined then the member is deleted.

            Example:

            // Parse the text. Values that look like ISO date strings will
            // be converted to Date objects.

            myData = JSON.parse(text, function (key, value) {
                var a;
                if (typeof value === 'string') {
                    a =
/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
                    if (a) {
                        return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
                            +a[5], +a[6]));
                    }
                }
                return value;
            });

            myData = JSON.parse('["Date(09/09/2001)"]', function (key, value) {
                var d;
                if (typeof value === 'string' &&
                        value.slice(0, 5) === 'Date(' &&
                        value.slice(-1) === ')') {
                    d = new Date(value.slice(5, -1));
                    if (d) {
                        return d;
                    }
                }
                return value;
            });


    This is a reference implementation. You are free to copy, modify, or
    redistribute.

    This code should be minified before deployment.
    See http://javascript.crockford.com/jsmin.html

    USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
    NOT CONTROL.
*/

/*jslint evil: true */

/*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
    call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
    getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
    lastIndex, length, parse, prototype, push, replace, slice, stringify,
    test, toJSON, toString, valueOf
*/

// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

var JSON = JSON || {};

(function () {

    function f(n) {
        // Format integers to have at least two digits.
        return n < 10 ? '0' + n : n;
    }

    if (typeof Date.prototype.toJSON !== 'function') {

        Date.prototype.toJSON = function (key) {

            return isFinite(this.valueOf()) ?
                   this.getUTCFullYear()   + '-' +
                 f(this.getUTCMonth() + 1) + '-' +
                 f(this.getUTCDate())      + 'T' +
                 f(this.getUTCHours())     + ':' +
                 f(this.getUTCMinutes())   + ':' +
                 f(this.getUTCSeconds())   + 'Z' : null;
        };

        String.prototype.toJSON =
        Number.prototype.toJSON =
        Boolean.prototype.toJSON = function (key) {
            return this.valueOf();
        };
    }

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;


    function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

        escapable.lastIndex = 0;
        return escapable.test(string) ?
            '"' + string.replace(escapable, function (a) {
                var c = meta[a];
                return typeof c === 'string' ? c :
                    '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
            }) + '"' :
            '"' + string + '"';
    }


    function str(key, holder) {

// Produce a string from holder[key].

        var i,          // The loop counter.
            k,          // The member key.
            v,          // The member value.
            length,
            mind = gap,
            partial,
            value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }

// What happens next depends on the value's type.

        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':

// If the value is a boolean or null, convert it to a string. Note:
// typeof null does not produce 'null'. The case is included here in
// the remote chance that this gets fixed someday.

            return String(value);

// If the type is 'object', we might be dealing with an object or an array or
// null.

        case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

            if (!value) {
                return 'null';
            }

// Make an array to hold the partial results of stringifying this object value.

            gap += indent;
            partial = [];

// Is the value an array?

            if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

                v = partial.length === 0 ? '[]' :
                    gap ? '[\n' + gap +
                            partial.join(',\n' + gap) + '\n' +
                                mind + ']' :
                          '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }

// If the replacer is an array, use it to select the members to be stringified.

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    k = rep[i];
                    if (typeof k === 'string') {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {

// Otherwise, iterate through all of the keys in the object.

                for (k in value) {
                    if (Object.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

            v = partial.length === 0 ? '{}' :
                gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' +
                        mind + '}' : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }

// If the JSON object does not yet have a stringify method, give it one.

    if (typeof JSON.stringify !== 'function') {
        JSON.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

            var i;
            gap = '';
            indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

// If the space parameter is a string, it will be used as the indent string.

            } else if (typeof space === 'string') {
                indent = space;
            }

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                     typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

            return str('', {'': value});
        };
    }


// If the JSON object does not yet have a parse method, give it one.

    if (typeof JSON.parse !== 'function') {
        JSON.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

            var j;

            function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

                var k, v, value = holder[key];
                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.hasOwnProperty.call(value, k)) {
                            v = walk(value, k);
                            if (v !== undefined) {
                                value[k] = v;
                            } else {
                                delete value[k];
                            }
                        }
                    }
                }
                return reviver.call(holder, key, value);
            }


// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

            cx.lastIndex = 0;
            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

            if (/^[\],:{}\s]*$/.
test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

                return typeof reviver === 'function' ?
                    walk({'': j}, '') : j;
            }

// If the text is not JSON parseable, then a SyntaxError is thrown.

            throw new SyntaxError('JSON.parse');
        };
    }
}());
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
			var key = $("#cfsp-key").val();
			var description = $("#cfsp-description").val();
			var content = $("#cfsp-content").val();
			
			$.closeDOMWindow();
			cfsp_save_snippet(key, description, content);
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