# Post formats media

**WordPress audio, gallery, image and video post format media generator.**

* **License**: GPL-2.0+
* **Copyright**: 2014 WebMan - Oliver Juhas, http://www.webmandesign.eu

## Generated media

Media generated from post content for supported post formats:

### Audio post format

* first `[audio]` or `[playlist]` shortcode
* or first embed media URL

### Gallery post format

* coma separeted string of image IDs from first `[gallery]` shortcode
* or coma separated string of attached images IDs

### Image post format

*Only if NO featured image set:*
* ID of the first image in post content (for uploaded images, and only if `wm_get_image_id_from_url()` function exists)
* or URL of the first image in post content

### Video post format

* first `[video]`, `[playlist]` or `[wpvideo]` shortcode
* or first embed media URL

## Custom meta fields

If no media saved in custom meta field, these functions will attempt to generate the media and save them in a hidden custom meta field.

Also, regeneration occurs on every post saving or update.

You can override the generated media with a custom `post_format_media` custom meta field setup (http://codex.wordpress.org/Custom_Fields).

## Implementation

Copy this file into your WordPress theme's root directory and inlcude it in your theme's `funstions.php` file like so:

	get_template_part( 'post-formats' );

Use this code in your `content-audio.php` file (for example):

	$post_format_media = wm_get_post_format_media();

	if ( 0 === strpos( $post_format_media, '[' ) ) {
		$post_format_media = do_shortcode( $post_format_media );
	} else {
		$post_format_media = wp_oembed_get( $post_format_media );
	}

	echo $post_format_media;

## Other notes

Please note that this file does not register post formats for your theme. Register post formats in your theme according to WordPress Codex instructions:

* http://codex.wordpress.org/Post_Formats#Adding_Theme_Support
* http://codex.wordpress.org/Post_Formats