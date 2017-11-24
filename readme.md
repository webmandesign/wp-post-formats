# Post formats media

**WordPress audio, gallery, image and video post format media generator.**

* **License**: GPL-2.0+
* **Copyright**: WebMan Design, Oliver Juhas, https://www.webmandesign.eu


## Generated media

Media generated from post content for supported post formats:


**Audio post format**

* the first `[audio]` or `[playlist]` shortcode found,
* or the first embed media URL.


**Gallery post format**

* string of coma separated list of image IDs from the first `[gallery]` shortcode found.


**Image post format**

* ID of the first image found in the post content,
* or just the URL of that image, if it is not found in media library.


**Video post format**

* the first `[video]`, `[playlist]` or `[wpvideo]` shortcode found,
* or the first embed media URL.


## Custom meta fields

If no media saved in custom meta field, this script will attempt to generate the media and save them in a hidden custom meta field for the specific post. Regeneration of the custom field also occurs on every post save or update action.

You can override the generated media by setting a `post_format_media` custom meta field for the specific post (http://codex.wordpress.org/Custom_Fields).


## Implementation example

Copy the `class-post-formats.php` file into your WordPress theme's root directory and inlcude it in your theme's `funstions.php` file like so:

	require_once 'class-post-formats.php';

Then, use this code in your `content-audio.php` file, for example:

	$post_format_media = (string) {%= prefix_class %}_Post_Formats::get();

	if ( 0 === strpos( $post_format_media, '[' ) ) {
		$post_format_media = do_shortcode( $post_format_media );
	} else {
		$post_format_media = wp_oembed_get( $post_format_media );
	}

	echo $post_format_media;

Do not forget to replace all development variables (the `{%= variable_name %}` string) with the actual values for your theme.


## Other notes

Please note that this file does not register no post format for your theme.
You should register those in your theme according to WordPress Codex instructions:

* http://codex.wordpress.org/Post_Formats#Adding_Theme_Support
* http://codex.wordpress.org/Post_Formats
