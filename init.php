<?php
/*
Script Name: 	Custom Metaboxes and Fields
Contributors: 	Andrew Norcross (@norcross / andrewnorcross.com)
				Jared Atchison (@jaredatch / jaredatchison.com)
				Bill Erickson (@billerickson / billerickson.net)
				Justin Sternberg (@jtsternberg / dsgnwrks.pro)
Description: 	This will create metaboxes with custom fields that will blow your mind.
Version: 		0.9.2
*/

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/************************************************************************
		You should not edit the code below or things might explode!
*************************************************************************/

$meta_boxes = array();
$meta_boxes = apply_filters ( 'cmb_meta_boxes' , $meta_boxes );
foreach ( $meta_boxes as $meta_box ) {
	$my_box = new cmb_Meta_Box( $meta_box );
}

/**
 * Validate value of meta fields
 * Define ALL validation methods inside this class and use the names of these
 * methods in the definition of meta boxes (key 'validate_func' of each field)
 */
class cmb_Meta_Box_Validate {
	function check_text( $text ) {
		if ($text != 'hello') {
			return false;
		}
		return true;
	}
}

/**
 * Defines the url to which is used to load local resources.
 * This may need to be filtered for local Window installations.
 * If resources do not load, please check the wiki for details.
 */
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
       //winblows
    define( 'CMB_META_BOX_URL', trailingslashit( str_replace( DIRECTORY_SEPARATOR, '/', str_replace( str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR ), WP_CONTENT_URL, dirname(__FILE__) ) ) ) );

} else {
    define( 'CMB_META_BOX_URL', apply_filters( 'cmb_meta_box_url', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) ) ) ) );
}

/**
 * Create meta boxes
 */
class cmb_Meta_Box {
	protected $_meta_box;

	function __construct( $meta_box ) {
		if ( !is_admin() ) return;

		$this->_meta_box = $meta_box;

		$upload = false;
		foreach ( $meta_box['fields'] as $field ) {
			if ( $field['type'] == 'file' || $field['type'] == 'file_list' ) {
				$upload = true;
				break;
			}
		}

		global $pagenow;
		if ( $upload && in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) ) {
			add_action( 'admin_head', array( &$this, 'add_post_enctype' ) );
		}

		add_action( 'admin_menu', array( &$this, 'add' ) );
		add_action( 'save_post', array( &$this, 'save' ) );

