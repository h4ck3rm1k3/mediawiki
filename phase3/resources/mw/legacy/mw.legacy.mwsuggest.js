/*
 * Legacy emulation for the now depricated skins/common/mwsuggest.js
 * 
 * OpenSearch ajax suggestion engine for MediaWiki
 * 
 * Uses core MediaWiki open search support to fetch suggestions and show them below search boxes and other inputs
 *
 * by Robert Stojnic (April 2008)
 */

( function( $, mw ) {

/* Extension */

$.extend( mw.legacy, {
	
	/* Global Variables */
	
	// search_box_id -> Results object
	'os_map': {},
	// cached data, url -> json_text
	'os_cache': {},
	// global variables for suggest_keypress
	'os_cur_keypressed': 0,
	'os_keypressed_count': 0,
	// type: Timer
	'os_timer': null,
	// tie mousedown/up events
	'os_mouse_pressed': false,
	'os_mouse_num': -1,
	// if true, the last change was made by mouse (and not keyboard)
	'os_mouse_moved': false,
	// delay between keypress and suggestion (in ms)
	'os_search_timeout': 250,
	// these pairs of inputs/forms will be autoloaded at startup
	'os_autoload_inputs': new Array('searchInput', 'searchInput2', 'powerSearchText', 'searchText'),
	'os_autoload_forms': new Array('searchform', 'searchform2', 'powersearch', 'search' ),
	// if we stopped the service
	'os_is_stopped': false,
	// max lines to show in suggest table
	'os_max_lines_per_suggest': 7,
	// number of steps to animate expansion/contraction of container width
	'os_animation_steps': 6,
	// num of pixels of smallest step
	'os_animation_min_step': 2,
	// delay between steps (in ms)
	'os_animation_delay': 30,
	// max width of container in percent of normal size (1 == 100%)
	'os_container_max_width': 2,
	// currently active animation timer
	'os_animation_timer': null,
	// <datalist> is a new HTML5 element that allows you to manually supply
	// suggestion lists and have them rendered according to the right platform
	// conventions.  However, the only shipping browser as of early 2010 is Opera,
	// and that has a fatal problem: the suggestion lags behind what the user types
	// by one keypress.  (Reported as DSK-276870 to Opera's secret bug tracker.)
	// The code here otherwise seems to work, though, so this can be flipped on
	// (maybe with a UA check) when some browser has a better implementation.
	// 'os_use_datalist': 'list' in document.createElement( 'input' ),
	'os_use_datalist': false,
	
	/* Functions */
	
	/**
	 * Timeout timer class that will fetch the results
	 */
	'os_Timer': function( id, r, query ) {
		this.id = id;
		this.r = r;
		this.query = query;
	},
	/** 
	 * Property class for single search box
	 */
	'os_Results': function( name, formname ) {
		this.searchform = formname; // id of the searchform
		this.searchbox = name; // id of the searchbox
		this.container = name + 'Suggest'; // div that holds results
		this.resultTable = name + 'Result'; // id base for the result table (+num = table row)
		this.resultText = name + 'ResultText'; // id base for the spans within result tables (+num)
		this.toggle = name + 'Toggle'; // div that has the toggle (enable/disable) link
		this.query = null; // last processed query
		this.results = null;  // parsed titles
		this.resultCount = 0; // number of results
		this.original = null; // query that user entered
		this.selected = -1; // which result is selected
		this.containerCount = 0; // number of results visible in container
		this.containerRow = 0; // height of result field in the container
		this.containerTotal = 0; // total height of the container will all results
		this.visible = false; // if container is visible
		this.stayHidden = false; // don't try to show if lost focus
	},
	/**
	 * Timer user to animate expansion/contraction of container width
	 */
	'os_AnimationTimer': function( r, target ) {
		this.r = r;
		var current = document.getElementById(r.container).offsetWidth;
		this.inc = Math.round( ( target - current ) / os_animation_steps );
		if( this.inc < os_animation_min_step && this.inc >=0 ) {
			this.inc = os_animation_min_step; // minimal animation step
		}
		if( this.inc > -os_animation_min_step && this.inc < 0 ) {
			this.inc = -os_animation_min_step;
		}
		this.target = target;
	},
	
	/* Intialization Functions */
	
	/**
	 * Initialization, call upon page onload 
	 */
	'os_MWSuggestInit': function() {
		for( i = 0; i < os_autoload_inputs.length; i++ ) {
			var id = os_autoload_inputs[i];
			var form = os_autoload_forms[i];
			element = document.getElementById( id );
			if( element != null ) {
				os_initHandlers( id, form, element );
			}
		}
	},
	/**
	 * Init Result objects and event handlers
	 */
	'os_initHandlers': function( name, formname, element ) {
		var r = new os_Results( name, formname );
		var formElement = document.getElementById( formname );
		if( !formElement ) {
			// Older browsers (Opera 8) cannot get form elements
			return;
		}
		// event handler
		os_hookEvent( element, 'keyup', function( event ) { os_eventKeyup( event ); } );
		os_hookEvent( element, 'keydown', function( event ) { os_eventKeydown( event ); } );
		os_hookEvent( element, 'keypress', function( event ) { os_eventKeypress( event ); } );
		if ( !os_use_datalist ) {
			// These are needed for the div hack to hide it if the user blurs.
			os_hookEvent( element, 'blur', function( event ) { os_eventBlur( event ); } );
			os_hookEvent( element, 'focus', function( event ) { os_eventFocus( event ); } );
			// We don't want browser auto-suggestions interfering with our div, but
			// autocomplete must be on for datalist to work (at least in Opera
			// 10.10).
			element.setAttribute( 'autocomplete', 'off' );
		}
		// stopping handler
		os_hookEvent( formElement, 'submit', function( event ) { return os_eventOnsubmit( event ); } );
		os_map[name] = r;
		// toggle link
		if( document.getElementById( r.toggle ) == null ) {
			// TODO: disable this while we figure out a way for this to work in all browsers
			/* if( name == 'searchInput' ) {
				// special case: place above the main search box
				var t = os_createToggle( r, 'os-suggest-toggle' );
				var searchBody = document.getElementById( 'searchBody' );
				var first = searchBody.parentNode.firstChild.nextSibling.appendChild(t);
			} else {
				// default: place below search box to the right
				var t = os_createToggle( r, 'os-suggest-toggle-def' );
				var top = element.offsetTop + element.offsetHeight;
				var left = element.offsetLeft + element.offsetWidth;
				t.style.position = 'absolute';
				t.style.top = top + 'px';
				t.style.left = left + 'px';
				element.parentNode.appendChild( t );
				// only now width gets calculated, shift right
				left -= t.offsetWidth;
				t.style.left = left + 'px';
				t.style.visibility = 'visible';
			} */
		}
	},
	'os_hookEvent': function( element, hookName, hookFunct ) {
		if ( element.addEventListener ) {
			element.addEventListener( hookName, hookFunct, false );
		} else if ( window.attachEvent ) {
			element.attachEvent( 'on' + hookName, hookFunct );
		}
	},
	
	/* Keyboard Event Functions */

	/**
	 * Event handler that will fetch results on keyup
	 */
	'os_eventKeyup': function( e ) {
		var targ = os_getTarget( e );
		var r = os_map[targ.id];
		if( r == null ) {
			return; // not our event
		}

		// some browsers won't generate keypressed for arrow keys, catch it
		if( os_keypressed_count == 0 ) {
			os_processKey( r, os_cur_keypressed, targ );
		}
		var query = targ.value;
		os_fetchResults( r, query, os_search_timeout );
	},
	/**
	 * Catch arrows up/down and escape to hide the suggestions
	 */
	'os_processKey': function( r, keypressed, targ ) {
		if ( keypressed == 40 && !r.visible && os_timer == null ) {
			// If the user hits the down arrow, fetch results immediately if none
			// are already displayed.
			r.query = '';
			os_fetchResults( r, targ.value, 0 );
		}
		// Otherwise, if we're not using datalist, we need to handle scrolling and
		// so on.
		if ( os_use_datalist ) {
			return;
		}
		if ( keypressed == 40 ) { // Arrow Down
			if ( r.visible ) {
				os_changeHighlight( r, r.selected, r.selected + 1, true );
			}
		} else if ( keypressed == 38 ) { // Arrow Up
			if ( r.visible ) {
				os_changeHighlight( r, r.selected, r.selected - 1, true );
			}
		} else if( keypressed == 27 ) { // Escape
			document.getElementById( r.searchbox ).value = r.original;
			r.query = r.original;
			os_hideResults( r );
		} else if( r.query != document.getElementById( r.searchbox ).value ) {
			// os_hideResults( r ); // don't show old suggestions
		}
	},
	/**
	 * When keys is held down use a timer to output regular events
	 */
	'os_eventKeypress': function( e ) {
		var targ = os_getTarget( e );
		var r = os_map[targ.id];
		if( r == null ) {
			return; // not our event
		}

		var keypressed = os_cur_keypressed;

		os_keypressed_count++;
		os_processKey( r, keypressed, targ );
	},
	/**
	 * Catch the key code (Firefox bug)
	 */
	'os_eventKeydown': function( e ) {
		if ( !e ) {
			e = window.event;
		}
		var targ = os_getTarget( e );
		var r = os_map[targ.id];
		if( r == null ) {
			return; // not our event
		}

		os_mouse_moved = false;

		os_cur_keypressed = ( e.keyCode == undefined ) ? e.which : e.keyCode;
		os_keypressed_count = 0;
	},
	/**
	 * When the form is submitted hide everything, cancel updates...
	 */
	'os_eventOnsubmit': function( e ) {
		var targ = os_getTarget( e );

		os_is_stopped = true;
		// kill timed requests
		if( os_timer != null && os_timer.id != null ) {
			clearTimeout( os_timer.id );
			os_timer = null;
		}
		// Hide all suggestions
		for( i = 0; i < os_autoload_inputs.length; i++ ) {
			var r = os_map[os_autoload_inputs[i]];
			if( r != null ) {
				var b = document.getElementById( r.searchform );
				if( b != null && b == targ ) {
					// set query value so the handler won't try to fetch additional results
					r.query = document.getElementById( r.searchbox ).value;
				}
				os_hideResults( r );
			}
		}
		return true;
	},
	/**
	 * Hide results from the user, either making the div visibility=hidden or detaching the datalist from the input.
	 */
	'os_hideResults': function( r ) {
		if ( os_use_datalist ) {
			document.getElementById( r.searchbox ).setAttribute( 'list', '' );
		} else {
			var c = document.getElementById( r.container );
			if ( c != null ) {
				c.style.visibility = 'hidden';
			}
		}
		r.visible = false;
		r.selected = -1;
	},
	'os_decodeValue': function( value ) {
		if ( decodeURIComponent ) {
			return decodeURIComponent( value );
		}
		if( unescape ) {
			return unescape( value );
		}
		return null;
	},
	'os_encodeQuery': function( value ) {
		if ( encodeURIComponent ) {
			return encodeURIComponent( value );
		}
		if( escape ) {
			return escape( value );
		}
		return null;
	},
	/**
	 * Handles data from XMLHttpRequest, and updates the suggest results
	 */
	'os_updateResults': function( r, query, text, cacheKey ) {
		os_cache[cacheKey] = text;
		r.query = query;
		r.original = query;
		if( text == '' ) {
			r.results = null;
			r.resultCount = 0;
			os_hideResults( r );
		} else {
			try {
				var p = eval( '(' + text + ')' ); // simple json parse, could do a safer one
				if( p.length < 2 || p[1].length == 0 ) {
					r.results = null;
					r.resultCount = 0;
					os_hideResults( r );
					return;
				}
				if ( os_use_datalist ) {
					os_setupDatalist( r, p[1] );
				} else {
					os_setupDiv( r, p[1] );
				}
			} catch( e ) {
				// bad response from server or such
				os_hideResults( r );
				os_cache[cacheKey] = null;
			}
		}
	},
	/**
	 * Create and populate a <datalist>.
	 *
	 * @param r       os_Result object
	 * @param results Array of the new results to replace existing ones
	 */
	'os_setupDatalist': function( r, results ) {
		var s = document.getElementById( r.searchbox );
		var c = document.getElementById( r.container );
		if ( c == null ) {
			c = document.createElement( 'datalist' );
			c.setAttribute( 'id', r.container );
			document.body.appendChild( c );
		} else {
			c.innerHTML = '';
		}
		s.setAttribute( 'list', r.container );

		r.results = new Array();
		r.resultCount = results.length;
		r.visible = true;
		for ( i = 0; i < results.length; i++ ) {
			var title = os_decodeValue( results[i] );
			var opt = document.createElement( 'option' );
			opt.value = title;
			r.results[i] = title;
			c.appendChild( opt );
		}
	},
	/**
	 * Fetch namespaces from checkboxes or hidden fields in the search form, if none defined use wgSearchNamespaces
	 * global
	 */
	'os_getNamespaces': function( r ) {
		var namespaces = '';
		var elements = document.forms[r.searchform].elements;
		for( i = 0; i < elements.length; i++ ) {
			var name = elements[i].name;
			if( typeof name != 'undefined' && name.length > 2 && name[0] == 'n' &&
				name[1] == 's' && (
					( elements[i].type == 'checkbox' && elements[i].checked ) ||
					( elements[i].type == 'hidden' && elements[i].value == '1' )
				)
			) {
				if( namespaces != '' ) {
					namespaces += '|';
				}
				namespaces += name.substring( 2 );
			}
		}
		if( namespaces == '' ) {
			namespaces = wgSearchNamespaces.join('|');
		}
		return namespaces;
	},
	/**
	 * Update results if user hasn't already typed something else
	 */
	'os_updateIfRelevant': function( r, query, text, cacheKey ) {
		var t = document.getElementById( r.searchbox );
		if( t != null && t.value == query ) { // check if response is still relevant
			os_updateResults( r, query, text, cacheKey );
		}
		r.query = query;
	},
	/**
	 * Fetch results after some timeout
	 */
	'os_delayedFetch': function() {
		if( os_timer == null ) {
			return;
		}
		var r = os_timer.r;
		var query = os_timer.query;
		os_timer = null;
		var path = wgMWSuggestTemplate.replace( "{namespaces}", os_getNamespaces( r ) )
										.replace( "{dbname}", wgDBname )
										.replace( "{searchTerms}", os_encodeQuery( query ) );
		// try to get from cache, if not fetch using ajax
		var cached = os_cache[path];
		if( cached != null && cached != undefined ) {
			os_updateIfRelevant( r, query, cached, path );
		} else {
			var xmlhttp = sajax_init_object();
			if( xmlhttp ) {
				try {
					xmlhttp.open( 'GET', path, true );
					xmlhttp.onreadystatechange = function() {
						if ( xmlhttp.readyState == 4 && typeof os_updateIfRelevant == 'function' ) {
							os_updateIfRelevant( r, query, xmlhttp.responseText, path );
						}
					};
					xmlhttp.send( null );
				} catch ( e ) {
					if ( window.location.hostname == 'localhost' ) {
						alert( "Your browser blocks XMLHttpRequest to 'localhost', try using a real hostname for development/testing." );
					}
					throw e;
				}
			}
		}
	},
	/**
	 * Init timed update via os_delayedUpdate()
	 */
	'os_fetchResults': function( r, query, timeout ) {
		if( query == '' ) {
			r.query = '';
			os_hideResults( r );
			return;
		} else if( query == r.query ) {
			return; // no change
		}

		os_is_stopped = false; // make sure we're running

		// cancel any pending fetches
		if( os_timer != null && os_timer.id != null ) {
			clearTimeout( os_timer.id );
		}
		// schedule delayed fetching of results
		if( timeout != 0 ) {
			os_timer = new os_Timer( setTimeout( "os_delayedFetch()", timeout ), r, query );
		} else {
			os_timer = new os_Timer( null, r, query );
			os_delayedFetch(); // do it now!
		}
	},
	/**
	 * Find event target
	 */
	'os_getTarget': function( e ) {
		if ( !e ) {
			e = window.event;
		}
		if ( e.target ) {
			return e.target;
		} else if ( e.srcElement ) {
			return e.srcElement;
		} else {
			return null;
		}
	},
	/**
	 * Check if x is a valid integer
	 */
	'os_isNumber': function( x ) {
		if( x == '' || isNaN( x ) ) {
			return false;
		}
		for( var i = 0; i < x.length; i++ ) {
			var c = x.charAt( i );
			if( !( c >= '0' && c <= '9' ) ) {
				return false;
			}
		}
		return true;
	},
	/**
	 * Call this to enable suggestions on input (id=inputId), on a form (name=formName)
	 */
	'os_enableSuggestionsOn': function( inputId, formName ) {
		os_initHandlers( inputId, formName, document.getElementById( inputId ) );
	},
	/**
	 * Call this to disable suggestios on input box (id=inputId)
	 */
	'os_disableSuggestionsOn': function( inputId ) {
		r = os_map[inputId];
		if( r != null ) {
			// cancel/hide results
			os_timer = null;
			os_hideResults( r );
			// turn autocomplete on !
			document.getElementById( inputId ).setAttribute( 'autocomplete', 'on' );
			// remove descriptor
			os_map[inputId] = null;
		}

		// Remove the element from the os_autoload_* arrays
		var index = os_autoload_inputs.indexOf( inputId );
		if ( index >= 0 ) {
			os_autoload_inputs[index] = os_autoload_forms[index] = '';
		}
	},
	
	/* Div-only Functions */
	
	/**
	 * Event: loss of focus of input box
	 */
	'os_eventBlur': function( e ) {
		var targ = os_getTarget( e );
		var r = os_map[targ.id];
		if( r == null ) {
			return; // not our event
		}
		if( !os_mouse_pressed ) {
			os_hideResults( r );
			// force canvas to stay hidden
			r.stayHidden = true;
			// cancel any pending fetches
			if( os_timer != null && os_timer.id != null ) {
				clearTimeout( os_timer.id );
			}
			os_timer = null;
		}
	},
	/**
	 * Event: focus (catch only when stopped)
	 */
	'os_eventFocus': function( e ) {
		var targ = os_getTarget( e );
		var r = os_map[targ.id];
		if( r == null ) {
			return; // not our event
		}
		r.stayHidden = false;
	},
	/**
	 * Create and populate a <div>, for non-<datalist>-supporting browsers.
	 *
	 * @param r       os_Result object
	 * @param results Array of the new results to replace existing ones
	 */
	'os_setupDiv': function( r, results ) {
		var c = document.getElementById( r.container );
		if ( c == null ) {
			c = os_createContainer( r );
		}
		c.innerHTML = os_createResultTable( r, results );
		// init container table sizes
		var t = document.getElementById( r.resultTable );
		r.containerTotal = t.offsetHeight;
		r.containerRow = t.offsetHeight / r.resultCount;
		os_fitContainer( r );
		os_trimResultText( r );
		os_showResults( r );
	},
	/**
	 * Create the result table to be placed in the container div
	 */
	'os_createResultTable': function( r, results ) {
		var c = document.getElementById( r.container );
		var width = c.offsetWidth - os_operaWidthFix( c.offsetWidth );
		var html = '<table class="os-suggest-results" id="' + r.resultTable + '" style="width: ' + width + 'px;">';
		r.results = new Array();
		r.resultCount = results.length;
		for( i = 0; i < results.length; i++ ) {
			var title = os_decodeValue( results[i] );
			r.results[i] = title;
			html += '<tr><td class="os-suggest-result" id="' + r.resultTable + i + '"><span id="' + r.resultText + i + '">' + title + '</span></td></tr>';
		}
		html += '</table>';
		return html;
	},
	/**
	 * Show results div
	 */
	'os_showResults': function( r ) {
		if( os_is_stopped ) {
			return;
		}
		if( r.stayHidden ) {
			return;
		}
		os_fitContainer( r );
		var c = document.getElementById( r.container );
		r.selected = -1;
		if( c != null ) {
			c.scrollTop = 0;
			c.style.visibility = 'visible';
			r.visible = true;
		}
	},
	'os_operaWidthFix': function( x ) {
		// For browsers that don't understand overflow-x, estimate scrollbar width
		if( typeof document.body.style.overflowX != 'string' ) {
			return 30;
		}
		return 0;
	},
	
	/* Brower-dependent Functions */
	
	'f_clientWidth': function() {
		return f_filterResults(
			window.innerWidth ? window.innerWidth : 0,
			document.documentElement ? document.documentElement.clientWidth : 0,
			document.body ? document.body.clientWidth : 0
		);
	},
	'f_clientHeight': function() {
		return f_filterResults(
			window.innerHeight ? window.innerHeight : 0,
			document.documentElement ? document.documentElement.clientHeight : 0,
			document.body ? document.body.clientHeight : 0
		);
	},
	'f_scrollLeft': function() {
		return f_filterResults(
			window.pageXOffset ? window.pageXOffset : 0,
			document.documentElement ? document.documentElement.scrollLeft : 0,
			document.body ? document.body.scrollLeft : 0
		);
	},
	'f_scrollTop': function() {
		return f_filterResults(
			window.pageYOffset ? window.pageYOffset : 0,
			document.documentElement ? document.documentElement.scrollTop : 0,
			document.body ? document.body.scrollTop : 0
		);
	},
	'f_filterResults': function( n_win, n_docel, n_body ) {
		var n_result = n_win ? n_win : 0;
		if ( n_docel && ( !n_result || ( n_result > n_docel ) ) ) {
			n_result = n_docel;
		}
		return n_body && ( !n_result || ( n_result > n_body ) ) ? n_body : n_result;
	},
	/**
	 * Get the height available for the results container
	 */
	'os_availableHeight': function( r ) {
		var absTop = document.getElementById( r.container ).style.top;
		var px = absTop.lastIndexOf( 'px' );
		if( px > 0 ) {
			absTop = absTop.substring( 0, px );
		}
		return f_clientHeight() - ( absTop - f_scrollTop() );
	},
	/**
	 * Get element absolute position {left,top}
	 */
	'os_getElementPosition': function( elemID ) {
		var offsetTrail = document.getElementById( elemID );
		var offsetLeft = 0;
		var offsetTop = 0;
		while ( offsetTrail ) {
			offsetLeft += offsetTrail.offsetLeft;
			offsetTop += offsetTrail.offsetTop;
			offsetTrail = offsetTrail.offsetParent;
		}
		if ( navigator.userAgent.indexOf('Mac') != -1 && typeof document.body.leftMargin != 'undefined' ) {
			offsetLeft += document.body.leftMargin;
			offsetTop += document.body.topMargin;
		}
		return { left:offsetLeft, top:offsetTop };
	},
	/**
	 * Create the container div that will hold the suggested titles
	 */
	'os_createContainer': function( r ) {
		var c = document.createElement( 'div' );
		var s = document.getElementById( r.searchbox );
		var pos = os_getElementPosition( r.searchbox );
		var left = pos.left;
		var top = pos.top + s.offsetHeight;
		c.className = 'os-suggest';
		c.setAttribute( 'id', r.container );
		document.body.appendChild( c );

		// dynamically generated style params
		// IE workaround, cannot explicitely set "style" attribute
		c = document.getElementById( r.container );
		c.style.top = top + 'px';
		c.style.left = left + 'px';
		c.style.width = s.offsetWidth + 'px';

		// mouse event handlers
		c.onmouseover = function( event ) { os_eventMouseover( r.searchbox, event ); };
		c.onmousemove = function( event ) { os_eventMousemove( r.searchbox, event ); };
		c.onmousedown = function( event ) { return os_eventMousedown( r.searchbox, event ); };
		c.onmouseup = function( event ) { os_eventMouseup( r.searchbox, event ); };
		return c;
	},
	/**
	 * Change container height to fit to screen
	 */
	'os_fitContainer': function( r ) {
		var c = document.getElementById( r.container );
		var h = os_availableHeight( r ) - 20;
		var inc = r.containerRow;
		h = parseInt( h / inc ) * inc;
		if( h < ( 2 * inc ) && r.resultCount > 1 ) { // min: two results
			h = 2 * inc;
		}
		if( ( h / inc ) > os_max_lines_per_suggest ) {
			h = inc * os_max_lines_per_suggest;
		}
		if( h < r.containerTotal ) {
			c.style.height = h + 'px';
			r.containerCount = parseInt( Math.round( h / inc ) );
		} else {
			c.style.height = r.containerTotal + 'px';
			r.containerCount = r.resultCount;
		}
	},
	/**
	 * If some entries are longer than the box, replace text with "..."
	 */
	'os_trimResultText': function( r ) {
		// find max width, first see if we could expand the container to fit it
		var maxW = 0;
		for( var i = 0; i < r.resultCount; i++ ) {
			var e = document.getElementById( r.resultText + i );
			if( e.offsetWidth > maxW ) {
				maxW = e.offsetWidth;
			}
		}
		var w = document.getElementById( r.container ).offsetWidth;
		var fix = 0;
		if( r.containerCount < r.resultCount ) {
			fix = 20; // give 20px for scrollbar
		} else {
			fix = os_operaWidthFix( w );
		}
		if( fix < 4 ) {
			fix = 4; // basic padding
		}
		maxW += fix;

		// resize container to fit more data if permitted
		var normW = document.getElementById( r.searchbox ).offsetWidth;
		var prop = maxW / normW;
		if( prop > os_container_max_width ) {
			prop = os_container_max_width;
		} else if( prop < 1 ) {
			prop = 1;
		}
		var newW = Math.round( normW * prop );
		if( w != newW ) {
			w = newW;
			if( os_animation_timer != null ) {
				clearInterval( os_animation_timer.id );
			}
			os_animation_timer = new os_AnimationTimer( r, w );
			os_animation_timer.id = setInterval( "os_animateChangeWidth()", os_animation_delay );
			w -= fix; // this much is reserved
		}

		// trim results
		if( w < 10 ) {
			return;
		}
		for( var i = 0; i < r.resultCount; i++ ) {
			var e = document.getElementById( r.resultText + i );
			var replace = 1;
			var lastW = e.offsetWidth + 1;
			var iteration = 0;
			var changedText = false;
			while( e.offsetWidth > w && ( e.offsetWidth < lastW || iteration < 2 ) ) {
				changedText = true;
				lastW = e.offsetWidth;
				var l = e.innerHTML;
				e.innerHTML = l.substring( 0, l.length - replace ) + '...';
				iteration++;
				replace = 4; // how many chars to replace
			}
			if( changedText ) {
				// show hint for trimmed titles
				document.getElementById( r.resultTable + i ).setAttribute( 'title', r.results[i] );
			}
		}
	},
	/**
	 * Invoked on timer to animate change in container width
	 */
	'os_animateChangeWidth': function() {
		var r = os_animation_timer.r;
		var c = document.getElementById( r.container );
		var w = c.offsetWidth;
		var normW = document.getElementById( r.searchbox ).offsetWidth;
		var normL = os_getElementPosition( r.searchbox ).left;
		var inc = os_animation_timer.inc;
		var target = os_animation_timer.target;
		var nw = w + inc;
		if( ( inc > 0 && nw >= target ) || ( inc <= 0 && nw <= target ) ) {
			// finished !
			c.style.width = target + 'px';
			clearInterval( os_animation_timer.id );
			os_animation_timer = null;
		} else {
			// in-progress
			c.style.width = nw + 'px';
			if( document.documentElement.dir == 'rtl' ) {
				c.style.left = ( normL + normW + ( target - nw ) - os_animation_timer.target - 1 ) + 'px';
			}
		}
	},
	/**
	 * Change the highlighted row (i.e. suggestion), from position cur to next
	 */
	'os_changeHighlight': function( r, cur, next, updateSearchBox ) {
		if ( next >= r.resultCount ) {
			next = r.resultCount - 1;
		}
		if ( next < -1 ) {
			next = -1;
		}
		r.selected = next;
		if ( cur == next ) {
			return; // nothing to do.
		}

		if( cur >= 0 ) {
			var curRow = document.getElementById( r.resultTable + cur );
			if( curRow != null ) {
				curRow.className = 'os-suggest-result';
			}
		}
		var newText;
		if( next >= 0 ) {
			var nextRow = document.getElementById( r.resultTable + next );
			if( nextRow != null ) {
				nextRow.className = os_HighlightClass();
			}
			newText = r.results[next];
		} else {
			newText = r.original;
		}

		// adjust the scrollbar if any
		if( r.containerCount < r.resultCount ) {
			var c = document.getElementById( r.container );
			var vStart = c.scrollTop / r.containerRow;
			var vEnd = vStart + r.containerCount;
			if( next < vStart ) {
				c.scrollTop = next * r.containerRow;
			} else if( next >= vEnd ) {
				c.scrollTop = ( next - r.containerCount + 1 ) * r.containerRow;
			}
		}

		// update the contents of the search box
		if( updateSearchBox ) {
			os_updateSearchQuery( r, newText );
		}
	},
	'os_HighlightClass': function() {
		var match = navigator.userAgent.match(/AppleWebKit\/(\d+)/);
		if ( match ) {
			var webKitVersion = parseInt( match[1] );
			if ( webKitVersion < 523 ) {
				// CSS system highlight colors broken on old Safari
				// https://bugs.webkit.org/show_bug.cgi?id=6129
				// Safari 3.0.4, 3.1 known ok
				return 'os-suggest-result-hl-webkit';
			}
		}
		return 'os-suggest-result-hl';
	},
	'os_updateSearchQuery': function( r, newText ) {
		document.getElementById( r.searchbox ).value = newText;
		r.query = newText;
	},
	
	/* Mouse Event Functions */
	
	/**
	 * Mouse over the container
	 */
	'os_eventMouseover': function( srcId, e ) {
		var targ = os_getTarget( e );
		var r = os_map[srcId];
		if( r == null || !os_mouse_moved ) {
			return; // not our event
		}
		var num = os_getNumberSuffix( targ.id );
		if( num >= 0 ) {
			os_changeHighlight( r, r.selected, num, false );
		}
	},
	/**
	 * Get row where the event occured (from its id)
	 */
	'os_getNumberSuffix': function( id ) {
		var num = id.substring( id.length - 2 );
		if( !( num.charAt( 0 ) >= '0' && num.charAt( 0 ) <= '9' ) ) {
			num = num.substring( 1 );
		}
		if( os_isNumber( num ) ) {
			return parseInt( num );
		} else {
			return -1;
		}
	},
	/**
	 * Save mouse move as last action
	 */
	'os_eventMousemove': function( srcId, e ) {
		os_mouse_moved = true;
	},
	/**
	 * Mouse button held down, register possible click
	 */
	'os_eventMousedown': function( srcId, e ) {
		var targ = os_getTarget( e );
		var r = os_map[srcId];
		if( r == null ) {
			return; // not our event
		}
		var num = os_getNumberSuffix( targ.id );

		os_mouse_pressed = true;
		if( num >= 0 ) {
			os_mouse_num = num;
			// os_updateSearchQuery( r, r.results[num] );
		}
		// keep the focus on the search field
		document.getElementById( r.searchbox ).focus();

		return false; // prevents selection
	},
	/**
	 * Mouse button released, check for click on some row
	 */
	'os_eventMouseup': function( srcId, e ) {
		var targ = os_getTarget( e );
		var r = os_map[srcId];
		if( r == null ) {
			return; // not our event
		}
		var num = os_getNumberSuffix( targ.id );

		if( num >= 0 && os_mouse_num == num ) {
			os_updateSearchQuery( r, r.results[num] );
			os_hideResults( r );
			document.getElementById( r.searchform ).submit();
		}
		os_mouse_pressed = false;
		// keep the focus on the search field
		document.getElementById( r.searchbox ).focus();
	},
	/**
	 * Return the span element that contains the toggle link (dead code?)
	 */
	'os_createToggle': function( r, className ) {
		var t = document.createElement( 'span' );
		t.className = className;
		t.setAttribute( 'id', r.toggle );
		var link = document.createElement( 'a' );
		link.setAttribute( 'href', 'javascript:void(0);' );
		link.onclick = function() { os_toggle( r.searchbox, r.searchform ); };
		var msg = document.createTextNode( wgMWSuggestMessages[0] );
		link.appendChild( msg );
		t.appendChild( link );
		return t;
	},
	/**
	 * Call when user clicks on some of the toggle links (dead code?)
	 */
	'os_toggle': function( inputId, formName ) {
		r = os_map[inputId];
		var msg = '';
		if( r == null ) {
			os_enableSuggestionsOn( inputId, formName );
			r = os_map[inputId];
			msg = wgMWSuggestMessages[0];
		} else{
			os_disableSuggestionsOn( inputId, formName );
			msg = wgMWSuggestMessages[1];
		}
		// change message
		var link = document.getElementById( r.toggle ).firstChild;
		link.replaceChild( document.createTextNode( msg ), link.firstChild );
	}
} );

/* Initialization */

$( document ).ready( function() {
	mw.legacy.os_MWSuggestInit();
} );

} )( jQuery, MediaWiki );