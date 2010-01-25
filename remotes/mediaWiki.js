/*
 * This file exposes some of the functionality of mwEmbed to wikis
 * that do not yet have js2 enabled
 */
var urlparts = getRemoteEmbedPath();
var mwEmbedHostPath = urlparts[0];
var mwRemoteVersion = 'r77';
var mwUseScriptLoader = true;

// Setup up request Params: 
var reqParts = urlparts[1].substring( 1 ).split( '&' );
var mwReqParam = { };
for ( var i = 0; i < reqParts.length; i++ ) {
	var p = reqParts[i].split( '=' );
	if ( p.length == 2 )
		mwReqParam[ p[0] ] = p[1];
}

// Use wikibits onLoad hook: ( since we don't have js2 / mw object loaded ) 
addOnloadHook( function() {	
	// Only do rewrites if mwEmbed / js2 is "off"
	if ( typeof mwEmbed_VERSION == 'undefined' ) {		
		doPageSpecificRewrite();
	}
} );

/**
* Page specific rewrites for mediaWiki
*/
// Deal with multiple doPageSpecificRewrite 
if( !ranRewrites){
	var ranRewrites = 'none';
}
function doPageSpecificRewrite() {
	if( ranRewrites != 'none')
		return ;	
	ranRewrites = 'done';
	// Add media wizard
	if ( wgAction == 'edit' || wgAction == 'submit' ) {				
		loadMwEmbed( [ 
			'mw.RemoteSearchDriver', 
			'$j.fn.textSelection', 
			'$j.ui', 
			'$j.ui.sortable' 
		], function() {
			mw.load( mwEmbedHostPath + '/editPage.js?' + mwGetReqArgs() );
		} );
	}
	
	// Timed text display:
	if ( wgPageName.indexOf( "TimedText" ) === 0 ) {		
		if( wgAction == 'view' ){
			var orgBody = mwSetPageToLoading();
			//load the "player" ( includes call to  loadMwEmbed )
			mwLoadPlayer(function(){
				// Now load MediaWiki TimedText Remote: 			
				mw.load( 'RemoteMwTimedText',function(){
					//Setup the remote configuration
					var myRemote = new RemoteMwTimedText( {
						'action': wgAction,
						'title' : wgTitle,
						'target': '#bodyContent',
						'orgBody': orgBody
					});	
					// Update the UI
					myRemote.updateUI();
				} );
			} );		
		}			
	}
	
	// Remote Sequencer
	if( wgPageName.indexOf( "Sequence" ) === 0 ){	
		console.log( 'spl: ' + typeof mwSetPageToLoading );	
		// If on a view page set content to "loading" 
		mwSetPageToLoading();
		// Loading with loadMwEmbed not so big a deal since "sequencer is huge
		loadMwEmbed( function(){
			mw.load( 'Sequencer', function(){
				mw.load( 'RemoteMwSequencer' );
			} );
		} );	
	}
	
	
	// Firefogg integration
	if ( wgPageName == "Special:Upload" ) {			
		loadMwEmbed([ 
				'mw.BaseUploadInterface', 
				'mw.Firefogg', 
				'$j.ui',
				'$j.ui.progressbar', 
				'$j.ui.dialog', 
				'$j.ui.draggable' 
			], function() {
				mw.load( mwEmbedHostPath + '/uploadPage.js?' + mwGetReqArgs() );
			} 
		);
	}
	
	// Special api proxy page
	if ( wgPageName == 'MediaWiki:ApiProxy' ) {
		var wgEnableIframeApiProxy = true;
		loadMwEmbed( [ 'mw.proxy' ], function() {
			mw.load( mwEmbedHostPath + '/apiProxyPage.js?' + mwGetReqArgs() );
		} );
	}
	
	// OggHandler rewrite for view pages:
	var vidIdList = [];
	var divs = document.getElementsByTagName( 'div' );
	for ( var i = 0; i < divs.length; i++ ) {
		if ( divs[i].id && divs[i].id.substring( 0, 11 ) == 'ogg_player_' ) {			
			vidIdList.push( divs[i].getAttribute( "id" ) );
		}
	}	
	if ( vidIdList.length > 0 ) {
		// Reverse order the array so videos at the "top" get swapped first:
		vidIdList = vidIdList.reverse();
		mwLoadPlayer(function(){
			//Load the "EmbedPlayer" module: 
			// All the actual code was requested in our single script-loader call 
			//  but the "load" request applies the setup.
			mw.load( 'EmbedPlayer', function() {
				// Do utility rewrite of OggHandler content:
				rewrite_for_OggHandler( vidIdList );
			} );
		} );
	}
}
/*
* Sets the mediaWiki content to "loading" 
*/
function mwSetPageToLoading(){
	importStylesheetURI( mwEmbedHostPath + '/mwEmbed/skins/mvpcf/styles.css?' + mwGetReqArgs() );
	var body = document.getElementById('bodyContent');
	var oldBodyHTML = body.innerHTML;
	body.innerHTML = '<div class="loading_spinner"></div>';
	return oldBodyHTML;
}
/**
* Similar to the player loader in /modules/embedPlayer/loader.js
* ( front-loaded to avoid extra requests )
*/
function mwLoadPlayer( callback ){
	//Load the video style sheets:
	importStylesheetURI( mwEmbedHostPath + '/mwEmbed/skins/mvpcf/styles.css?' + mwGetReqArgs() );
	importStylesheetURI( mwEmbedHostPath + '/mwEmbed/skins/kskin/EmbedPlayer.css?' + mwGetReqArgs() );
			
	var jsSetVideo = [ 		
		'mw.EmbedPlayer', 
		'$j.ui', 
		'ctrlBuilder', 
		'$j.cookie', 
		'$j.ui.slider', 
		'kskinConfig',
		'$j.fn.menu',
		'mw.TimedText' 
	];		
	// Quick sniff use java if IE and native if firefox 
	// ( other browsers will run detect and get on-demand ) 	
	if (navigator.userAgent.indexOf("MSIE") != -1)
		jsSetVideo.push( 'javaEmbed' );
		
	if ( navigator.userAgent &&  navigator.userAgent.indexOf("Firefox") != -1 )
		jsSetVideo.push( 'nativeEmbed' );

	loadMwEmbed( jsSetVideo, function() {
		callback();
	});
}

