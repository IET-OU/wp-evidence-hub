<?php
/**
 * Construct a detailed map using LeafletJS
 * 
 * Shortcode: [geomap]
 * Options: type - string comma list of types to map
 *          do_cache - boolean to disable cache option default: true
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 * @since 0.1.1
 *
 * @package Evidence_Hub
 * @subpackage Evidence_Hub_Shortcode
 */
new Evidence_Hub_Shortcode_GeoMap();
// Base class 'Evidence_Hub_Shortcode' defined in 'shortcodes/class-shortcode.php'.
class Evidence_Hub_Shortcode_GeoMap extends Evidence_Hub_Shortcode {

	const SHORTCODE = 'geomap';

	protected $defaults = array(
		'title' => false,
		'no_evidence_message' => "There is no map yet to display",
		'title_tag' => 'h3',
		'type' => 'evidence',
		'table' => true,
		'display_key' => true,
	);

	protected static $post_types_with_shortcode = array();


    protected function enqueue_leaflet_map_scripts() {
        $path = EVIDENCE_HUB_REGISTER_FILE;
        $scripts = array(
            'http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js',
            plugins_url( 'js/markercluster/leaflet.markercluster-src.js', $path ),
            plugins_url( 'js/leaflet-map.js' , $path ),
            plugins_url( 'js/map-visualization.js', $path ),
        );

        foreach ($scripts as $idx => $js) {
            wp_enqueue_script('leaflet-js-'. $idx, $js, array('jquery'), null, $in_footer = true);
        }
        ?>
	<script>
        google.load('visualization', '1.1', { packages: [ 'controls' ] });
    </script>
<?php
    }

	/**
	* Generate post content.
	*
	* @since 0.1.1
	* @return string.
	*/
	protected function content() {
		ob_start();
		extract($this->options);

		$this->enqueue_leaflet_map_scripts();

		$errors = array();	
		$sub_options = array();
		$hypothesis_options = array();
		$types = explode(',', $this->options['type']);
		
		$types_array = array();
		foreach ($types as $i => $value) {
			$types_array[strtolower($value)] = ucwords($value);
		}

		
		// build dropdown filters for map 
		// type
		$sub_options = array_merge($sub_options, array(
			'type' => array(
				'type' => 'select',
				'save_as' => 'post_meta',
				'label' => "Type",
				'options' => $types_array,
				),
		));
		
		if (in_array('evidence', $types)){
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
			// hypothesis
			$sub_options = array_merge($sub_options, array(
				'hypothesis_id' => array(
					'type' => 'select',
					'save_as' => 'post_meta',
					'label' => $this->is_proposition() ? 'Proposition' : 'Hypothesis',
					'options' => $hypothesis_options,
					),
			));
			// polarity
			$sub_options = array_merge($sub_options, array(
				'polarity' => array(
					'type' => 'select',
					'save_as' => 'term',
					'label' => "Polarity",
					'options' => get_terms('evidence_hub_polarity', 'hide_empty=0&orderby=id'),
					),
			));
		}
		
		if (in_array('policy', $types)){
			$sub_options = array_merge($sub_options, array(
				'locale' => array(
					'type' => 'select',
					'save_as' => 'term',
					'label' => 'Locale',
					'options' => get_terms('evidence_hub_locale', 'hide_empty=0&orderby=id'),
					)
			 ));
		}
	
		$sub_options = array_merge($sub_options, array(
			'sector' => array(
				'type' => 'select',
				'save_as' => 'term',
				'label' => 'Sector',
				'options' => get_terms('evidence_hub_sector', 'hide_empty=0&orderby=id'),
				)
		 ));
		//html dump>>
		?>

		<?php $this->print_chart_loading_no_support_message( $is_map = TRUE, $partial = TRUE ) ?>
         <div id="evidence-map">
            <div id="map"><?php //$this->print_chart_loading_no_support_message( $is_map = TRUE ) ?></div>
            <?php $post = NULL; include(sprintf("%s/post-types/custom_post_metaboxes.php", EVIDENCE_HUB_PATH));?>
         </div>
         <script>
		 /* <![CDATA[ */
		var OERRH = OERRH || {};

		var json = OERRH.map_json = <?php $this->print_json_file($this->get_api_url( 'hub.get_geojson' ) .'count=-1&type='. strtolower($type)) ?>;	
		var hubPoints = json['geoJSON'] || null;
		var pluginurl = '<?php echo EVIDENCE_HUB_URL; ?>';

		jQuery(function ($) {
			var $map_width = $('#evidence-map').width()
			  , $map = $('#map')
			  , height = ($map_width > 820) ? parseInt($map_width * 9 / 16) : 560;
			$map.css('height', height);
		});
		<?php $this->print_leaflet_geomap_options_javascript() ?>
		/* ]]> */
		</script>
        <link rel="stylesheet" href="<?php echo plugins_url( 'js/markercluster/MarkerCluster.css' , EVIDENCE_HUB_REGISTER_FILE )?>" />
        <link rel="stylesheet" href="<?php echo plugins_url( 'js/markercluster/MarkerCluster.Default.css' , EVIDENCE_HUB_REGISTER_FILE )?>" />
        <!--<script src="<?php echo plugins_url( 'js/markercluster/leaflet.markercluster-src.js' , EVIDENCE_HUB_REGISTER_FILE )?>" charset="utf-8"></script>
		<script src="<?php echo plugins_url( 'js/leaflet-map.js?v=6' , EVIDENCE_HUB_REGISTER_FILE )?>" charset="utf-8"></script>-->

		<?php $this->print_fullscreen_button_html_javascript() ?>
		<?php if ($display_key) {
			require_once __DIR__ . '/geomap-key.php';
		} ?>
		<script>
		jQuery("#eh-form").appendTo(".my-custom-control");
		jQuery('#evidence-map fieldset').show();
		</script>
		<?php
		if($table){
			$this->renderGoogleTable();	
		}
		// <<html dump	
		return ob_get_clean();
	}

	protected function renderGoogleTable() {
		// See: js/map-visualization.js
		return;
		?>
        <script>
          google.load('visualization', '1.1', { packages: [ 'controls' ] });
        </script>
 <?php }
}