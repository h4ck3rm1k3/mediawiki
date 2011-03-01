/**
 * JavasSript for Google Maps v2 maps in the Maps extension.
 * @see http://www.mediawiki.org/wiki/Extension:Maps
 * 
 * @author Jeroen De Dauw <jeroendedauw at gmail dot com>
 */

/**
 * The Google Maps API is so nice to take care of loading all it's modules on it's own.
 * This means that when the API is loaded, not all modules will have yet, so we need to
 * wait untill they are available before setting up the maps. Try every 100ms.
 */
function loadGoogleMaps() {
	if ( typeof GLayer == 'undefined' ) {
		setTimeout( function() { loadGoogleMaps(); }, 100 );
	}
	else {
		if ( GBrowserIsCompatible() ) {
			window.unload = GUnload;
			
			window.GOverlays = [
		      	new GLayer("com.panoramio.all"),
		      	new GLayer("com.youtube.all"),
		      	new GLayer("org.wikipedia.en"),
		      	new GLayer("com.google.webcams")
		    ];		
			
			for ( i in window.maps.googlemaps2 ) {
				var map = new GoogleMap( jQuery, window.maps.googlemaps2[i] );
				map.initiate( i );
			}			
		}
		else {
			alert( mediaWiki.msg( 'maps-googlemaps2-incompatbrowser' ) );
			
			for ( i in window.maps.googlemaps2 ) {
				// FIXME: common module with message not getting loaded for some reason
				jQuery( '#' + i ).text( mediaWiki.msg( 'maps-load-failed' ) );
			}
		}
	}
}

// Load the Google Maps v2 API.
jQuery.getScript(
	'http://maps.google.com/maps?file=api&v=2&key=' + window.googleMapsKey + '&hl=' + window.googleLangCode,
	function () { loadGoogleMaps(); }
);

