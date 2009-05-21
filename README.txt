# CF Snippets Plugin

The CF Snippets plugin allows users to define snippets for use post content and theme templates. Also defines a widget to allow snippets to be added to the sidebar.


## Usage

The Snippets admin page allows for the creation of multiple snippets. From the plugin page:

	Paste in HTML content for a snippet and give it a name. The name will be automatically "sanitized:" lowercased and all spaces converted to dashes.

	To insert a snippet in your template, type `<?php cfsnip_snippet('my-snippet-name'); ?>`
	Use the shortcode syntax: `[cfsnip name="my-snippet-name"]` in post or page content to insert your snippet there.

	Or use snippet widgets wherever widgets can be used.

	To access files in your current theme template directory from within a snippet, type `{cfsnip_template_url}`. That will be replaced with, for example, `http://example.com/wordpress/wp-content/themes/mytheme/`.
	

## Template Tags & Default Values

Default values for snippets can now be defined through the template tags for pulling snippet content. If the snippet does not exist the snippet will be created so that it can be changed, if desired, via the WordPress admin. The 'create if not exists' behavior can be overidden. The affected functions are:

- `cfsnip_snippet`
- `cfsnip_snippet_content`
- `cfsnip_get_snippet`
- `cfsnip_get_snippet_content`

Each function takes the same parameters

- `$snippet_name`: string, name of the snippet being pulled
- `$default_value`: a default value to use if the snippet does not exist. 
	- Default is false
- `$create_snippet_if_not_exists`: wether to create a snippet if a snippet does not exist and a default value is provided
	- Default is true

**Example:**
	
	<div><?php cfsnip_snippet('my-snippet','default value'); ?></div>
	

## Shortcodes

Snippets can be addressed via shortcode as well. `[cfsnip name="my-snippet-name"]` will pull the snippet `my-snippet-name`. Default values are not applicable to shortcodes.

There is a Snippet button in the post/page content edit bar. Click on the "cog" icon to bring up a list of snippets to insert at the cursor point in the contnet.


## Widgets

A multi-instance widget is added by the plugin. It provides the ability to add multiple widgets to the sidebar, provide an optional title and select a widget to display. Only one widget can be selected in a widget at a time.