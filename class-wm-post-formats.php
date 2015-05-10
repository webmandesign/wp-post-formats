<?php
/**
 * Get post formats media
 *
 * @package    ___THEME_NAME
 * @copyright  2015 WebMan - Oliver Juhas
 *
 * @since    ___SINCE
 * @version  ___VERSION
 */





if ( ! class_exists( 'WM_Post_Formats' ) ) {

	add_action( 'after_setup_theme', 'WM_Post_Formats::init' );

	/**
	 * Post formats media
	 *
	 * WordPress audio, gallery, image and video post format media generator.
	 *
	 * Custom hooks naming convention:
	 * - `wmhook_` - global (and other, such as plugins related) hooks
	 * - `wmhook_wmpf_` - WM_Post_Formats class specific hooks
	 *
	 * @copyright  2015 WebMan - Oliver Juhas
	 * @license    GPL-2.0+, http://www.gnu.org/licenses/gpl-2.0.html
	 *
	 * @link  https://github.com/webmandesign/wp-post-formats
	 * @link  http://www.webmandesign.eu
	 *
	 * @version    2.0
	 *
	 *
	 * GENERATED MEDIA
	 * ===============
	 *
	 * Media generated from post content for supported post formats:
	 *
	 *   Audio post format
	 *   - first `[audio]` or `[playlist]` shortcode
	 *   - or first embed media URL
	 *
	 *   Gallery post format
	 *   - coma separeted string of image IDs from first `[gallery]` shortcode
	 *   - or coma separated string of attached images IDs
	 *
	 *   Image post format
	 *   (Only if no featured image set:)
	 *   - ID of the first image in post content (for uploaded images)
	 *   - or URL of the first image in post content
	 *
	 *   Video post format
	 *   - first `[video]`, `[playlist]` or `[wpvideo]` shortcode
	 *   - or first embed media URL
	 *
	 *
	 * CUSTOM META FIELDS
	 * ==================
	 *
	 * If no media saved in custom meta field, these functions will attempt to
	 * generate the media and save them in a hidden custom meta field.
	 * Also, regeneration occurs on every post saving or update.
	 * You can override the generated media with a custom `post_format_media`
	 * custom meta field setup.
	 *
	 * @link  http://codex.wordpress.org/Custom_Fields
	 *
	 *
	 * IMPLEMENTATION
	 * ==============
	 *
	 * Copy this file into your WordPress theme's root directory and inlcude
	 * it in your theme's `funstions.php` file like so:
	 *
	 *   get_template_part( 'post-formats' );
	 *
	 * Then, use this code in your `content-audio.php` file (for example):
	 *
	 *   $post_format_media = wm_get_post_format_media();
	 *   if ( 0 === strpos( $post_format_media, '[' ) ) {
	 *     $post_format_media = do_shortcode( $post_format_media );
	 *   } else {
	 *     $post_format_media = wp_oembed_get( $post_format_media );
	 *   }
	 *   echo $post_format_media;
	 *
	 *
	 * OTHER NOTES
	 * ===========
	 *
	 * Please note that this file does not register post formats for your theme.
	 * Register post formats in your theme according to WordPress Codex instructions:
	 *
	 * @link  http://codex.wordpress.org/Post_Formats#Adding_Theme_Support
	 * @link  http://codex.wordpress.org/Post_Formats
	 */
	final class WM_Post_Formats {

		/**
		 * Contents:
		 *
		 *  10) Init
		 *  20) Getter
		 *  30) Core
		 * 100) Helpers
		 */





		/**
		 * 10) Init
		 */

			/**
			 * Class initialization
			 *
			 * @since    1.0
			 * @version  2.0
			 */
			static public function init() {

				//Hooks

					/**
					 * Actions
					 */

						//Generate post format media meta field on post save

							add_action( 'save_post', 'WM_Post_Formats::format_media' );

						//Flushing transients

							if ( class_exists( 'WM_Theme_Framework' ) ) {
								add_action( 'switch_theme', 'WM_Theme_Framework::image_ids_transient_flusher' );
							}

			} // /init





		/**
		 * 20) Getter
		 */

			/**
			 * Get the post format media
			 *
			 * Supported post formats: audio, gallery, image, video.
			 * Must be inside the loop.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $format
			 */
			static public function get( $format = null ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmpf_get_pre', false, $format );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					if ( empty( $format ) ) {
						$format = get_post_format();
					}


				//Output

					return self::format_media( get_the_ID(), $format );

			} // /get





		/**
		 * 30) Core
		 */

			/**
			 * Get/set the post format media
			 *
			 * If not set already, get the post media from the post content and save
			 * it in a hidden custom meta field. But, allow user to bypass by setting
			 * a `post_format_media` custom meta field, too.
			 *
			 * The function is triggered also on every post save to refresh the hidden
			 * post media custom meta field.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  int    $post_id
			 * @param  string $format
			 */
			static public function format_media( $post_id = null, $format = null ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmpf_format_media_pre', false, $post_id, $format );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if ( empty( $post_id ) ) {
						$post_id = get_the_ID();
					}
					if (
							empty( $post_id )
							//Exit early for no-post_format post types
							|| ( is_admin() && isset( $_REQUEST ) && ! isset( $_REQUEST['post_format'] ) )
						) {
						return false;
					}


				//Helper variables

					$post_id   = absint( $post_id );
					$format    = ( empty( $format ) ) ? ( get_post_format( $post_id ) ) : ( $format );
					$meta_name = apply_filters( 'wmhook_wmpf_format_media_meta_name', 'post_format_media' );

					$supported_formats = apply_filters( 'wmhook_wmpf_format_media_formats', array( 'audio', 'gallery', 'image', 'video' ) );

					//Requirements check

						if ( ! in_array( $format, $supported_formats ) ) {
							return;
						}

					//Allow users to set custom field first

						$output = get_post_meta( $post_id, $meta_name, true );

					//If no user custom field set, get the previously generated one (from hidden custom field)

						if ( empty( $output ) ) {
							$output = get_post_meta( $post_id, '_' . $meta_name, true );
						}

					//Premature output filtering

						$output = apply_filters( 'wmhook_wmpf_format_media_output_pre', $output, $post_id, $format );

					//Force refresh (regenerate and resave) the post media meta field

						if (
								//When forced by hook
								apply_filters( 'wmhook_wmpf_format_media_force_refresh', false, $post_id, $format )
								//When no media saved
								|| empty( $output )
								//When saving post (no need for checking nonce as this can be triggered anywhere...)
								|| (
										is_admin()
										&& current_user_can( 'edit_posts', $post_id )
										&& ! wp_is_post_revision( $post_id )
										&& isset( $_REQUEST )
										&& ! empty( $_REQUEST )
										&& isset( $_REQUEST['post_format'] )
										&& ! empty( $_REQUEST['post_format'] )
									)
							) {
							$output = '';
						}

					//Return if we have output

						if ( $output ) {
							return apply_filters( 'wmhook_wmpf_format_media_output', $output, $post_id, $format );
						}


				//Processing

					/**
					 * This is being triggered only when forced to refresh
					 */

						switch ( $format ) {

							case 'audio':
							case 'video':

									$output = self::get_media_audio_video( $post_id );

							break;
							case 'gallery':

									$output = self::get_media_gallery( $post_id );

							break;
							case 'image':

									$output = self::get_media_image( $post_id );

							break;

							default:
							break;

						} // /switch

						//Filter the output

							$output = apply_filters( 'wmhook_wmpf_format_media_output', $output, $post_id, $format );

						//Save the post media meta field

							update_post_meta( $post_id, '_' . $meta_name, $output );

						//Custom action hook

							do_action( 'wmhook_wmpf_format_media', $output, $post_id, $format, $meta_name );


				//Output

					return $output;

			} // /format_media





		/**
		 * 100) Helpers
		 */

			/**
			 * Get the post format media: audio, video
			 *
			 * Searches for media shortcode or URL in the post content.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  int $post_id
			 *
			 * @return  Audio/video/playlist shortcode or oembed media URL.
			 */
			static public function get_media_audio_video( $post_id ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmpf_get_media_audio_video_pre', false, $post_id );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if ( empty( $post_id ) ) {
						return;
					}


				//Helper variables

					$output  = '';
					$post    = get_post( $post_id );
					$content = $post->post_content;
					$pattern = ( 'video' == get_post_format( $post_id ) ) ? ( 'video|playlist|wpvideo' ) : ( 'audio|playlist' );


				//Processing

					/**
					 * Info:
					 *
					 * preg_match() sufixes:
					 * @link  http://php.net/manual/en/function.preg-match.php#102214
					 * @link  http://php.net/manual/en/function.preg-match.php#111573
					 */

					//Search for the media

						$pattern = '/\[(' . $pattern . ')(.*)\]/u';

						preg_match( $pattern, strip_tags( $content ), $matches );

						if ( isset( $matches[0] ) ) {

							$output = trim( $matches[0] );

							//If [playlist], use the first media
							/*
								if ( false !== strpos( $output, 'ids="' ) ) {
									preg_match( '/ids="(.+?)"/', $output, $matches );

									$output = explode( ',', str_replace( array( 'ids="', '"', ' ' ), '', $matches[0] ) );
									$output = '[' . get_post_format() . ' src="' . wp_get_attachment_url( absint( $output[0] ) ) . '" /]';
								}
							*/

						} elseif ( false !== strpos( $content, 'http' ) ) {

							//If no prioritized shortcode found, look for oembed media URL

								$pattern = '/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';

								preg_match_all( $pattern, strip_tags( $content ), $matches );

							//Return only the first URL which is actually oembed one

								if ( isset( $matches[0] ) && is_array( $matches[0] ) ) {
									$matches = array_unique( $matches[0] );

									foreach ( $matches as $url ) {
										if ( wp_oembed_get( esc_url( $url ) ) ) {
											$output = $url;
											break;
										}
									} // /foreach
								}

						}


				//Output

					return $output;

			} // /get_media_audio_video



			/**
			 * Get the post format media: gallery
			 *
			 * Get images from the first [gallery] shortcode found in the post content
			 * or get images attached to the post.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  int $post_id
			 *
			 * @return  Comma separated gallery images IDs string.
			 */
			static public function get_media_gallery( $post_id ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmpf_get_media_gallery_pre', false, $post_id );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if ( empty( $post_id ) ) {
						return;
					}


				//Helper variables

					$output  = '';
					$post    = get_post( $post_id );
					$content = $post->post_content;


				//Processing

					/**
					 * Info:
					 *
					 * preg_match() sufixes:
					 * @link  http://php.net/manual/en/function.preg-match.php#102214
					 * @link  http://php.net/manual/en/function.preg-match.php#111573
					 */

					//Search for the media

						$pattern = '/\[gallery(.*)\]/u';

						preg_match( $pattern, strip_tags( $content ), $matches );

						//Get [gallery] shortcode parameters only

							if ( isset( $matches[1] ) ) {
								$output = trim( $matches[1] );
							}

					//Get image IDs array: from shortcode attribute or attached images

						if ( false !== strpos( $output, 'ids="' ) ) {

							preg_match( '/ids="(.+?)"/u', $output, $matches );

							$output = str_replace( array( 'ids="', '"', ' ' ), '', $matches[0] );

						} else {

							$output = implode( ',', array_keys( (array) get_children( apply_filters( 'wmhook_wmpf_get_media_gallery_get_children_args', array(
									'post_parent'    => $post_id,
									'post_status'    => 'inherit',
									'post_type'      => 'attachment',
									'post_mime_type' => 'image',
									'order'          => 'ASC',
									'orderby'        => 'menu_order'
								) ) ) ) );

						}

					//Make shure we output array if we have the images

						if ( ! empty( $output ) ) {
							$output = trim( (string) $output );
						}


				//Output

					return $output;

			} // /get_media_gallery



			/**
			 * Get the post format media: image
			 *
			 * Searches for the image in the post content only if featured image not set.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  int $post_id
			 *
			 * @return  Image ID (for uploaded image) or image URL.
			 */
			static public function get_media_image( $post_id ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmpf_get_media_image_pre', false, $post_id );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check
					if (
							empty( $post_id )
							|| has_post_thumbnail( $post_id )
						) {
						return;
					}


				//Helper variables

					$output  = '';
					$post    = get_post( $post_id );
					$content = $post->post_content;


				//Processing

					/**
					 * Info:
					 *
					 * preg_match() sufixes:
					 * @link  http://php.net/manual/en/function.preg-match.php#example-4907
					 */

					//Search for the media

						$pattern = '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i';

						preg_match( $pattern, $content, $matches );

						if ( isset( $matches[1] ) ) {
							$output = trim( $matches[1] );
						}

					//Get the image ID if the image is uploaded, otherwise output the URL

						if (
								class_exists( 'WM_Theme_Framework' )
								&& ( $image_id = WM_Theme_Framework::get_image_id_from_url( $output ) )
							) {
							$output = $image_id;
						}


				//Output

					return $output;

			} // /get_media_image

	}
} // /WM_Post_Formats