		add_filter( 'cmb_show_on', array( &$this, 'add_for_id' ), 10, 2 );
		add_filter( 'cmb_show_on', array( &$this, 'add_for_page_template' ), 10, 2 );
	}

	function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#post").attr("enctype", "multipart/form-data");
			jQuery("#post").attr("encoding", "multipart/form-data");
		});
		</script>';
	}

	// Add metaboxes
	function add() {
		$this->_meta_box['context'] = empty($this->_meta_box['context']) ? 'normal' : $this->_meta_box['context'];
		$this->_meta_box['priority'] = empty($this->_meta_box['priority']) ? 'high' : $this->_meta_box['priority'];
		$this->_meta_box['show_on'] = empty( $this->_meta_box['show_on'] ) ? array('key' => false, 'value' => false) : $this->_meta_box['show_on'];

		foreach ( $this->_meta_box['pages'] as $page ) {
			if( apply_filters( 'cmb_show_on', true, $this->_meta_box ) )
				add_meta_box( $this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']) ;
		}
	}

	/**
	 * Show On Filters
	 * Use the 'cmb_show_on' filter to further refine the conditions under which a metabox is displayed.
	 * Below you can limit it by ID and page template
	 */

	// Add for ID
	function add_for_id( $display, $meta_box ) {
		if ( 'id' !== $meta_box['show_on']['key'] )
			return $display;

		// If we're showing it based on ID, get the current ID
		if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
		elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
		if( !isset( $post_id ) )
			return false;

		// If value isn't an array, turn it into one
		$meta_box['show_on']['value'] = !is_array( $meta_box['show_on']['value'] ) ? array( $meta_box['show_on']['value'] ) : $meta_box['show_on']['value'];

		// If current page id is in the included array, display the metabox

		if ( in_array( $post_id, $meta_box['show_on']['value'] ) )
			return true;
		else
			return false;
	}

	// Add for Page Template
	function add_for_page_template( $display, $meta_box ) {
		if( 'page-template' !== $meta_box['show_on']['key'] )
			return $display;

		// Get the current ID
		if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
		elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
		if( !( isset( $post_id ) || is_page() ) ) return false;

		// Get current template
		$current_template = get_post_meta( $post_id, '_wp_page_template', true );

		// If value isn't an array, turn it into one
		$meta_box['show_on']['value'] = !is_array( $meta_box['show_on']['value'] ) ? array( $meta_box['show_on']['value'] ) : $meta_box['show_on']['value'];

		// See if there's a match
		if( in_array( $current_template, $meta_box['show_on']['value'] ) )
			return true;
		else
			return false;
	}

	// Show fields
	function show() {

		global $post;

		// Use nonce for verification
		echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';
		echo '<table class="form-table cmb_metabox">';

		foreach ( $this->_meta_box['fields'] as $field ) {
			// Set up blank or default values for empty ones
			if ( !isset( $field['name'] ) ) $field['name'] = '';
			if ( !isset( $field['desc'] ) ) $field['desc'] = '';
			if ( !isset( $field['std'] ) ) $field['std'] = '';
			if ( 'file' == $field['type'] && !isset( $field['allow'] ) ) $field['allow'] = array( 'url', 'attachment' );
			if ( 'file' == $field['type'] && !isset( $field['save_id'] ) )  $field['save_id']  = false;
			if ( 'multicheck' == $field['type'] ) $field['multiple'] = true;

			$meta = get_post_meta( $post->ID, $field['id'], 'multicheck' != $field['type'] /* If multicheck this can be multiple values */ );

			echo '<tr>';

			if ( $field['type'] == "title" ) {
				echo '<td colspan="2">';
			} else {
				if( $this->_meta_box['show_names'] == true ) {
					echo '<th style="width:18%"><label for="', $field['id'], '">', $field['name'], '</label></th>';
				}
				echo '<td>';
			}

			switch ( $field['type'] ) {

				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" />','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'text_small':
					echo '<input class="cmb_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_medium':
					echo '<input class="cmb_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_date':
					echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_date_timestamp':
					echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? date( 'm\/d\/Y', $meta ) : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;

				case 'text_datetime_timestamp':
					echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], '[date]" id="', $field['id'], '_date" value="', '' !== $meta ? date( 'm\/d\/Y', $meta ) : $field['std'], '" />';
					echo '<input class="cmb_timepicker text_time" type="text" name="', $field['id'], '[time]" id="', $field['id'], '_time" value="', '' !== $meta ? date( 'h:i A', $meta ) : $field['std'], '" /><span class="cmb_metabox_description" >', $field['desc'], '</span>';
					break;
				case 'text_time':
					echo '<input class="cmb_timepicker text_time" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'text_money':
					echo '$ <input class="cmb_text_money" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'colorpicker':
					$meta = '' !== $meta ? $meta : $field['std'];
					$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
					if ( preg_match( '/^' . $hex_color . '/i', $meta ) ) // Value is just 123abc, so prepend #.
						$meta = '#' . $meta;
					elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta ) ) // Value doesn't match #123abc, so sanitize to just #.
						$meta = "#";
					echo '<input class="cmb_colorpicker cmb_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta, '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'textarea':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'textarea_small':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'textarea_code':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" class="cmb_textarea_code">', '' !== $meta ? $meta : $field['std'], '</textarea>','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'select':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					foreach ($field['options'] as $option) {
						echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
					}
					echo '</select>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio_inline':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<div class="cmb_radio_inline">';
					$i = 1;
					foreach ($field['options'] as $option) {
						echo '<div class="cmb_radio_inline_option"><input type="radio" name="', $field['id'], '" id="', $field['id'], $i, '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'], '</label></div>';
						$i++;
					}
					echo '</div>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'radio':
					if( empty( $meta ) && !empty( $field['std'] ) ) $meta = $field['std'];
					echo '<ul>';
					$i = 1;
					foreach ($field['options'] as $option) {
						echo '<li><input type="radio" name="', $field['id'], '" id="', $field['id'], $i,'" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $option['name'].'</label></li>';
						$i++;
					}
					echo '</ul>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
					echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'multicheck':
					echo '<ul>';
					$i = 1;
					foreach ( $field['options'] as $value => $name ) {
						// Append `[]` to the name to get multiple values
						// Use in_array() to check whether the current option should be checked
						echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], $i, '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label for="', $field['id'], $i, '">', $name, '</label></li>';
						$i++;
					}
					echo '</ul>';
					echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
					break;
				case 'title':
					echo '<h5 class="cmb_metabox_title">', $field['name'], '</h5>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'wysiwyg':
					wp_editor( $meta ? $meta : $field['std'], $field['id'], isset( $field['options'] ) ? $field['options'] : array() );
			        echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_select':
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ( $terms as $term ) {
						if (!is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<option value="' . $term->slug . '" selected>' . $term->name . '</option>';
						} else {
							echo '<option value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name . '</option>';
						}
					}
					echo '</select>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_radio':
					$names= wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					echo '<ul>';
					foreach ( $terms as $term ) {
						if ( !is_wp_error( $names ) && !empty( $names ) && !strcmp( $term->slug, $names[0]->slug ) ) {
							echo '<li><input type="radio" name="', $field['id'], '" value="'. $term->slug . '" checked>' . $term->name . '</li>';
						} else {
							echo '<li><input type="radio" name="', $field['id'], '" value="' . $term->slug . '  ' , $meta == $term->slug ? $meta : ' ' ,'  ">' . $term->name .'</li>';
						}
					}
					echo '</ul>';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					break;
				case 'taxonomy_multicheck':
					echo '<ul>';
					$names = wp_get_object_terms( $post->ID, $field['taxonomy'] );
					$terms = get_terms( $field['taxonomy'], 'hide_empty=0' );
					foreach ($terms as $term) {
						echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], '" value="', $term->name , '"';
						foreach ($names as $name) {
							if ( $term->slug == $name->slug ){ echo ' checked="checked" ';};
						}
						echo' /><label>', $term->name , '</label></li>';
					}
					echo '</ul>';
					echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
				break;
				case 'file_list':
					echo '<input class="cmb_upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
					echo '<input class="cmb_upload_button button" type="button" value="Upload File" />';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
						$args = array(
								'post_type' => 'attachment',
								'numberposts' => null,
								'post_status' => null,
								'post_parent' => $post->ID
							);
							$attachments = get_posts($args);
							if ($attachments) {
								echo '<ul class="attach_list">';
								foreach ($attachments as $attachment) {
									echo '<li>'.wp_get_attachment_link($attachment->ID, 'thumbnail', 0, 0, 'Download');
									echo '<span>';
									echo apply_filters('the_title', '&nbsp;'.$attachment->post_title);
									echo '</span></li>';
								}
								echo '</ul>';
							}
						break;
				case 'file':
					$input_type_url = "hidden";
					if ( 'url' == $field['allow'] || ( is_array( $field['allow'] ) && in_array( 'url', $field['allow'] ) ) )
						$input_type_url="text";
					echo '<input class="cmb_upload_file" type="' . $input_type_url . '" size="45" id="', $field['id'], '" name="', $field['id'], '" value="', $meta, '" />';
					echo '<input class="cmb_upload_button button" type="button" value="Upload File" />';
					echo '<input class="cmb_upload_file_id" type="hidden" id="', $field['id'], '_id" name="', $field['id'], '_id" value="', get_post_meta( $post->ID, $field['id'] . "_id",true), '" />';
					echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
					echo '<div id="', $field['id'], '_status" class="cmb_media_status">';
						if ( $meta != '' ) {
							$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
							if ( $check_image ) {
								echo '<div class="img_status">';
								echo '<img src="', $meta, '" alt="" />';
								echo '<a href="#" class="cmb_remove_file_button" rel="', $field['id'], '">Remove Image</a>';
								echo '</div>';
							} else {
								$parts = explode( '/', $meta );
								for( $i = 0; $i < count( $parts ); ++$i ) {
									$title = $parts[$i];
								}
								echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <a href="#" class="cmb_remove_file_button" rel="', $field['id'], '">Remove</a>)';
							}
						}
					echo '</div>';
				break;
				case 'oembed':
					echo '<input class="cmb_oembed" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" />','<p class="cmb_metabox_description">', $field['desc'], '</p>';
					echo '<p class="cmb-spinner spinner"></p>';
					echo '<div id="', $field['id'], '_status" class="cmb_media_status ui-helper-clearfix embed_wrap">';
						if ( $meta != '' ) {
							$check_embed = $GLOBALS['wp_embed']->run_shortcode( '[embed]'. esc_url( $meta ) .'[/embed]' );
							if ( $check_embed ) {
								echo '<div class="embed_status">';
								echo $check_embed;
								echo '<a href="#" class="cmb_remove_file_button" rel="', $field['id'], '">Remove Embed</a>';
								echo '</div>';
							} else {
								echo 'URL is not a valid oEmbed URL.';
							}
						}
					echo '</div>';
					break;

				default:
					do_action('cmb_render_' . $field['type'] , $field, $meta);
			}

			echo '</td>','</tr>';
		}
		echo '</table>';
	}

	// Save data from metabox
	function save( $post_id)  {

		// verify nonce
		if ( ! isset( $_POST['wp_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wp_meta_box_nonce'], basename(__FILE__) ) ) {
			return $post_id;
		}

		// check autosave
		if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// get the post types applied to the metabox group
		// and compare it to the post type of the content
		$post_type = get_post_type($post_id);
		$meta_type = $this->_meta_box['pages'];
		$type_comp = in_array($post_type, $meta_type) ? true : false;

		foreach ( $this->_meta_box['fields'] as $field ) {
			$name = $field['id'];

			if ( ! isset( $field['multiple'] ) )
				$field['multiple'] = ( 'multicheck' == $field['type'] ) ? true : false;

			$old = get_post_meta( $post_id, $name, !$field['multiple'] /* If multicheck this can be multiple values */ );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;

			if ( $type_comp == true && in_array( $field['type'], array( 'taxonomy_select', 'taxonomy_radio', 'taxonomy_multicheck' ) ) )  {
				$new = wp_set_object_terms( $post_id, $new, $field['taxonomy'] );
			}

			if ( ($field['type'] == 'textarea') || ($field['type'] == 'textarea_small') ) {
				$new = htmlspecialchars( $new );
			}

			if ( ($field['type'] == 'textarea_code') ) {
				$new = htmlspecialchars_decode( $new );
			}

			if ( $type_comp == true && $field['type'] == 'text_date_timestamp' ) {
				$new = strtotime( $new );
			}

			if ( $type_comp == true && $field['type'] == 'text_datetime_timestamp' ) {
				$string = $new['date'] . ' ' . $new['time'];
				$new = strtotime( $string );
			}

			$new = apply_filters('cmb_validate_' . $field['type'], $new, $post_id, $field);

			// validate meta value
			if ( isset( $field['validate_func']) ) {
				$ok = call_user_func( array( 'cmb_Meta_Box_Validate', $field['validate_func']), $new );
				if ( $ok === false ) { // pass away when meta value is invalid
					continue;
				}
			} elseif ( $field['multiple'] ) {
				delete_post_meta( $post_id, $name );
				if ( !empty( $new ) ) {
					foreach ( $new as $add_new ) {
						add_post_meta( $post_id, $name, $add_new, false );
					}
				}
			} elseif ( '' !== $new && $new != $old  ) {
				update_post_meta( $post_id, $name, $new );
			} elseif ( '' == $new ) {
				delete_post_meta( $post_id, $name );
			}

			if ( 'file' == $field['type'] ) {
				$name = $field['id'] . "_id";
				$old = get_post_meta( $post_id, $name, !$field['multiple'] /* If multicheck this can be multiple values */ );
				if ( isset( $field['save_id'] ) && $field['save_id'] ) {
					$new = isset( $_POST[$name] ) ? $_POST[$name] : null;
				} else {
					$new = "";
				}

				if ( $new && $new != $old ) {
					update_post_meta( $post_id, $name, $new );
				} elseif ( '' == $new && $old ) {
					delete_post_meta( $post_id, $name, $old );
				}
			}
		}
	}
}

