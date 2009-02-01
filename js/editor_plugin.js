(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('cfsnippets');

	tinymce.create('tinymce.plugins.cfsnippets', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */

		init : function(ed, url) {
			ed.addButton('cfsnip_Btn', {
				title : 'Click to insert snippet',
				image : url + '/images/cog_add.png',
				onclick : function() {
					tinyMCE.activeEditor.windowManager.open({
						file : 'options-general.php?page=cf-snippets.php&cfsnip_action=dialog',
						width : 250 + ed.getLang('cfsnippet.delta_width', 0),
						height : 150 + ed.getLang('cfsnippet.delta_height', 0),
						title: 'Select snippet below',
						inline : 1
					});
				}
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "CFSnippets",
				author : 'CrowdFavorite',
				authorurl : 'http://crowdfavorite.com',
				infourl : 'http://crowdfavorite.com',
				version : "1.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('cfsnippets', tinymce.plugins.cfsnippets);
})();