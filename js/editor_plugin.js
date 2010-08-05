(function() {
	tinymce.create('tinymce.plugins.cfsnippets', {
		init : function(ed, url) {
			var pluginUrl = url.replace('js', '');
			
			// Register button
			ed.addButton('cfsnip_Btn', {
				title : 'Click to Insert Snippet',
				image : pluginUrl + '/images/cog_add.png',
				cmd : 'CFSP_Insert'
			});
			
			// Register command
			ed.addCommand('CFSP_Insert', function() {
				ed.windowManager.open({
					file : 'index.php?cf_action=cfsp-dialog',
					width : 450 + ed.getLang('cfsnippet.delta_width', 0),
					height : 350 + ed.getLang('cfsnippet.delta_height', 0),
					inline : 1
				}, {
					plugin_url: url
				});
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
				version : "2.0"
			};
		}
	});
	tinymce.PluginManager.add('cfsnippets', tinymce.plugins.cfsnippets);
})();