/**
 * Adding scripts and styles
 */
function cmb_scripts( $hook ) {
	global $wp_version;
	// only enqueue our scripts/styles on the proper pages
	if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
		// scripts required for cmb
		$cmb_script_array = array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'media-upload', 'thickbox' );
		// styles required for cmb
		$cmb_style_array = array( 'thickbox' );
		// if we're 3.5 or later, user wp-color-picker
		if ( 3.5 <= $wp_version ) {
			$cmb_script_array[] = 'wp-color-picker';
			$cmb_style_array[] = 'wp-color-picker';
		} else {
			// otherwise use the older 'farbtastic'
			$cmb_script_array[] = 'farbtastic';
			$cmb_style_array[] = 'farbtastic';
		}
		wp_register_script( 'cmb-timepicker', CMB_META_BOX_URL . 'js/jquery.timePicker.min.js' );
		wp_register_script( 'cmb-scripts', CMB_META_BOX_URL . 'js/cmb.js', $cmb_script_array, '0.9.1' );
		wp_localize_script( 'cmb-scripts', 'cmb_ajax_data', array( 'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ), 'post_id' => get_the_ID() ) );
		wp_enqueue_script( 'cmb-timepicker' );
		wp_enqueue_script( 'cmb-scripts' );
		wp_register_style( 'cmb-styles', CMB_META_BOX_URL . 'style.css', $cmb_style_array );
		wp_enqueue_style( 'cmb-styles' );
	}
}
add_action( 'admin_enqueue_scripts', 'cmb_scripts', 10 );

