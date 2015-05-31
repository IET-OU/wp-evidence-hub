/*!
  Fullscreen button for map(s).

  From PHP: Evidence_Hub_Shortcode::print_fullscreen_button_html_javascript()
*/

var OERRH = OERRH || {};

jQuery(function ($) {

	var G = OERRH.geomap
	  , $button = $('#evidence-map-fullscreen')
	  , $map_outer = $(G.outer_map_sel)
	  , $map = $(G.map_sel)
	  , element = document.getElementById(G.outer_map_id);

	$button.on('click', function () {
		if (BigScreen.enabled) {
			BigScreen.request(element,
			function () {
				$map_outer.css('height', '100%');
				$map.css('height', $map_outer.height());
				G.map.invalidateSize();
			},
			function () {
				$map_outer.css('height', '');
				$map.css('height', parseInt($map_outer.width() * 9/16));
				G.map.invalidateSize();
			});
			// You could also use .toggle(element, onEnter, onExit, onError)
		}
		else {
			// fallback for browsers that don't support full screen
		}
	});
});
