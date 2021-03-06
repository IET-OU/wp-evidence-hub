<?php
/**
 * Construct a evidence custom post type
 *
 * @since 0.1.1
 *
 * @package Evidence_Hub
 * @subpackage Evidence_Hub_CustomPostType
 */
new Evidence_Template();
class Evidence_Template extends Evidence_Hub_CustomPostType {

	const POST_TYPE = 'evidence';
	//public $post_type	= "evidence";

	protected $archive_slug = "evidence"; // use pluralized string if you want an archive page
	protected $singular = "Evidence";
	protected $plural = "Evidence";

	protected $options = array();

	/**
	* Register custom post type.
	*
	* @since 0.1.1
	*/
	public function create_post_type() {

		$do_rewrite = ! $this->get_option( 'evidence_post_type_no_rewrite' );
		if ($do_rewrite) {
			$rewrite = array(
				'slug' => $this->archive_slug,
				'with_front' => false,
			);
		} else {
			$rewrite = FALSE;
		}
		$this->debug(array( __FUNCTION__, 'rewrite', $rewrite ));

		register_post_type($this->get_post_type() ,
			array(
				'labels' => array(
					'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", $this->get_post_type() )))),
					'singular_name' => __(ucwords(str_replace("_", " ", $this->get_post_type() )))
				),
				'labels' => array(
					'name' => __(sprintf('%s', $this->plural)),
					'singular_name' => __(sprintf('%s', $this->singular)),
					'add_new' => __(sprintf('Add New %s', $this->singular)),
					'add_new_item' => __(sprintf('Add New %s', $this->singular)),
					'edit_item' => __(sprintf('Edit %s', $this->singular)),
					'new_item' => __(sprintf('New %s', $this->singular)),
					'view_item' => __(sprintf('View %s', $this->singular)),
					'search_items' => __(sprintf('Search %s', $this->plural)),
					'not_found' => __(sprintf('No %s found', $this->plural)),
					'not_found_in_trash' => __(sprintf('No found in Trash%s', $this->plural)),
				),
				'public' => true,
				'description' => __("A piece of evidence to support a hypothesis"),
				'taxonomies' => array('post_tag'),
				'supports' => array(
					'title', 'editor', 'excerpt', 'author', 'comments'
				),
				'capabilities' => array(
					'edit_post'          => 'edit_evidence',
					'read_post'          => 'read_evidence',
					'delete_post'        => 'delete_evidence',
					'edit_others_posts'  => 'evidence_admin',
					'publish_posts'      => 'evidence_admin',
					'read_private_posts' => 'evidence_admin',
				),
				'map_meta_cap' => true,
				'has_archive' => true,
				'rewrite' => $rewrite, /*WAS: array(
					'slug' => $this->archive_slug,
					'with_front' => false,
				),*/
				'menu_position' => 30,
				'menu_icon' => EVIDENCE_HUB_URL.'images/icons/evidence.png',
			)
		);
	}