function cmb_editor_footer_scripts() { ?>
	<?php
	if ( isset( $_GET['cmb_force_send'] ) && 'true' == $_GET['cmb_force_send'] ) {
		$label = $_GET['cmb_send_label'];
		if ( empty( $label ) ) $label="Select File";
		?>
		<script type="text/javascript">
		jQuery(function($) {
			$('td.savesend input').val('<?php echo $label; ?>');
		});
		</script>
		<?php
	}
}
add_action( 'admin_print_footer_scripts', 'cmb_editor_footer_scripts', 99 );

// Force 'Insert into Post' button from Media Library
add_filter( 'get_media_item_args', 'cmb_force_send' );
function cmb_force_send( $args ) {

	// if the Gallery tab is opened from a custom meta box field, add Insert Into Post button
	if ( isset( $_GET['cmb_force_send'] ) && 'true' == $_GET['cmb_force_send'] )
		$args['send'] = true;

	// if the From Computer tab is opened AT ALL, add Insert Into Post button after an image is uploaded
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {

		$args['send'] = true;

		// TO DO: Are there any conditions in which we don't want the Insert Into Post
		// button added? For example, if a post type supports thumbnails, does not support
		// the editor, and does not have any cmb file inputs? If so, here's the first
		// bits of code needed to check all that.
		// $attachment_ancestors = get_post_ancestors( $_POST["attachment_id"] );
		// $attachment_parent_post_type = get_post_type( $attachment_ancestors[0] );
		// $post_type_object = get_post_type_object( $attachment_parent_post_type );
	}

	// change the label of the button on the From Computer tab
	if ( isset( $_POST['attachment_id'] ) && '' != $_POST["attachment_id"] ) {

		echo '
			<script type="text/javascript">
				function cmbGetParameterByNameInline(name) {
					name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
					var regexS = "[\\?&]" + name + "=([^&#]*)";
					var regex = new RegExp(regexS);
					var results = regex.exec(window.location.href);
					if(results == null)
						return "";
					else
						return decodeURIComponent(results[1].replace(/\+/g, " "));
				}

				jQuery(function($) {
					if (cmbGetParameterByNameInline("cmb_force_send")=="true") {
						var cmb_send_label = cmbGetParameterByNameInline("cmb_send_label");
						$("td.savesend input").val(cmb_send_label);
					}
				});
			</script>
		';
	}

    return $args;

}

