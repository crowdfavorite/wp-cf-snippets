<?php
global $post;
$disabled = empty($post->post_name) ? '' : ' disabled="disabled" style="border:none; box-shadow:none;"';
?>
<input type="text" name="cf_snippet_post_name" value="<?php echo esc_attr($post->post_name); ?>"<?php echo $disabled; ?> />