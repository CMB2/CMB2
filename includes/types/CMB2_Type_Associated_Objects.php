<?php
/**
 * CMB Associated Object field type
 *
 * @todo Make field type for all object types, to and from.
 * @todo Maybe remove dependence on super-globals?
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
	protected $object_type_labels = array();

	/**
	 * The type of field
	 *
	 * @var string
	 */
	public $type = 'associated_objects';

	/**
	 * [$hooked description]
	 *
	 * @var boolean
	 */
	protected static $hooked = false;

	/**
	 * [$query_object_type description]
	 *
	 * @var string
	 */
	protected $query_object_type = 'post';

	/**
	 * The field instance's query handler.
	 *
	 * @var CMB2_Type_Associated_Objects_Query
	 */
	protected $query;

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param CMB2_Types $types
	 * @param array      $args
	 */
	public function __construct( CMB2_Types $types, $args = array() ) {
		parent::__construct( $types, $args );

		if ( ! self::$hooked ) {
			add_action( 'cmb2_attached_posts_field_add_find_posts_div', array( __CLASS__, 'add_find_posts_div' ) );
			self::$hooked = true;
		}
	}

	/**
	 * @param array $args
	 *
	 * @return CMB2_Type_Base|string
	 */
	public function render( $args = array() ) {
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

		$this->field->add_js_dependencies( 'cmb2-associated-objects' );
		$this->query_object_type = $this->field->options( 'query_object_type' );
		$this->query_args = $this->field->options( 'query_args' );
		$this->query = $this->get_query( $this->query_object_type, $this->query_args );

		$filter_boxes = '';
		// Set .has_thumbnail
		$has_thumbnail = $this->field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected = $this->field->options( 'hide_selected' ) ? ' hide-selected' : '';

		// Check to see if we have any meta values saved yet
		$attached = $this->get_attached();
		$attached = empty( $attached ) ? array() : (array) $attached;

		$objects = $this->get_all_objects( $attached );
		$object_type_labels = $this->query->get_all_object_type_labels();
		$this->do_type_label = count( $object_type_labels ) > 1;

		// If there are no posts found, just stop
		if ( empty( $objects ) ) {
			return;
		}

		$rendered = '';

		// Wrap our lists
		$rendered.= '<div class="associated-objects-wrap widefat" data-fieldname="'. $this->_name() .'">';

		// Open our retrieved, or found posts, list
		$rendered.= '<div class="retrieved-wrap column-wrap">';
		$rendered.= '<h4 class="associated-objects-section">' . sprintf( __( 'Available %s', 'cmb' ), implode( '/', $object_type_labels ) ) . '</h4>';

		// Check 'filter' setting
		if ( $this->field->options( 'filter_boxes' ) ) {
			$filter_boxes = '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb' ), $this->object_type_labels ) . '" class="regular-text search" name="%s" /></div>';
		}

		if ( $filter_boxes ) {
			$rendered.= sprintf( $filter_boxes, 'available-search' );
		}

		$rendered.= '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our posts as list items
		$rendered.= $this->render_retrieved( $objects, $attached );

		// Close our retrieved, or found, posts
		$rendered.= '</ul><!-- .retrieved -->';

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
				'exclude'         => isset( $this->query_args['post__not_in'] )
					? $this->query_args['post__not_in']
					: array(),
				'linkTmpl'        => str_replace( $this->field->object_id(), 'REPLACEME', get_edit_post_link( $this->field->object_id() ) )
			) );

			$rendered.= '<p><button type="button" class="button cmb2-associated-objects-search-button" data-search=\''. $js_data .'\'>'. $findtxt .' <span title="'. esc_attr( $findtxt ) .'" class="dashicons dashicons-search"></span></button></p>';
		}

		$rendered.= '</div><!-- .retrieved-wrap -->';

		// Open our attached posts list
		$rendered.= '<div class="attached-wrap column-wrap">';
		$rendered.= '<h4 class="associated-objects-section">' . sprintf( __( 'Attached %s', 'cmb' ), implode( '/', $object_type_labels ) ) . '</h4>';

		if ( $filter_boxes ) {
			$rendered.= sprintf( $filter_boxes, 'attached-search' );
		}

		$rendered.= '<ul class="attached connected' . $has_thumbnail . '">';

		// If we have any ids saved already, display them
		$rendered.= $this->render_attached( $attached );

		// Close up shop
		$rendered.= '</ul><!-- #attached -->';
		$rendered.= '</div><!-- .attached-wrap -->';

		$rendered.= $this->types->input( array(
			'type'  => 'hidden',
			'class' => 'associated-objects-ids',
			'value' => ! empty( $attached ) ? implode( ',', $attached ) : '',
			'desc'  => '',
		) );

		$rendered.= '</div><!-- .associated-objects-wrap -->';

		// Display our description if one exists
		$rendered.= $this->_desc( true );

		return $this->rendered( $rendered );
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
	 * @return string
	 */
	public function get_list_item( $object, $li_class, $icon_class = 'dashicons-plus' ) {
		$label = '';

		if ( $this->do_type_label ) {
			$label = ' &mdash; <span class="object-label">'. $this->query->get_object_type_label( $object ) . '</span>';
		}

		// Build our list item
		return sprintf(
			'<li data-id="%1$d" class="%2$s" target="_blank">%3$s<a title="' . __( 'Edit' ) . '" href="%4$s">%5$s</a>%6$s<span class="dashicons %7$s add-remove"></span></li>',
			$this->query->get_id( $object ),
			$li_class,
			$this->query->get_thumb( $object ),
			$this->query->get_edit_link( $object ),
			$this->query->get_title( $object ),
			$label,
			$icon_class
		);
	}

	/**
	 * Returns the <li>s in the retrieved (left) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  mixed  $objects  Posts or users.
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return string
	 */
	protected function render_retrieved( $objects, $attached ) {
		$count = 0;
		$rendered = '';

		// Loop through our posts as list items
		foreach ( $objects as $object ) {

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached meta
			$class .= ! empty ( $attached ) && in_array( $this->query->get_id( $object ), $attached ) ? ' added' : '';

			$rendered.= $this->get_list_item( $object, $class );
		}

		return $rendered;
	}

	/**
	 * Returns the <li>s in the attached (right) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return string
	 */
	protected function render_attached( $attached ) {
		$rendered = '';

		// Remove any empty values
		$attached = array_filter( $attached );
		$this->query->set_include( $attached );

		if ( empty( $attached ) ) {
			return $rendered;
		}

		$count = 0;

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $this->query->get_object( $id );

			if ( empty( $object ) ) {
				continue;
			}

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			$rendered.= $this->get_list_item( $object, $class, 'dashicons-minus' );
		}

		return $rendered;
	}

	/**
	 * Returns a filtered array of object IDs.
	 *
	 * @return array
	 */
	function get_attached() {
		$attached = $this->field->escaped_value();
		$attached = array_filter( $attached );
		$ids = array();

		if ( empty( $attached ) ) {
			return array();
		}

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $this->query->get_object( $id );
			$id     = $this->query->get_id( $object );

			if ( empty( $object ) ) {
				continue;
			}
			$ids[ $id ] = $id;
		}

		return $ids;
	}

	/**
	 * Fetches the default query for items, and combines with any objects attached.
	 *
	 * @since  1.2.4
	 *
	 * @param  array  $attached Array of attached object ids.
	 *
	 * @return array            Array of attached object ids.
	 */
	public function get_all_objects( $attached = array() ) {
		$objects = $this->query->execute_query();

		if ( ! empty( $attached ) ) {
			$this->query->set_include( $attached );
			$this->query->set_number( count( $attached ) );

			$new = $this->query->execute_query();

			foreach ( $new as $object ) {
				if ( ! isset( $objects[ $this->query->get_id( $object ) ] ) ) {
					$objects[ $this->query->get_id( $object ) ] = $object;
				}
			}
		}

		return $objects;
	}

	/**
	 * @param string $query_object_type
	 * @param array $args
	 *
	 * @return CMB2_Type_Associated_Objects_Query
	 */
	public function get_query( $query_object_type = 'post', $args ) {
		switch ( $query_object_type ) {
			case 'user';
				$query = new CMB2_Type_Associated_Users_Query( $args );
				break;
			case 'term':
				$query = new CMB2_Type_Associated_Terms_Query( $args );
				break;
			case 'post':
			default:
				$query = new CMB2_Type_Associated_Posts_Query( $args );
		}

		return $query;
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

/**
 * Class CMB2_Type_Associated_Objects_Query
 */
abstract class CMB2_Type_Associated_Objects_Query {
	/**
	 * Map of query type to appropriate callback.
	 *
	 * @var array
	 */
	protected $query_type_callbacks = array(
		'post' => 'get_posts',
		'user' => 'get_users',
		'term' => 'get_terms',
	);

	/**
	 * @var array
	 */
	protected $original_args = array();

	/**
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * @var array
	 */
	public $objects = array();

	/**
	 * CMB2_Type_Associated_Objects_Query constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->original_args = $args;
		$this->set_query_args( $args );
	}

	/**
	 * @param $args
	 */
	public function set_query_args( $args ) {
		$this->query_args = wp_parse_args( $args, $this->default_query_args() );
	}

	/**
	 * Reset the query args to their original values.
	 */
	public function reset_query_args() {
		$this->set_query_args( $this->original_args );
	}

	/**
	 * Execute the appropriate query callback for the current query type.
	 *
	 * @return array
	 */
	public function execute_query() {
		if ( !isset( $this->query_type_callbacks[ $this->query_type() ] ) || !is_callable( $this->query_type_callbacks[ $this->query_type() ] ) ) {
			return array();
		}

		$this->objects = array();
		$objects = call_user_func( $this->query_type_callbacks[ $this->query_type() ], $this->query_args );

		foreach ($objects as $object) {
			$this->objects[ $this->get_id( $object ) ] = $object;
		}

		return $this->objects;
	}

	/**
	 * @return string
	 */
	abstract function query_type();

	/**
	 * @return array
	 */
	abstract function default_query_args();

	/**
	 * @param object $object
	 *
	 * @return int
	 */
	abstract function get_id( $object );

	/**
	 *
	 * @param object $object
	 *
	 * @return string
	 */
	abstract function get_title( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract function get_thumb( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract function get_edit_link( $object );

	/**
	 * @param $id
	 *
	 * @return object
	 */
	abstract function get_object( $id );

	/**
	 * @param object $object
	 *
	 * @return mixed
	 */
	abstract function get_object_type_label( $object );

	/**
	 * @return array
	 */
	abstract function get_all_object_type_labels();

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_include( $ids ) {
		$this->query_args['include'] = $ids;
	}

	/**
	 * @param int $count
	 *
	 * @return void
	 */
	public function set_number( $count ) {
		$this->query_args['number'] = $count;
	}

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_exclude( $ids ) {
		$this->query_args['exclude'] = $ids;
	}

}

/**
 * Class CMB2_Type_Associated_Posts_Query
 */
class CMB2_Type_Associated_Posts_Query extends CMB2_Type_Associated_Objects_Query {

	/**
	 * @return string
	 */
	public function query_type() {
		return 'post';
	}

	/**
	 * @return array
	 */
	public function default_query_args() {
		return array(
			'post_type'      => 'post',
			'posts_per_page' => 100,
			'orderby' => 'name',
			'order' => 'ASC',
			'ignore_sticky_posts' => TRUE,
			'post__not_in' => array(),
		);
	}

	/**
	 * @param WP_Post $object
	 *
	 * @return string
	 */
	public function get_title( $object ) {
		return get_the_title( $object->ID );
	}

	/**
	 * @param WP_Post $object
	 *
	 * @return string
	 */
	public function get_thumb( $object ) {
		return get_the_post_thumbnail( $object->ID, array( 50, 50 ) );
	}

	/**
	 * @param WP_Post $object
	 *
	 * @return string
	 */
	public function get_edit_link( $object ) {
		return get_edit_post_link( $object );
	}

	/**
	 * @param $id
	 *
	 * @return null|WP_Post
	 */
	public function get_object( $id ) {
		return get_post( $id );
	}

	/**
	 * @param WP_Post $object
	 *
	 * @return int
	 */
	public function get_id( $object ) {
		return $object->ID;
	}

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_include( $ids ) {
		$this->query_args['post__in'] = (array) $ids;
	}

	/**
	 * @param $count
	 *
	 * @return void
	 */
	public function set_number( $count ) {
		$this->query_args['posts_per_page'] = $count;
	}

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_exclude( $ids ) {
		$this->query_args['post__not_in'] = (array) $ids;
	}

	/**
	 * @param object $object
	 *
	 * @return mixed
	 */
	public function get_object_type_label( $object ) {
		$post_type_obj = get_post_type_object( $object->post_type );
		$label = isset( $post_type_obj->labels->singular_name ) ? $post_type_obj->labels->singular_name : $post_type_obj->label;
		return $label;
	}

	/**
	 * @return array
	 */
	public function get_all_object_type_labels() {
		$labels = array();

		foreach ( (array) $this->query_args['post_type'] as $post_type ) {
			// Get post type object for attached post type
			$post_type_obj = get_post_type_object( $post_type );

			// continue if we don't have a label for the post type
			if ( ! $post_type_obj || ! isset( $post_type_obj->labels->name ) ) {
				continue;
			}

			$labels[] = $post_type_obj->labels->name;
		}

		return $labels;
	}
}

/**
 * Class CMB2_Type_Associated_Users_Query
 */
class CMB2_Type_Associated_Users_Query extends CMB2_Type_Associated_Objects_Query {

	/**
	 * @return string
	 */
	public function query_type() {
		return 'user';
	}

	/**
	 * @return array
	 */
	public function default_query_args() {
		return array(
			'number' => 100,
			'exclude' => array(),
		);
	}

	/**
	 *
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_title( $object ) {
		return $object->data->display_name;
	}

	/**
	 * @param WP_User $object
	 *
	 * @return false|string
	 */
	public function get_thumb( $object ) {
		return get_avatar( $object->ID, 25 );
	}

	/**
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_edit_link( $object ) {
		return get_edit_user_link( $object->ID );
	}

	/**
	 * @param int $id
	 *
	 * @return false|WP_User
	 */
	public function get_object( $id ) {
		return get_user_by( 'id', absint( $id ) );
	}

	/**
	 * @param WP_User $object
	 *
	 * @return mixed
	 */
	public function get_id( $object ) {
		return $object->ID;
	}

	/**
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_object_type_label( $object ) {
		return __( 'Users' );
	}

	/**
	 * @return array
	 */
	public function get_all_object_type_labels() {
		return array(
			__( 'Users' ),
		);
	}
}

/**
 * Class CMB2_Type_Associated_Terms_Query
 */
class CMB2_Type_Associated_Terms_Query extends CMB2_Type_Associated_Objects_Query {

	/**
	 * @var mixed string|array
	 */
	protected $taxonomies = 'post_tag';

	/**
	 * Constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		if ( !empty( $args['taxonomy'] ) ) {
			$this->taxonomies = $args['taxonomy'];
		}
	}

	/**
	 * @return string
	 */
	public function query_type() {
		return 'term';
	}

	/**
	 * @return array
	 */
	public function default_query_args() {
		return array(
			'taxonomy' => $this->taxonomies,
			'hide_empty' => false,
		);
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return int
	 */
	public function get_id( $object ) {
		return $object->term_id;
	}

	/**
	 *
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_title( $object ) {
		return $object->name;
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_thumb( $object ) {
		return '';
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_edit_link( $object ) {
		return get_edit_term_link( $object->term_id, $object->taxonomy );
	}

	/**
	 * @param $id
	 *
	 * @return false|WP_Term
	 */
	public function get_object( $id ) {
		$found = false;

		foreach ( (array) $this->taxonomies as $taxonomy ) {
			$term = get_term( $id, $taxonomy );

			if ( !empty($term) && !$term instanceof WP_Error ) {
				$found = $term;
				break;
			}
		}

		return $found;
	}

	/**
	 * @param WP_Term $object
	 *
	 * @return string
	 */
	public function get_object_type_label( $object ) {
		$taxonomy = get_taxonomy( $object->taxonomy );
		return $taxonomy->labels->name;
	}

	/**
	 * @return array
	 */
	public function get_all_object_type_labels() {
		$labels = array();

		foreach ( (array) $this->taxonomies as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy );

			if ( !empty( $tax ) ) {
				$labels[] = $tax->labels->name;
			}
		}

		return $labels;
	}
}