var GoogleMap = function( $, args ) {
	var args = cleanParameters( args );
	
	this.initiate = function( mapName ) {
		createGoogleMap( mapName, args );
	}
	
	function cleanParameters( args ) {
		
    	args.type = eval( args.type );
    	var ts=[];
    	var t;
    	while ( t = args.types.shift() ) {
    		ts.unshift( eval( t ) );
    	}
    	args.types = ts;
    	
    	args.types = ensureTypeIsSelectable( args.type, args.types );
    	
    	return args;
	}

    /**
     * Returns GMap2 object with the provided properties.
     */
    function createGoogleMap( mapName, args ) {
    	var mapElement = document.getElementById( mapName );
    	var map = new GMap2( mapElement, { mapTypes: args.types } );
    	map.name = mapName;
    	map.setMapType( args.type );	

    	var hasSearchBar = false;
    	
    	for ( i = args.controls.length - 1; i >= 0; i-- ) {
    		if ( args.controls[i] == 'searchbar' ) {
    			hasSearchBar = true;
    			break;
    		}
    	}
    	
    	// List of GControls: http://code.google.com/apis/maps/documentation/reference.html#GControl
    	for ( var i = 0, n = args.controls.length; i < n; i++ ) {
    		if ( args.controls[i] == 'auto' ) {
    			if ( mapElement.offsetHeight > 75 ) args.controls[i] = mapElement.offsetHeight > 320 ? 'large' : 'small';
    		}			

    		switch ( args.controls[i] ) {
    			case 'large' : 
    				map.addControl( new GLargeMapControl3D() );
    				break;
    			case 'small' : 
    				map.addControl( new GSmallZoomControl3D() );
    				break;
    			case 'large-original' : 
    				map.addControl( new GLargeMapControl() );
    				break;
    			case 'small-original' : 
    				map.addControl( new GSmallMapControl() );
    				break;
    			case 'zoom' : 
    				map.addControl( new GSmallZoomControl() );
    				break;
    			case 'type' : 
    				map.addControl( new GMapTypeControl() );
    				break;				
    			case 'type-menu' : 
    				map.addControl( new GMenuMapTypeControl() );
    				break;
    			case 'overlays' : 
    				map.addControl( new MoreControl() );
    				break;		
    			case 'overview' : case 'overview-map' : 
    				map.addControl( new GOverviewMapControl() );
    				break;
    			case 'scale' : 
    				if ( hasSearchBar ) {
    					map.addControl( new GScaleControl(), new GControlPosition( G_ANCHOR_BOTTOM_LEFT, new GSize( 5,37 ) ) );
    				}
    				else {
    					map.addControl( new GScaleControl() );
    				}
    				break;
    			case 'nav-label' : case 'nav' : 
    				map.addControl( new GNavLabelControl() );
    				break;
    			case 'searchbar' :
    				map.enableGoogleBar();
    				break;
    		}
    	}	
    	
/*
    	var bounds = ((mapOptions.zoom == null || mapOptions.centre == null) && markers.length > 1) ? new GLatLngBounds() : null;

    	for ( i = markers.length - 1; i >= 0; i-- ) {
    		var marker = markers[i];
    		marker.point = new GLatLng( marker.lat, marker.lon );
    		map.addOverlay( createGMarker( marker ) );
    		if ( bounds != null ) bounds.extend( marker.point );
    	}

    	if (bounds != null) {
    		map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));
    	}

    	if (mapOptions.centre != null) map.setCenter(mapOptions.centre);
    	if (mapOptions.zoom != null) map.setZoom(mapOptions.zoom);
    	
    	if (mapOptions.scrollWheelZoom) map.enableScrollWheelZoom();

    	map.enableContinuousZoom();
    	
    	// Code to add KML files.
    	var kmlOverlays = [];
    	for ( i = mapOptions.kml.length - 1; i >= 0; i-- ) {
    		kmlOverlays[i] = new GGeoXml( mapOptions.kml[i] );
    		map.addOverlay( kmlOverlays[i] );
    	}
    	
    	// Make the map variable available for other functions.
    	if (!window.GMaps) window.GMaps = new Object;
    	eval("window.GMaps." + mapName + " = map;"); 	
    	
    	return map;
    	*/
    }
    
    function ensureTypeIsSelectable( type, types ) {
    	var typesContainType = false;

    	for ( var i = 0, n = types.length; i < n; i++ ) {
    		if ( types[i] == type ) {
    			typesContainType = true;
    			break;
    		}
    	}

    	if ( !typesContainType ) {
    		types.push( type );
    	}
    	
    	return types;
    }

    /**
     * Returns GMarker object on the provided location. It will show a popup baloon
     * with title and label when clicked, if either of these is set.
     */
	function createGMarker( markerData ) {
    	var marker;
    	
    	if ( markerData.icon != '' ) {
    		var iconObj = new GIcon( G_DEFAULT_ICON );
    		iconObj.image = markerData.icon;
    		
    		var newimg = new Image();
    		newimg.src = markerData.icon;
    		
    		// Only do these things when there is an actual width, which there won,t the first time the image is loaded.
    		// FIXME: this means the image won't have it's correct size when it differs from the default on first load!
    		if ( newimg.width > 0 ) {
    			/* Determine size of icon and pass it in */
    			iconObj.iconSize.width = newimg.width;
    			iconObj.iconSize.height = newimg.height;
    			iconObj.shadow = null;	
    			
    			/* Anchor the icon on bottom middle */
    			var anchor = new GPoint();
    			anchor.x = Math.floor( newimg.width / 2 );
    			anchor.y = newimg.height;
    			iconObj.iconAnchor = anchor;			
    		}

    		marker = new GMarker( markerData.point, { icon: iconObj } );
    	} else {
    		marker = new GMarker( markerData.point );
    	}
    	
    	if ( ( markerData.title + markerData.label ).length != '' ) {
    		var bothTxtAreSet = markerData.title.length != '' && markerData.label.length != '';
    		var popupText = bothTxtAreSet ? '<b>' + markerData.title + '</b><hr />' + markerData.label : markerData.title + markerData.label;	
    		popupText = '<div style="overflow:auto;max-height:130px;">' + popupText + '</div>';

    		GEvent.addListener(marker, 'click',
    			function() {
    				marker.openInfoWindowHtml(popupText, {maxWidth:350});
    			}
    		);		
    	}

    	return marker;
    }    
    
    function setupCheckboxShiftClick() { return true; }

    function MoreControl() {};
    MoreControl.prototype = new GControl();

    MoreControl.prototype.initialize = function(map) {
    	this.map = map;
    	
    	var more = document.getElementById(map.name + "-outer-more");

    	var buttonDiv = document.createElement("div");
    	buttonDiv.id = map.name + "-more-button";
    	buttonDiv.title = "Show/Hide Overlays";
    	buttonDiv.style.border = "1px solid black";
    	buttonDiv.style.width = "86px";

    	var textDiv = document.createElement("div");
    	textDiv.id = map.name + "-inner-more";
    	textDiv.setAttribute('class', 'inner-more');
    	textDiv.appendChild(document.createTextNode( mediaWiki.msg( 'maps_overlays' ) ));

    	buttonDiv.appendChild(textDiv);

    	// Register Event handlers
    	more.onmouseover = showGLayerbox;
    	more.onmouseout = setGLayerboxClose;

    	// Insert the button just after outer_more div.
    	more.insertBefore(buttonDiv, document.getElementById(map.name + "-more-box").parentNode);

    	// Remove the whole div from its location and reinsert it to the map.
    	map.getContainer().appendChild(more);

    	return more;
    };

    MoreControl.prototype.getDefaultPosition = function() {
    	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 35));
    };

    function checkGChecked(mapName)	{
    	//	Returns true if	a checkbox is still	checked otherwise false.
    	var	boxes = document.getElementsByName(mapName + "-overlay-box");
    	for(var	i = 0; i < boxes.length; i++) {
    		if(boxes[i].checked) return true;
    	}
    	return false;
    }

    function showGLayerbox() {
    	var mapName = this.id.split('-')[0];	
    	eval("if(window.timer_" + mapName + ") clearTimeout(timer_" + mapName + ");");
    	document.getElementById(mapName + "-more-box").style.display = "block";
    	var	button = document.getElementById(mapName + "-inner-more");
    	button.style.borderBottomWidth = "4px";
    	button.style.borderBottomColor = "white";
    }


    function setGLayerboxClose() {
    	var mapName = this.id.split('-')[0];
    	var	layerbox = document.getElementById(mapName + "-more-box");
    	var	button = document.getElementById(mapName + "-inner-more");
    	var	bottomColor	= checkGChecked(mapName) ? "#6495ed" : "#c0c0c0";
    	eval("timer_" + mapName + " = window.setTimeout(function() { layerbox.style.display = 'none'; button.style.borderBottomWidth = '1px'; button.style.borderBottomColor = bottomColor; },	400);");
    }

    function switchGLayer(map, checked, layer) {
    	var	layerbox = document.getElementById(map.name + "-more-box");
    	var	button = document.getElementById(map.name + "-inner-more");
    	
    	if(checked)	{
    			map.addOverlay(layer);
    	}
    	else {
    			map.removeOverlay(layer);
    	}
    	
    }

    function initiateGOverlay(elementId, mapName, urlNr) {
    	document.getElementById(elementId).checked = true;
    	switchGLayer(GMaps[mapName], true, GOverlays[urlNr]);
    }	
		
}
	