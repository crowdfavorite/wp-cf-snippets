# CF Snippets Plugin

The CF Snippets plugin allows users to define snippets for use post content and theme templates. Also defines a widget to allow snippets to be added to the sidebar.


## Usage

The Snippets admin page allows for the creation of multiple snippets. From the plugin page:

	Paste in HTML content for a snippet and give it a name. The name will be automatically "sanitized:" lowercased and all spaces converted to dashes.

	To insert a snippet in your template, type `<?php cfsnip_snippet('my-snippet-name'); ?>`
	Use the shortcode syntax: `[cfsnip name="my-snippet-name"]` in post or page content to insert your snippet there.

	Or use snippet widgets wherever widgets can be used.

	To access files in your current theme template directory from within a snippet, type `{cfsnip_template_url}`. That will be replaced with, for example, `http://example.com/wordpress/wp-content/themes/mytheme/`.
	

## Widgets

A multi-instance widget is added by the plugin. It provides the ability to add multiple widgets to the sidebar, provide an optional title and select a widget to display. Only one widget can be selected in a widget at a time.