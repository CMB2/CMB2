<?php
/**
 * CMB2 Associated Objects field type
 *
 * @todo Make field type for all object types, to and from.
 * @todo Maybe remove dependence on super-globals?
 * @todo Replace WP core ajax completely.
 * @todo Unit tests for field.
 * @todo Add example to example-functions.php. See https://github.com/CMB2/cmb2-attached-posts/blob/master/example-field-setup.php
 *
 * @since  2.X.X
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
	 * The field instance's query handler.
	 *
	 * @var CMB2_Type_Query_Associated_Objects
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
	}

	/**
	 * @param array $args
	 *
	 * @return CMB2_Type_Base|string
	 */
	public function render( $args = array() ) {
		$this->query = $this->get_query();

		// Check to see if we have any meta values saved yet
		$attached = $this->get_attached();
		$attached = empty( $attached ) ? array() : (array) $attached;

		// Fetch the objects.
		$objects  = $this->get_all_objects( $attached );

		// If there are no objects found, just stop
		if ( empty( $objects ) ) {
			return;
		}

		$this->field->add_js_dependencies( 'cmb2-associated-objects' );

		// Set .has_thumbnail
		$has_thumbnail       = $this->field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected       = $this->field->options( 'hide_selected' ) ? ' hide-selected' : '';
		$object_type_labels  = $this->query->get_all_object_type_labels();
		$combined_label      = implode( '/', $object_type_labels );
		$this->do_type_label = count( $object_type_labels ) > 1;
		$rendered            = '';
		$filter_boxes        = '';

		// Check 'filter' setting
		if ( $this->field->options( 'filter_boxes' ) ) {
			$filter_boxes = '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb2' ), $combined_label ) . '" class="regular-text search" name="%s" /></div>';
		}

		// Wrap our lists
		$rendered .= '<div class="associated-objects-wrap widefat" data-fieldname="' . $this->_name() . '">';

		// Open our retrieved, or found objects, list
		$rendered .= '<div class="retrieved-wrap column-wrap">';
		$rendered .= '<h4 class="associated-objects-section">' . sprintf( __( 'Available %s', 'cmb2' ), $combined_label ) . '</h4>';

		if ( $filter_boxes ) {
			$rendered .= sprintf( $filter_boxes, 'available-search' );
		}

		$rendered .= '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our objects as list items
		$rendered .= $this->get_rendered_retrieved( $objects, $attached );

		// Close our retrieved, or found, objects
		$rendered .= '</ul><!-- .retrieved -->';

		// @todo make other object types search work.
		$findtxt = $this->_text( 'find_text', __( 'Search' ) );
		$rendered .= '<p><button type="button" class="button cmb2-associated-objects-search-button" data-search=\'' . $this->button_data_for_js() . '\'>' . $findtxt . ' <span title="' . esc_attr( $findtxt ) . '" class="dashicons dashicons-search"></span></button></p>';

		$rendered .= '</div><!-- .retrieved-wrap -->';

		// Open our attached objects list
		$rendered .= '<div class="attached-wrap column-wrap">';
		$rendered .= '<h4 class="associated-objects-section">' . sprintf( __( 'Attached %s', 'cmb2' ), $combined_label ) . '</h4>';

		if ( $filter_boxes ) {
			$rendered .= sprintf( $filter_boxes, 'attached-search' );
		}

		$rendered .= '<ul class="attached connected' . $has_thumbnail . '">';

		// If we have any ids saved already, get them
		$rendered .= $this->get_rendered_attached( $attached );

		// Close up shop
		$rendered .= '</ul><!-- #attached -->';
		$rendered .= '</div><!-- .attached-wrap -->';

		$rendered .= $this->types->input( array(
			'type'  => 'hidden',
			'class' => 'associated-objects-ids',
			'value' => ! empty( $attached ) ? implode( ',', $attached ) : '',
			'desc'  => '',
		) );

		$rendered .= '</div><!-- .associated-objects-wrap -->';

		// Display our description if one exists
		$rendered .= $this->_desc( true );

		return $this->rendered( $rendered );
	}

	/**
	 * Get the JSON encoded data for our objects-search button.
	 * TODO: make work for non-posts.
	 *
	 * @since  2.X.X
	 *
	 * @return string
	 */
	public function button_data_for_js() {
		$args = array(
			'sourceType' => $this->query->get_source_type(),
			'types'      => (array) $this->query->get_query_arg( 'post_type' ),
			'cmbId'      => $this->field->cmb_id,
			'errortxt'   => esc_attr( $this->_text( 'error_text', __( 'An error has occurred. Please reload the page and try again.' ) ) ),
			'findtxt'    => esc_attr( $this->_text( 'search_modal_title', __( 'Search for content...', 'cmb2' ) ) ),
			'groupId'    => $this->field->group ? $this->field->group->id() : false,
			'fieldId'    => $this->field->_id( '', false ),
			'objectId'   => $this->field->object_id(),
			'objectType' => $this->field->object_type(),
			'exclude'    => $this->query->get_query_arg( 'post__not_in', array() ),
			'linkTmpl'   => str_replace( $this->field->object_id(), 'REPLACEME', get_edit_post_link( $this->field->object_id() ) ),
		);
		return json_encode( $args );
	}

	/**
	 * Outputs a column list item.
	 *
	 * @since  2.X.X
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
			$label = '<span class="object-label-separator"> &mdash; </span><span class="object-label">' . $this->query->get_object_type_label( $object ) . '</span>';
		}

		// Build our list item
		return sprintf(
			'<li data-id="%1$d" class="%2$s">%3$s<a title="' . __( 'Edit' ) . '" href="%4$s" target="_blank">%5$s</a>%6$s<span class="dashicons %7$s add-remove"></span></li>',
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
	 * @since  2.X.X
	 *
	 * @param  mixed  $objects  Posts or users.
	 * @param  array  $attached Array of attached objects.
	 *
	 * @return string
	 */
	protected function get_rendered_retrieved( $objects, $attached ) {
		$count = 0;
		$rendered = '';

		// Loop through our objects as list items
		foreach ( $objects as $object ) {

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached meta
			$class .= ! empty( $attached ) && in_array( $this->query->get_id( $object ), $attached ) ? ' added' : '';

			$rendered .= $this->get_list_item( $object, $class );
		}

		return $rendered;
	}

	/**
	 * Returns the <li>s in the attached (right) column.
	 *
	 * @since  2.X.X
	 *
	 * @param  array  $attached Array of attached objects.
	 *
	 * @return string
	 */
	protected function get_rendered_attached( $attached ) {
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

			$rendered .= $this->get_list_item( $object, $class, 'dashicons-minus' );
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
		$attached = array_filter( (array) $attached );
		$ids = array();

		if ( empty( $attached ) ) {
			return array();
		}

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $this->query->get_object( $id );

			if ( empty( $object ) ) {
				continue;
			}

			$id = $this->query->get_id( $object );
			$ids[ $id ] = $id;
		}

		return $ids;
	}

	/**
	 * Fetches the default query for items, and combines with any objects attached.
	 *
	 * @since  2.X.X
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
	 *
	 * @return CMB2_Type_Query_Associated_Objects
	 */
	public function get_query() {
		return CMB2_Type_Query_Associated_Objects::get_query_object(
			$this->field
		);
	}

	/**
	 * Outputs the modal window used for attaching associated content in the media-listing screen.
	 *
	 * @since 2.X.X
	 */
	public static function output_js_underscore_templates() {
		?>
		<div id="cmb2-find-posts" class="find-box cmb2-find-box" style="display: none;">
			<div id="cmb2-find-posts-head" class="find-box-head">
				<span id="cmb2-find-posts-head-title"><?php _e( 'Attach to existing content', 'cmb2' ); ?></span>
				<button type="button" id="cmb2-find-posts-close"><span class="screen-reader-text"><?php _e( 'Close search panel', 'cmb2' ); ?></span></button>
			</div>
			<div class="find-box-inside">
				<div class="find-box-search">
					<?php wp_nonce_field( 'cmb2-find-posts', 'cmb2-find-posts-nonce', false ); ?>
					<label class="screen-reader-text" for="cmb2-find-posts-input"><?php _e( 'Search', 'cmb2' ); ?></label>
					<input type="text" id="cmb2-find-posts-input" name="ps" value="" />
					<span class="spinner"></span>
					<input type="button" id="cmb2-find-posts-search" value="<?php esc_attr_e( 'Search' , 'cmb2' ); ?>" class="button" />
					<div class="clear"></div>
				</div>
				<div id="cmb2-find-posts-response"></div>
			</div>
			<div class="find-box-buttons">
				<?php submit_button( __( 'Select' ), 'primary alignright', 'cmb2-find-posts-submit', false ); ?>
				<div class="clear"></div>
			</div>
		</div>
		<script type="text/html" id="tmpl-cmb2-associated-search-table">
			<table class="widefat cmb-type-associated-{{ data.objectType }}">
				<thead>
					<tr>
						<th class="found-radio">
							<br />
						</th>
						<th>{{ data.columnOneLabel }}</th>
						<# if ( data.columnTwoLabel ) { #>
							<th class="no-break">{{ data.columnTwoLabel }}</th>
						<# } #>
						<# if ( data.columnThreeLabel ) { #>
							<th class="no-break">{{ data.columnThreeLabel }}</th>
						<# } #>
						<# if ( data.columnFourLabel ) { #>
							<th class="no-break">{{ data.columnFourLabel }}</th>
						<# } #>
					</tr>
				</thead>
				<tbody>{{{ data.results }}}</tbody>
			</table>
		</script>
		<script type="text/html" id="tmpl-cmb2-associated-search-table-row">
			<tr class="found-posts {{ data.alt }}">
				<td class="found-radio">
					<input type="checkbox" id="found-{{ data.postId }}" name="found_post_id" value="{{ data.postId }}">
				</td>
				<td>
					<label for="found-{{ data.postId }}">{{ data.columnOne }}</label>
				</td>
				<# if ( data.columnTwo ) { #>
					<td class="no-break">{{ data.columnTwo }}</td>
				<# } #>
				<# if ( data.columnThree ) { #>
					<td class="no-break">{{ data.columnThree }}</td>
				<# } #>
				<# if ( data.columnFour ) { #>
					<td class="no-break">{{ data.columnFour }}</td>
				<# } #>
			</tr>
		</script>
		<script type="text/html" id="tmpl-cmb2-associated-results-item">
			<li
				data-id="{{ data.id }}"
				class="{{ data.class }} ui-draggable ui-draggable-handle"
			>
				<a
					title="{{ data.editTitle }}"
					href="{{ data.link }}"
					target="_blank"
				>{{ data.title }}</a>{{ data.type }}<span class="dashicons dashicons-plus add-remove"></span>
			</li>
		</script>
		<?php
	}

	/**
	 * Magic getter for our object. Provides access to protected properties, but prevents overriding.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'do_type_label':
			case 'query':
				return $this->{$property};
			default:
				return parent::__get( $property );
		}
	}
}
