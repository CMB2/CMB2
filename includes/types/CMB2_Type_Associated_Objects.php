<?php
/**
 * CMB Associated Object field type
 *
 * @todo Make field type for all object types, to and from.
 * @todo Maybe remove dependence on super-globals?
 * @todo Remove output buffer and instead concatenate string.
 * @todo Replace WP core `find_posts_div()` markup and ajax completely.
 * @todo Unit tests for field.
 * @todo Add example to example-functions.php. See https://github.com/CMB2/cmb2-attached-posts/blob/master/example-field-setup.php
 *
 * @since  2.2.7
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Associated_Objects extends CMB2_Type_Text {

	/**
	 * Whether to output the type label.
	 * Determined when multiple post types exist in the query_args field arg.
	 *
	 * @var bool
	 */
	protected $do_type_label = false;

	/**
	 * [$query_args description]
	 *
	 * @var [type]
	 */
	protected $query_args = array();

	/**
	 * [$this->post_type_labels description]
	 *
	 * @var array
	 */
	protected $this->post_type_labels = array();

	/**
	 * [$hooked description]
	 *
	 * @var boolean
	 */
	protected static $hooked = false;

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param CMB2_Types $types
	 * @param array      $args
	 */
	public function __construct( CMB2_Types $types, $args = array(), $type = '' ) {
		parent::__construct( $types, $args );
		$this->type = $type ? $type : $this->type;

		if ( ! self::$hooked ) {
			add_action( 'cmb2_attached_posts_field_add_find_posts_div', array( __CLASS__, 'add_find_posts_div' ) );
			self::$hooked = true;
		}
	}

	/**
	 * [$query_object_type description]
	 *
	 * @var string
	 */
	protected $query_object_type = 'post'

	public function render( $args ) {
		if ( ! is_admin() ) {
			// Will need custom styling!
			// @todo add styles for front-end
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
			do_action( 'cmb2_attached_posts_field_add_find_posts_div' );
		} else {
			// markup needed for modal
			// @todo Replace with our own markup.
			add_action( 'admin_footer', 'find_posts_div' );
		}

		$this->query_args = (array) $this->field->options( 'query_args' );
		$this->setup_query_args();

		$filter_boxes = '';
		// Check 'filter' setting
		if ( $this->field->options( 'filter_boxes' ) ) {
			$filter_boxes = '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb' ), $this->post_type_labels ) . '" class="regular-text search" name="%s" /></div>';
		}

		// Check to see if we have any meta values saved yet
		$attached = (array) $field->escaped_value();

		$objects = $this->get_all_objects( $args, $attached );

		// If there are no posts found, just stop
		if ( empty( $objects ) ) {
			return;
		}

		ob_start();

		// Wrap our lists
		echo '<div class="associated-objects-wrap widefat" data-fieldname="'. $this->_name() .'">';

		// Open our retrieved, or found posts, list
		echo '<div class="retrieved-wrap column-wrap">';
		echo '<h4 class="associated-objects-section">' . sprintf( __( 'Available %s', 'cmb' ), $this->post_type_labels ) . '</h4>';

		// Set .has_thumbnail
		$has_thumbnail = $this->field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected = $this->field->options( 'hide_selected' ) ? ' hide-selected' : '';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'available-search' );
		}

		echo '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our posts as list items
		$this->get_retrieved( $objects, $attached );

		// Close our retrieved, or found, posts
		echo '</ul><!-- .retrieved -->';

		// @todo make User search work.
		if ( 'post' === $this->query_object_type ) {
			$findtxt = $this->_text( 'find_text', __( 'Search' ) );

			$js_data = json_encode( array(
				'queryObjectType' => $this->query_object_type,
				'types'           => (array) $this->query_object_type,
				'cmbId'           => $this->field->cmb_id,
				'errortxt'        => esc_attr( $this->_text( 'error_text', __( 'An error has occurred. Please reload the page and try again.' ) ) ),
				'findtxt'         => esc_attr( $this->_text( 'find_text', __( 'Find Posts or Pages' ) ) ),
				'groupId'         => $this->field->group ? $this->field->group->id() : false,
				'fieldId'         => $this->field->_id(),
				'exclude'         => isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(),
				'linkTmpl'        => str_replace( $this->field->object_id(), 'REPLACEME', get_edit_post_link( $this->field->object_id() ) )
			) );

			echo '<p><button type="button" class="button cmb2-associated-objects-search-button" data-search=\''. $js_data .'\'>'. $findtxt .' <span title="'. esc_attr( $findtxt ) .'" class="dashicons dashicons-search"></span></button></p>';
		}

		echo '</div><!-- .retrieved-wrap -->';

		// Open our attached posts list
		echo '<div class="attached-wrap column-wrap">';
		echo '<h4 class="associated-objects-section">' . sprintf( __( 'Attached %s', 'cmb' ), $this->post_type_labels ) . '</h4>';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'attached-search' );
		}

		echo '<ul class="attached connected', $has_thumbnail ,'">';

		// If we have any ids saved already, display them
		$ids = $this->get_attached( $attached );

		// Close up shop
		echo '</ul><!-- #attached -->';
		echo '</div><!-- .attached-wrap -->';

		echo $this->types->input( array(
			'type'  => 'hidden',
			'class' => 'associated-objects-ids',
			'value' => ! empty( $ids ) ? implode( ',', $ids ) : '',
			'desc'  => '',
		) );

		echo '</div><!-- .associated-objects-wrap -->';

		// Display our description if one exists
		$field_type->_desc( true, true );

		// Get the contents of the output buffer.
		$rendered = ob_get_clean();

		return $this->rendered( $rendered );
	}

	protected function setup_query_args() {
		$this->query_object_type = $this->field->options( 'query_object_type' );

		if ( 'post' === $this->query_object_type ) {

			// Setup our args
			$this->query_object_type = wp_parse_args( $this->query_args, array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			) );

			// TODO: Remove reliance on superglobal?
			if ( isset( $_POST['post'] ) ) {
				$this->query_object_type['post__not_in'] = array( absint( $_POST['post'] ) );
			}

			// loop through post types to get labels for all
			$this->post_type_labels = array();
			foreach ( (array) $this->query_object_type['post_type'] as $post_type ) {
				// Get post type object for attached post type
				$post_type_obj = get_post_type_object( $post_type );

				// continue if we don't have a label for the post type
				if ( ! $post_type_obj || ! isset( $post_type_obj->labels->name ) ) {
					continue;
				}

				if ( is_post_type_hierarchical( $post_type_obj ) ) {
					$this->query_object_type['orderby'] = isset( $this->query_object_type['orderby'] )
						? $this->query_object_type['orderby']
						: 'name';
					$this->query_object_type['order']   = isset( $this->query_object_type['order'] )
						? $this->query_object_type['order']
						: 'ASC';
				}

				$this->post_type_labels[] = $post_type_obj->labels->name;
			}

			$this->do_type_label    = count( $this->post_type_labels ) > 1;
			$this->post_type_labels = implode( '/', $this->post_type_labels );

		} else {
			// Setup our args
			$this->query_object_type = wp_parse_args( $this->query_args, array(
				'number' => 100,
			) );
			$this->post_type_labels = $this->_text( 'users_text', esc_html__( 'Users' ) );
		}

	}

	/**
	 * Outputs the <li>s in the retrieved (left) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  mixed  $objects  Posts or users.
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return void
	 */
	protected function get_retrieved( $objects, $attached ) {
		$count = 0;

		// Loop through our posts as list items
		foreach ( $objects as $object ) {

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached meta
			$class .= ! empty ( $attached ) && in_array( $this->get_id( $object ), $attached ) ? ' added' : '';

			echo $this->get_list_item( $object, $class );
		}
	}

	/**
	 * Outputs the <li>s in the attached (right) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return void
	 */
	protected function get_attached( $attached ) {
		$ids = array();

		// Remove any empty values
		$attached = array_filter( $attached );

		if ( empty( $attached ) ) {
			return $ids;
		}

		$count = 0;

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $this->get_object( $id );
			$id     = $this->get_id( $object );

			if ( empty( $object ) ) {
				continue;
			}

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			echo $this->get_list_item( $object, $class, 'dashicons-minus' );
			$ids[ $id ] = $id;
		}

		return $ids;
	}

	/**
	 * Outputs a column list item.
	 *
	 * @since  1.2.5
	 *
	 * @param  mixed  $object     Post or User.
	 * @param  string  $li_class   The list item (zebra) class.
	 * @param  string  $icon_class The icon class. Either 'dashicons-plus' or 'dashicons-minus'.
	 *
	 * @return void
	 */
	public function get_list_item( $object, $li_class, $icon_class = 'dashicons-plus' ) {
		// Build our list item
		return sprintf(
			'<li data-id="%1$d" class="%2$s" target="_blank">%3$s<a title="' . __( 'Edit' ) . '" href="%4$s">%5$s</a>%6$s<span class="dashicons %7$s add-remove"></span></li>',
			$this->get_id( $object ),
			$li_class,
			$this->get_thumb( $object ),
			$this->get_edit_link( $object ),
			$this->get_title( $object ),
			$this->get_object_label( $object ),
			$icon_class
		);
	}

	/**
	 * Get thumbnail for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The thumbnail, if endabled/found.
	 */
	public function get_thumb( $object ) {
		$thumbnail = '';

		if ( $this->field->options( 'show_thumbnails' ) ) {
			// Set thumbnail if the options is true
			$thumbnail = 'user' === $this->field->options( 'query_object_type' )
				? get_avatar( $object->ID, 25 )
				: get_the_post_thumbnail( $object->ID, array( 50, 50 ) );
		}

		return $thumbnail;
	}

	/**
	 * Get ID for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return int            The object ID.
	 */
	public function get_id( $object ) {
		return $object->ID;
	}

	/**
	 * Get title for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object title.
	 */
	public function get_title( $object ) {
		return 'user' === $this->field->options( 'query_object_type' )
			? $object->data->display_name
			: get_the_title( $object );
	}

	/**
	 * Get object label.
	 *
	 * @since  1.2.6
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object label.
	 */
	public function get_object_label( $object ) {
		if ( ! $this->do_type_label ) {
			return '';
		}

		$post_type_obj = get_post_type_object( $object->post_type );
		$label = isset( $post_type_obj->labels->singular_name ) ? $post_type_obj->labels->singular_name : $post_type_obj->label;

		return ' &mdash; <span class="object-label">'. $label .'</span>';
	}

	/**
	 * Get edit link for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object edit link.
	 */
	public function get_edit_link( $object ) {
		return 'user' === $this->field->options( 'query_object_type' )
			? get_edit_user_link( $object->ID )
			: get_edit_post_link( $object );
	}

	/**
	 * Get object by id.
	 *
	 * @since  1.2.4
	 *
	 * @param  int   $id Post or User ID.
	 *
	 * @return mixed     Post or User if found.
	 */
	public function get_object( $id ) {
		return 'user' === $this->field->options( 'query_object_type' )
			? get_user_by( 'id', absint( $id ) )
			: get_post( absint( $id ) );
	}

	/**
	 * Fetches the default query for items, and combines with any objects attached.
	 *
	 * @since  1.2.4
	 *
	 * @param  array  $args     Array of query args.
	 * @param  array  $attached Array of attached object ids.
	 *
	 * @return array            Array of attached object ids.
	 */
	public function get_all_objects( $args, $attached = array() ) {
		$objects = $this->get_objects( $args );

		$attached_objects = array();
		foreach ( $objects as $object ) {
			$attached_objects[ $this->get_id( $object ) ] = $object;
		}

		if ( ! empty( $attached ) ) {
			$is_users = 'user' === $this->field->options( 'query_object_type' );
			$args[ $is_users ? 'include' : 'post__in' ] = $attached;
			$args[ $is_users ? 'number' : 'posts_per_page' ] = count( $attached );

			$new = $this->get_objects( $args );

			foreach ( $new as $object ) {
				if ( ! isset( $attached_objects[ $this->get_id( $object ) ] ) ) {
					$attached_objects[ $this->get_id( $object ) ] = $object;
				}
			}
		}

		return $attached_objects;
	}

	/**
	 * Peforms a get_posts or get_users query.
	 *
	 * @since  1.2.4
	 *
	 * @param  array  $args Array of query args.
	 *
	 * @return array        Array of results.
	 */
	public function get_objects( $args ) {
		return call_user_func( 'user' === $this->field->options( 'query_object_type' ) ? 'get_users' : 'get_posts', $args );
	}

	/**
	 * Add the find posts div via a hook so we can relocate it manually
	 */
	public static function add_find_posts_div() {
		add_action( 'wp_footer', 'find_posts_div' );
	}

	/**
	 * Sanitizes/formats the associated-objects field value.
	 *
	 * @since  1.2.4
	 *
	 * @param  string  $sanitized_val The sanitized value to be saved.
	 * @param  string  $val           The unsanitized value.
	 *
	 * @return string                 The (maybe-modified) sanitized value to be saved.
	 */
	public function sanitize( $sanitized_val, $val ) {
		if ( ! empty( $val ) ) {
			$sanitized_val = explode( ',', $val );
		}

		return $sanitized_val;
	}

	/**
	 * Check to see if we have a post type set and, if so, add the
	 * pre_get_posts action to set the queried post type
	 *
	 * @since  1.2.4
	 *

	 * @return void
	 */
	public static function ajax_find_associated() {
		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_POST['cmb2_attached_search'], $_POST['retrieved'], $_POST['action'], $_POST['search_types'] )
			&& 'find_posts' == $_POST['action']
			&& ! empty( $_POST['search_types'] )
		) {
			// This is not working until we fix the user query bit.
			if ( ! empty( $_POST['query_object_type'] ) && is_array( $_POST['query_object_type'] ) && in_array( 'user', $_POST['query_object_type'], true ) ) {
				add_action( 'pre_get_users', array( __CLASS__, 'modify_query' ) );
			} else {
				add_action( 'pre_get_posts', array( __CLASS__, 'modify_query' ) );
			}
		}
	}

	/**
	 * Modify the search query.
	 *
	 * @since  1.2.4
	 *
	 * @param  WP_Query  $query WP_Query instance during the pre_get_posts hook.
	 *
	 * @return void
	 */
	public static function modify_query( $query ) {
		$is_users = 'pre_get_users' === current_filter();

		if ( $is_users ) {
			// This is not working until we fix the user query bit.
		} else {
			$types = $_POST['search_types'];
			$types = is_array( $types ) ? array_map( 'esc_attr', $types ) : esc_attr( $types );
			$query->set( 'post_type', $types );
		}

		if ( ! empty( $_POST['retrieved'] ) && is_array( $_POST['retrieved'] ) ) {
			// Exclude posts/users already existing.
			$ids = array_map( 'absint', $_POST['retrieved'] );

			if ( ! empty( $_POST['exclude'] ) && is_array( $_POST['exclude'] ) ) {
				// Exclude the post that we're looking at.
				$exclude = array_map( 'absint', $_POST['exclude'] );
				$ids = array_merge( $ids, $exclude );
			}

			$query->set( $is_users ? 'exclude' : 'post__not_in', $ids );
		}

		self::maybe_callback( $query, $_POST );
	}

	/**
	 * If field has a 'attached_posts_search_query_cb', run the callback.
	 *
	 * @since  1.2.4
	 *
	 * @param  WP_Query $query     WP_Query instance during the pre_get_posts hook.
	 * @param  array    $post_args The $_POST array.
	 *
	 * @return void
	 */
	public static function maybe_callback( $query, $post_args ) {
		$cmb   = isset( $post_args['cmb_id'] ) ? $post_args['cmb_id'] : '';
		$group = isset( $post_args['group_id'] ) ? $post_args['group_id'] : '';
		$field = isset( $post_args['field_id'] ) ? $post_args['field_id'] : '';

		$cmb = cmb2_get_metabox( $cmb );
		if ( $cmb && $group ) {
			$group = $cmb->get_field( $group );
		}

		if ( $cmb && $field ) {
			$group = $group ? $group : null;
			$field = $cmb->get_field( $field, $group );
		}

		if ( $field && ( $cb = $field->maybe_callback( 'attached_posts_search_query_cb' ) ) ) {
			call_user_func( $cb, $query, $field );
		}
	}

}
