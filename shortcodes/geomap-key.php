<?php
/**
 * Key for [geomap] shortcode (class-general_geomap.php).
 *
 * @link  js/markercluster/leaflet.markercluster-src.js#L620-626  Cluster boundaries.
 */

function _key_marker($name = 'evidence', $alias = null) {
    $stem = explode( '-', $name );
    $stem = $stem[ 0 ];
    $marker_url = plugins_url( 'images/icons/marker-' .$name . '.png', __DIR__ );

    echo "<li class='ac-marker $stem $name $alias'><img src='$marker_url'>";
}
?>

<div id="key" class="evidence-map-key geomap-key key-type-<?php echo $type ?>">
<h3>Key</h3>
<ul>
  <li class="marker cluster"><div class="marker-cluster marker-cluster-small" ><div><span>1</span></div></div>
    Small cluster: <?php if('evidence'==$type):?>limited evidence<?php else:?>a limited number of items<?php endif;?> <small>(green)</small>
  <li class="marker cluster"><div class="marker-cluster marker-cluster-medium"><div><span>21</span></div></div>
    Medium cluster: more than 20 items <?php if('evidence'==$type):?>of evidence<?php endif;?> <small>(yellow)</small>
  <li class="marker cluster"><div class="marker-cluster marker-cluster-large" ><div><span>101</span></div></div>
    Large cluster: more than 100 items <?php if('evidence'==$type):?>of evidence<?php endif;?> <small>(red)</small>

  <?php _key_marker( 'evidence-pos' )?> Positive evidence <small>(orange pin)</small>
  <?php _key_marker( 'evidence', 'ev-neutral' )?> Neutral evidence, mixed evidence or no polarity given <small>(blue pin)</small>
  <?php #<li class="marker evidence"             > Polarity not given <small>(blue pin)</small> ?>
  <?php _key_marker( 'evidence-neg' )?> Negative evidence <small>(grey pin)</small>

  <?php _key_marker( 'project' )?> Project <small>(dark blue pin)</small>

  <?php _key_marker( 'policy-international' )?> International policy <small>(orange square marker)</small>
  <?php _key_marker( 'policy-local' )   ?> Local/institutional policy
  <?php _key_marker( 'policy-national' )?> National policy
  <?php _key_marker( 'policy-regional' )?> Regional policy
</ul>
</div>
