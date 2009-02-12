(function() {
	tinymce.create('tinymce.plugins.cfsnippets', {
		init : function(ed, url) {
			pluginUrl = url.replace('js', '');
			ed.addButton('cfsnip_Btn', {
				title : 'Click to insert snippet',
				image : pluginUrl + '/images/cog_add.png',
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
		createControl : function(n, cm) {
			return null;
		},
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
	tinymce.PluginManager.add('cfsnippets', tinymce.plugins.cfsnippets);
})();