add_action( 'wp_ajax_cmb_oembed_handler', 'cmb_oembed_ajax_results' );
/**
 * Handles our oEmbed ajax request
 */
function cmb_oembed_ajax_results() {

	// verify our nonce
	if ( ! ( isset( $_REQUEST['cmb_ajax_nonce'], $_REQUEST['oembed_url'] ) && wp_verify_nonce( $_REQUEST['cmb_ajax_nonce'], 'ajax_nonce' ) ) )
		die();

	// sanitize our search string
	$oembed_string = sanitize_text_field( $_REQUEST['oembed_url'] );

	if ( empty( $oembed_string ) ) {
		$return = '<p class="ui-state-error-text">'. __( 'Please Try Again', 'cmb' ) .'</p>';
		$found = 'not found';
	} else {

		global $wp_embed;

		$oembed_url = esc_url( $oembed_string );
		// Post ID is needed to check for embeds
		if ( isset( $_REQUEST['post_id'] ) )
			$GLOBALS['post'] = get_post( $_REQUEST['post_id'] );
		// ping WordPress for an embed
		$check_embed = $wp_embed->run_shortcode( '[embed]'. $oembed_url .'[/embed]' );
		// fallback that WordPress creates when no oEmbed was found
		$fallback = $wp_embed->maybe_make_link( $oembed_url );

		if ( $check_embed && $check_embed != $fallback ) {
			// Embed data
			$return = '<div class="embed_status">'. $check_embed .'<a href="#" class="cmb_remove_file_button" rel="'. $_REQUEST['field_id'] .'">'. __( 'Remove Embed', 'cmb' ) .'</a></div>';
			// set our response id
			$found = 'found';

		} else {
			// error info when no oEmbeds were found
			$return = '<p class="ui-state-error-text">'.sprintf( __( 'No oEmbed Results Found for %s. View more info at', 'cmb' ), $fallback ) .' <a href="http://codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>.</p>';
			// set our response id
			$found = 'not found';
		}
	}

	// send back our encoded data
	echo json_encode( array( 'result' => $return, 'id' => $found ) );
	die();
}

// End. That's it, folks! //