/**
* This will be depreciated when we update to OggHandler
* @param {Object} vidIdList List of video ids to process
*/
function rewrite_for_OggHandler( vidIdList ) {
	function procVidId( vidId ) {		
		// Don't process empty vids
		if ( !vidId )		
			return ;
			
		mw.log( 'vidIdList on: ' + vidId + ' id:' +  $j('#' + vidId ).length + ' length: ' + vidIdList.length + ' left in the set: ' + vidIdList );
		
		tag_type = 'video';	
				
		// Check type:
		var pwidth = $j( '#' + vidId ).width();
		var $pimg = $j( '#' + vidId + ' img:first' );		
		if(  $pimg.attr('src') && $pimg.attr('src').split('/').pop() == 'play.png'){
			tag_type = 'audio';
			poster_attr = '';		
			pheight = 0;
		}else{
			var poster_attr = 'poster = "' + $pimg.attr( 'src' ) + '" ';			
			var pheight = $pimg.attr( 'height' );				
		}
		
		// Parsed values:
		var src = '';
		var duration_attr = '';
		var rewriteHTML = $j( '#' + vidId ).html();
		
		if( rewriteHTML == ''){
			mw.log( "Error: empty rewrite html" );
			return ;
		}else{
			//mw.log(" rewrite: " + rewriteHTML + "\n of type: " + typeof rewriteHTML);
		}		
		var re = new RegExp( /videoUrl(&quot;:?\s*)*([^&]*)/ );
		src = re.exec( rewriteHTML )[2];

		var apiTitleKey = src.split( '/' );
		apiTitleKey = unescape( apiTitleKey[ apiTitleKey.length - 1 ] );			

		var re = new RegExp( /length(&quot;:?\s*)*([^,]*)/ );
		var dv = re.exec( rewriteHTML )[2];
		if ( dv ) {
			duration_attr = 'durationHint="' + dv + '" ';
		}

		var re = new RegExp( /offset(&quot;:?\s*)*([^,&]*)/ );
		offset = re.exec( rewriteHTML )[2];
		var offset_attr = offset ? 'startOffset="' + offset + '"' : '';

		if ( src ) {
			var html_out = '';
			
			var common_attr = ' id="mwe_' + vidId + '" ' +
					'apiTitleKey="' + apiTitleKey + '" ' +
					'src="' + src + '" ' +
					duration_attr +
					offset_attr + ' ' +
					'class="kskin" ';
								
			if ( tag_type == 'audio' ) {
				if( pwidth < 250 ){
					pwidth = 250;
				}
				html_out = '<audio' + common_attr + ' ' +
						'style="width:' + pwidth + 'px;height:0px;"></audio>';
			} else {
				html_out = '<video' + common_attr +
				poster_attr + ' ' +
				'style="width:' + pwidth + 'px;height:' + pheight + 'px;">' +
				'</video>';
			}
			// Set the video tag inner html and update the height
			$j( '#' + vidId ).after( html_out ).remove();				

			// Do the actual rewrite 			
			mw.log("rewrite: "+ vidId );	
			$j( '#mwe_' + vidId ).embedPlayer();
			//issue an async request to rewrite the next clip
			if ( vidIdList.length != 0 ) {					
				setTimeout( function() {
					procVidId( vidIdList.pop() )
				}, 1 );
			}

		}		
	};
	// Process current top item in vidIdList	
	procVidId( vidIdList.pop() );
}

