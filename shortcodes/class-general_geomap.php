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
	var $shortcode = 'geomap';
	var $defaults = array(
		'title' => false,
		'no_evidence_message' => "There is no map yet to display",
		'title_tag' => 'h3',
		'type' => 'evidence'
	);

	static $post_types_with_shortcode = array();
	
	/**
	* Generate post content.
	*
	* @since 0.1.1
	* @return string.
	*/
	function content() {
		ob_start();
		extract($this->options);
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
					'label' => "Hypothesis",
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
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.css" />
         <script src="http://cdn.leafletjs.com/leaflet-0.7/leaflet.js"></script>
         <div id="evidence-map">
         	<div id="map"></div>
            <?php $post = NULL; include(sprintf("%s/post-types/custom_post_metaboxes.php", EVIDENCE_HUB_PATH));?>
         </div>
         <div id="fullscreen-button"><a href="#" id="evidence-map-fullscreen">Full Screen</a></div>
         <script type="application/javascript">
		 /* <![CDATA[ */	
		 	var json = <?php print_r(file_get_contents(site_url().'/'.get_option('json_api_base', 'api').'/hub/get_geojson/?count=-1&type='.strtolower($type))); ?>;	
			var hubPoints = json['geoJSON'] || null;
			var pluginurl = '<?php echo EVIDENCE_HUB_URL; ?>';
			jQuery('#map').css('height', parseInt(jQuery('#evidence-map').width()*9/16));	
		/* ]]> */
		</script>
        <link rel="stylesheet" href="<?php echo plugins_url( 'js/markercluster/MarkerCluster.css' , EVIDENCE_HUB_REGISTER_FILE )?>" />
        <link rel="stylesheet" href="<?php echo plugins_url( 'js/markercluster/MarkerCluster.Default.css' , EVIDENCE_HUB_REGISTER_FILE )?>" />
        <script src="<?php echo plugins_url( 'js/markercluster/leaflet.markercluster-src.js' , EVIDENCE_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url( 'js/leaflet-map.js' , EVIDENCE_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
		<script src="<?php echo plugins_url( 'lib/map/lib/bigscreen.min.js' , EVIDENCE_HUB_REGISTER_FILE )?>" type="text/javascript" charset="utf-8"></script>
		<script>
		var element = document.getElementById('evidence-map');
		document.getElementById('evidence-map-fullscreen').addEventListener('click', function() {
			if (BigScreen.enabled) {
				BigScreen.request(element, onEnterEvidenceMap, onExitEvidenceMap);
				// You could also use .toggle(element, onEnter, onExit, onError)
			}
			else {
				// fallback for browsers that don't support full screen
			}
		}, false);
		
			// called when the first element enters full screen
		
		function onEnterEvidenceMap(){
			jQuery('#evidence-map').css('height','100%');
			jQuery('#map').css('height', jQuery('#evidence-map').height());
			map.invalidateSize();
		}
		function onExitEvidenceMap(){
			jQuery('#evidence-map').css('height','');
			jQuery('#map').css('height', parseInt(jQuery('#evidence-map').width()*9/16));
			map.invalidateSize();
		}
		jQuery("#eh-form").appendTo(".my-custom-control");
		jQuery('#evidence-map fieldset').show();
		</script>
		<?php
		// <<html dump	
		return ob_get_clean();
	}
}