	/**
	* Register custom post type fields.
	*
	* @since 0.1.1
	*/
	function set_options(){
		$hypothesis_options = array();
		// get all the hypothesis ids
		$hypotheses = get_posts( array(	'post_type' => 'hypothesis', // my custom post type
										'posts_per_page' => -1,
										'post_status' => 'publish',
										'orderby' => 'title',
										'order' => 'ASC',
										'fields' => 'ids'));
		foreach($hypotheses as $hypothesis){
			$hypothesis_options[$hypothesis] = get_the_title($hypothesis);
		}

		// regester different field options
		$this->options = array_merge($this->options, array(
			'post_type' => array(
				'type' => 'select-posttype',
				'position' => 'side',
				'label' => "Post Type",
				),
		));
		$this->options = array_merge($this->options, array(
			'hypothesis_id' => array(
				'type' => 'select',
				'required' => true,
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => $this->is_proposition() ?
				  __('Proposition', self::LOC_DOMAIN) : __('Hypothesis', self::LOC_DOMAIN),
				'options' => $hypothesis_options,
				),
		));
		$this->options = array_merge($this->options, array(
			'polarity' => array(
				'type' => 'single-checkbox',
				'required' => true,
				'save_as' => 'term',
				'position' => 'side',
				'label' => "Polarity",
				'options' => get_terms('evidence_hub_polarity', 'hide_empty=0&orderby=id'),
				),
		));
		$this->options = array_merge($this->options, array(
			'date' => array(
				'type' => 'date',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => 'Date'
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'sector' => array(
				'type' => 'select',
				'required' => true,
				'save_as' => 'term',
				'position' => 'side',
				'quick_edit' => true,
        		'label' => __('Sector', self::LOC_DOMAIN),
				'options' => get_terms('evidence_hub_sector', 'hide_empty=0&orderby=id'),
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'country' => array(
				'type' => 'select',
				'save_as' => 'term',
				'position' => 'side',
				'label' => "Country",
				'options' => get_terms('evidence_hub_country', 'hide_empty=0'),
				),
		 ));
		 $this->options = array_merge($this->options, array(
			'project_id' => array(
				'type' => 'project',
				'save_as' => 'post_meta',
				'position' => 'side',
				'label' => 'Project/Org',
				'descr' => 'Optional field to associate evidence to a project',
				)
		 ));
		 $this->options = array_merge($this->options, array(
			'citation' => array(
				'type' => 'text',
				'lookup' => true,
				'save_as' => 'post_meta',
				'position' => 'bottom',
				'label' => 'Citation'
				)
		 ));

		// Add a URL field based on configuration [LACE] [Bug: #21].
		if ($this->get_option( 'wp_evidence_hub_url_field' )) {
			$this->options = array_merge($this->options, array(
				'url' => array(
					'type' => 'text',
					'lookup' => true,
					'save_as' => 'post_meta',
					'position' => 'bottom',
					'label' => 'URL'
				)
			));
		}

		 Evidence_Hub::$post_type_fields[$this->get_post_type() ] = $this->options;
	}

	/**
	* Register custom fields box in wp-admin.
	*
	* @since 0.1.1
	*/
	public function add_meta_boxes() {
		// Add this metabox to every selected post
		add_meta_box(
			sprintf('wp_evidence_hub_%s_section', $this->get_post_type() ),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->get_post_type() ))),
			array(&$this, 'add_inner_meta_boxes'),
			$this->get_post_type() ,
			'top',          // The part of the page where the edit screen section should be shown.
            'high'
		);
		add_meta_box(
			sprintf('wp_evidence_hub_%s_side_section', $this->get_post_type() ),
			sprintf('%s Information', ucwords(str_replace("_", " ", $this->get_post_type() ))),
			array(&$this, 'add_inner_meta_boxes_side'),
			$this->get_post_type() ,
			'side',
			'high'
		);

		// remove standard Tags boxes
		remove_meta_box('tagsdiv-evidence_hub_country', $this->get_post_type(), 'side');
		remove_meta_box('tagsdiv-evidence_hub_sector', $this->get_post_type(), 'side');
		remove_meta_box('tagsdiv-evidence_hub_polarity', $this->get_post_type(), 'side');
		Pronamic_Google_Maps_MetaBox::register($this->get_post_type(), 'normal', 'high');

	} // END public function add_meta_boxes()

	# Now move advanced meta boxes after the title:
	public function foo_move_deck() {

		# Get the globals:
		global $post, $wp_meta_boxes;

		# Output the "advanced" meta boxes:
		do_meta_boxes(get_current_screen(), 'top', $post);

		# Remove the initial "advanced" meta boxes:
		unset($wp_meta_boxes['post']['advanced']);

	}



	/**
	* Add hypothesis column to wp-admin.
	*
	* @since 0.1.1
	*/
	public function columns($columns) {
		return array_slice($columns, 0, 3, true) +
				array('evidence_hub_hypothesis_id' => __( 'Hypothesis' )) +
				array_slice($columns, 3, count($columns) - 1, true) ;
	}

	/**
	* Sets text and link for custom columns.
	*
	* @since 0.1.1
	*/
	public function column($column, $post_id) {
		global $post;
		switch (str_replace('evidence_hub_', '', $column)) {
		case 'hypothesis_id':
			$case_id = get_post_meta( $post_id, $column, true );
			$case_title = get_the_title($case_id);
			if ( empty( $case_title ) )
				echo __( 'Empty' );
			else
				printf( __( '<a href="?post_type=evidence&hyp_id=%s">%s</a>' ), $case_id, ucwords($case_title) );
			break;
		default :
			break;
		}
	}
} // END class Post_Type_Template