/**
* Get the remote embed Path
*/
function getRemoteEmbedPath() {
	//debugger;
	for ( var i = 0; i < document.getElementsByTagName( 'script' ).length; i++ ) {
		var s = document.getElementsByTagName( 'script' )[i];
		if ( s.src.indexOf( '/mediaWiki.js' ) != - 1 ) {
			var reqStr = '';
			var scriptPath = '';
			if ( s.src.indexOf( '?' ) != - 1 ) {
				reqStr = s.src.substr( s.src.indexOf( '?' ) );
				scriptPath = s.src.substr( 0,  s.src.indexOf( '?' ) ).replace( '/mediaWiki.js', '' );
			} else {
				scriptPath = s.src.replace( '/mediaWiki.js', '' )
			}
			// Use the external_media_wizard path:
			return [scriptPath + '/../..', reqStr];
		}
	}
}

/**
* Get the request arguments
*/ 
function mwGetReqArgs() {
	var rurl = '';
	if ( mwReqParam['debug'] )
		rurl += 'debug=true&';

	if ( mwReqParam['uselang'] )
		rurl += 'uselang=' + mwReqParam['uselang'] + '&';

	if ( mwReqParam['urid'] ) {
		rurl += 'urid=' + mwReqParam['urid'];
	} else {
		// Make sure to use an urid 
		// This way remoteMwEmbed can control version of code being requested
		rurl += 'urid=' + mwRemoteVersion;
	}
	return rurl;
}

/**
* Load the mwEmbed library:
*
* @param {mixed} function or classSet to preload
* 	classSet saves round trips to the server by grabbing things we will likely need in the first request. 
* @param {callback} function callback to be called once mwEmbed is ready
*/
function loadMwEmbed( classSet, callback ) {	
	if( typeof classSet == 'function')
		callback = classSet;
		
	// Inject mwEmbed if needed
	if ( typeof MW_EMBED_VERSION == 'undefined' ) {
		if ( ( mwReqParam['uselang'] || mwReqParam[ 'useloader' ] ) && mwUseScriptLoader ) {
			var rurl = mwEmbedHostPath + '/mwEmbed/jsScriptLoader.php?class=mwEmbed';
			
			// Add jQuery too if we need it: 
			if ( typeof window.jQuery == 'undefined' ) {
				rurl += ',window.jQuery';
			}	
								
			// Add scriptLoader requested classSet
			for( var i=0; i < classSet.length; i++ ){
				var cName =  classSet[i];
				if( !mwCheckObjectPath( cName ) ){
					rurl +=  ',' + cName;
				}
			}
			
			// Add the remaining arguments
			rurl += '&' + mwGetReqArgs();													
			importScriptURI( rurl );
		} else { 
			// Ignore classSet (will be loaded onDemand )
			importScriptURI( mwEmbedHostPath + '/mwEmbed/mwEmbed.js?' + mwGetReqArgs() );
		}
	}
	waitMwEmbedReady( callback );
}

/**
* Waits for mwEmbed to be ready
* @param callback
*/
function waitMwEmbedReady( callback ) {
	if( ! mwCheckObjectPath( 'mw.version' ) ){
		setTimeout( function() {
			waitMwEmbedReady( callback );
		}, 25 );
	} else {				
		// Make sure mwEmbed is "setup" by using the addOnLoadHook: 
		mw.ready( function(){			
			callback();
		})
	}
}
/**
* Checks an object path to see if its defined
* @param {String} libVar The objectPath to be checked
*/
function mwCheckObjectPath ( libVar ) {
	if ( !libVar )
		return false;
	var objPath = libVar.split( '.' )
	var cur_path = '';
	for ( var p = 0; p < objPath.length; p++ ) {
		cur_path = ( cur_path == '' ) ? cur_path + objPath[p] : cur_path + '.' + objPath[p];
		eval( 'var ptest = typeof ( ' + cur_path + ' ); ' );
		if ( ptest == 'undefined' ) {
			this.missing_path = cur_path;
			return false;
		}
	}
	this.cur_path = cur_path;
	return true;
};
