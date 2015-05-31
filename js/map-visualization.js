/*!
  Summary-control & Google table visualization.

  From PHP: Evidence_Hub_Shortcode_GeoMap::renderGoogleTable()
*/

var OERRH = OERRH || {};

jQuery(function ($) {

	var C = window.console
	  , G = OERRH.geomap
	  , map_data = OERRH.map_json['geoJSON'] || null;

	var data, table;
	var pickers = {};
	var c = [];
	var tableArray = [];
	var summaryControl;

	//setTimeout(function () {

	summaryControl = L.Control.extend({
		options: {
			position: G.summary_position || 'bottomleft'
		},

		onAdd: function (map) {
			// create the control container with a particular class name
			var controlDiv = L.DomUtil.create('div', 'summary-table-block');
			controlDiv.innerHTML = "<div id='tbl-holder'><div class='tbl-header'>Results (<span id='result-count'></span>) <div class='expander'>▼</div></div><div id='summary-table'><div id='control1'></div><div id='table1'></div></div></div>";	
			L.DomEvent.disableClickPropagation(controlDiv);
			return controlDiv;
		}
	});

	G.map.addControl(new summaryControl());


	function drawVisualization() {
		// Prepare the data.
		var d = map_data;

		var row = ["id", "type", "name", "desc", "url", "sector", "polarity", "project", "hypothesis_id", "hypothesis", "locale"];
		if (d){
			/*for (var k in d[0].properties) {
				row.push(k);
			}*/
			tableArray.push(row);
			for (var i=0,  tI=d.length; i < tI; i++) {
				var row = [];
				for (var j=0,  tJ=tableArray[0].length; j < tJ; j++) {
					if (d[i].properties[tableArray[0][j]] instanceof Array) {
						row.push(d[i].properties[tableArray[0][j]].join(","));
					} else {
						row.push(d[i].properties[tableArray[0][j]]);
					}
				}
				tableArray.push(row);
			}
		}

		data = google.visualization.arrayToDataTable(tableArray, false);
		for (i=0; i<data.getNumberOfColumns(); i++){
			c[data.getColumnLabel(i)] = i;
		} 

		var formatter = new google.visualization.PatternFormat('<div>{1} - <span style="text-transform: capitalize;">{2}</span></div></div>');
		formatter.format(data, [c['url'],c['name'], c['type']], c['desc']);

		C && console.log(c);

		// Define a StringFilter control for the 'Name' column
		var stringFilter = new google.visualization.ControlWrapper({
			'controlType': 'StringFilter',
			'containerId': 'control1',
			'options': {
			'filterColumnIndex': c['name'],
				'matchType': 'any',
				'ui': { 'label': 'Search' }
			}
		});

		$(G.outer_map_sel).find('select').each(function (i, v) {
			var name = v.id.substring(13)
			pickers[name] = picker(name);

			v.addEventListener(
				'change',
				function() {
					pickers[name].setState({value: this.value});
					pickers[name].draw();
					document.getElementById('result-count').innerHTML = table.getDataTable().getNumberOfRows();
				},
				false
			);
		});

		var cssClassNames = {headerRow: 'tbl-head', 
							headerCell: 'tbl-head',
							tableRow: 'tbl-row',
							oddTableRow: 'tbl-row'};
			// Define a table visualization
		table = new google.visualization.ChartWrapper({
			  'chartType': 'Table',
			  'containerId': 'table1',
			  'options': {'height': '300px', 
						  'width': '22em',
						  //'page': 'enable',
						  //'pageSize': 5,
						  'allowHtml': true,
						  'pagingSymbols': {prev: 'prev', next: 'next'},
						  'pagingButtonsConfiguration': 'auto',
						  'cssClassNames': cssClassNames},
			  'view': {'columns': [c['desc']]}
		});

		google.visualization.events.addListener(table, 'ready', onReady);

		google.visualization.events.addListener(stringFilter, 'statechange', function () {
			var state = stringFilter.getState();
			document.getElementById('result-count').innerHTML = table.getDataTable().getNumberOfRows();
		});

		// Create the dashboard.
		var dashboard = new google.visualization.Dashboard(document.getElementById('summary-table')).
			// Configure the string filter to affect the table contents
			bind(stringFilter, table);

		for (pick in pickers){
			dashboard.bind(pickers[pick], table);
		}
		//bind(pickers['type'], table).
		// Draw the dashboard
		dashboard.draw(data);
	  
		document.getElementById('tbl-holder').style.display = 'block';
		}

	function onReady(){
		google.visualization.events.addListener(table.getChart() , 'select', function(){
			var sel = table.getChart().getSelection();
			map.setZoom(3);
			var curID = table.getDataTable().getValue(sel[0].row, c['id']);
			var currentMarker = markerMap[curID];
			setTimeout(function() {
				markers.zoomToShowLayer(currentMarker, function(){
					currentMarker.openPopup();
				});
			}, 1000);

			table.getChart().setSelection(null);
			event.preventDefault();
			return false;
		});
		document.getElementById('result-count').innerHTML = table.getDataTable().getNumberOfRows();

		$(G.loading_sel).hide();
	}

	function picker(type){
		var newdiv = document.createElement('div');
		newdiv.setAttribute('id','control-'+type);
		newdiv.setAttribute('style', 'display:none');
		document.getElementById('summary-table').appendChild(newdiv);
			
		return new google.visualization.ControlWrapper({
			'controlType': 'StringFilter',
			'containerId': 'control-'+type,
			'options': {
				'filterColumnIndex': c[type],
				'ui': {
					'allowTyping': false,
				}
			},
		});   
	}

	//}, 3000); //End: setTimeout.

	google.setOnLoadCallback(drawVisualization);

});
