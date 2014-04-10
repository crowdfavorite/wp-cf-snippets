## CF Snippets

The CF Snippets plugin gives Admin users the ability to create chunks of content (including HTML content) to be inserted into posts, widgets and front end display with an easy to use Admin interface.  This functionality gives the Admin users easy ability to edit the chunks of code without editing PHP/HTML files.  The plugin provides PHP functions for display of Snippets, as well as WordPress shortcodes.  On the post edit screen, the plugin provides a TinyMCE button for easy insertion of Snippets shortcodes.

** NOTE: Plugin requires WordPress 3.8 **

### Usage

Edit a post you'd like to use a snippet in.
If you know a snippet you're looking for, begin typing its key in the text box, then select the one you want from the suggestions.
If the snippet doesn't yet exist, click "Add New Snippet" and fill in the fields there, then Save.
Once that is done, in the post content area, put your cursor where you'd like the snippet added.
Click the "Add to Content" button and a shortcode will be added in the location of the cursor.

#### Alternate management

Go to the "Snippets" post type in admin.
From here, you can edit, create, and delete snippets through the normal post admin interfaces.


## Programmatic use and shortcodes

	To insert a snippet in your template, type <?php cfsp_content('my-snippet-name'); ?>
	Use the shortcode syntax: [cfsp name="my-snippet-name"] in post or page content to insert your snippet there.

	Or use snippet widgets wherever widgets can be used.

	To access files in your current theme template directory from within a snippet, type {cfsp_template_url}. That will be replaced with, for example, `http://example.com/wordpress/wp-content/themes/mytheme/`.

### Template Tags

The Snippets plugin provides PHP "template tags" for easy display of Snippet data.  The functions will display content based on snippet key.  The functions also provide the ability to automatically create a snippet if one does not exist.

The following function will echo the content for `snippet-1`:

	<?php cfsp_content('snippet-1'); ?>

Or to just get the snippet content without echoing:

	<?php $snippet = cfsp_get_content('snippet-1'); ?>
	
The function will also create the snippet with some default content, then display the content.  When the snippet is created it will have a default description of "Snippet 1":

	<?php cfsp_content('snippet-1', 'this is the snippet content for snippet-1', true); ?>
	
Or to just get the snippet content without echoing:

	<?php $snippet = cfsp_get_content('snippet-1', 'this is the snippet content for snippet-1', true); ?>

The function can also allows the user to create a custom description when creating the default snippet:

	<?php cfsp_content('snippet-1', 'this is the snippet content for snippet-1', true, array('description' => 'Description for Snippet 1')); ?>
	
Or to just get the snippet content without echoing:

	<?php $snippet = cfsp_get_content('snippet-1', 'this is the snippet content for snippet-1', true, array('description' => 'Description for Snippet 1')); ?>

### Shortcodes

The Snippets plugin also provides WordPress shortcodes for easy display of the Snippet data.  The shortcode will display data based on snippet key.  The shortcode does not provide the ability to create the snippet if it does not exist.  So if a snippet is to be displayed it will need to be created before it can be displayed.

To display a snippet in WordPress post data, simply add:
	
	[cfsp name="snippet-1"]
	
The plugin also provides a TinyMCE button to the WYSIWYG on the post edit screen.  The "cog" icon can be clicked and then a snippet selected to have the shortcode displayed.  

To add the snippet shortcode:

- Place the cursor in the WYSIWYG where the snippet should be displayed
- Click the "cog" icon
- Click on the Snippet Description that is desired to be displayed

### Widgets

The Snippets plugin also provides WordPress Widgets for easy display of Snippet data.  The Widget will display content based on a snippet selected from a drop down menu on the Widget admin page.  The Widget also provides a Title section for compliance with custom themes.

To add the Snippet Widget:

- Navigate to the Widgets admin page under the Appearance section of the WordPress Admin Navigation
- Click on the CF Snippets widget and drag it into the desired place in the desired sidebar
- Add a title (if desired)
- Select the Snippet to be displayed
- Click the Save button to save changes

For more information on how to use WordPress widgets, see this Documentation: http://en.support.wordpress.com/widgets/

