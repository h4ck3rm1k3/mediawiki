/* adds firefogg support. 
* autodetects: new upload api or old http POST.  
 */

loadGM({ 
	'fogg-select_file'			: 'Select File', 
	'fogg-select_new_file'		: 'Select New File',
	'fogg-save_local_file'		: 'Save Ogg',
	'fogg-check_for_fogg'		: 'Checking for Firefogg <blink>...</blink>',
	'fogg-installed'			: 'Firefogg is Installed',
	'fogg-please_install'		: 'You don\'t have firefogg, please <a href="$1">install firefogg</a>',
	'fogg-use_latest_fox'		: 'You need a <a href="http://www.mozilla.com/en-US/firefox/all-beta.html">Firefox 3.5</a> to use Firefogg',
	'passthrough_mode'			: 'Your selected file is already ogg or not a video file',
	 
});

var firefogg_install_links =  {
	'macosx':	'http://firefogg.org/macosx/Firefogg.xpi',
	'win32'	:	'http://firefogg.org/win32/Firefogg.xpi',
	'linux' :	'http://firefogg.org/linux/Firefogg.xpi'
};

var default_firefogg_options = {
	'upload_done_action':'redirect',
	'fogg_enabled':false,
	'api_url':null,
	'passthrough': false,
	'encoder_interface': false,
	
	//callbacks:
	'new_source_cb': false, //called on source name update passes along source name
	
	//target control container or form (can't be left null)
	'selector'			: '',
	
	'form_rewrite'		: false,//if not rewriting a form we are encoding local. 
	
	
	//taget buttons: 
	'target_btn_select_file'    : false,
	'target_btn_select_new_file': false,	
	'target_input_file_name'	: false,
	'target_btn_save_local_file': false,	
	
	
	//target install descriptions (visability will be set based ) 
	'target_check_for_fogg'		: false,
	'target_installed'	: false,
	'target_please_install'	: false,
	'target_use_latest_fox': false,
	//status: 
	'target_passthrough_mode':false
}	


var mvFirefogg = function(initObj){
	return this.init( initObj );
}
mvFirefogg.prototype = { //extends mvBaseUploadInterface

	min_firefogg_version : '0.9.6',
	fogg_enabled : false, 		//if firefogg is enabled or not. 	
	encoder_settings:{			//@@todo allow server to set this 
		'maxSize': 400, 
		'videoBitrate': 400,
		'noUpscaling':true
	},
	sourceFileInfo:{},
	ogg_extensions: ['ogg', 'ogv', 'oga'],
	video_extensions: ['avi', 'mov', 'mp4', 'mp2', 'mpeg', 'mpeg2', 'mpeg4', 'dv', 'wmv'],
	
	passthrough: false,

	init: function( iObj ){
		if(!iObj)
			iObj = {};
		//inherit iObj properties:
		for(var i in default_firefogg_options){
			if(iObj[i]){
				this[i] = iObj[i];
			}else{
				this[i] = default_firefogg_options[i];
			}
		}
		var myBUI = new mvBaseUploadInterface( iObj );
		//standard extends code: 
		for(var i in myBUI){			
			if(this[i]){
				this['pe_'+ i] = myBUI[i];
			}else{
				this[i] =  myBUI[i];
			}
		}
		if(!this.selector){
			js_log('Error: firefogg: missing selector ');
		}	
	},
	doRewrite:function( callback ){
		var _this = this;
		$j(this.selector).each(function(){				
			if( this.tagName.toLowerCase() == 'input' ){					
				_this.form_rewrite = true;					
			}
		});		
		//check if we are rewriting an input or a form:
		if( this.form_rewrite ){
			this.setupForm();
		}else{
			this.doControlHTML();	
			this.doControlBindings();	
		}
		
		//doRewrite is done: 
		if(callback)
			callback();
	},
	doControlHTML: function(){
		var _this = this;		
		var out = '';		
		$j.each(default_firefogg_options, function(target, na){
			if(target.substring(0, 6)=='target'){
				//check for the target if missing add to the output: 
				if( _this[target] === false){					
					out+= _this.getTargetHtml(target);
				}
				//update the target selector 
				_this[target] = _this.selector + ' .' + target;
			}
		});
		//output the html
		$j( this.selector ).html( out ).show();				
	},
	getTargetHtml:function(target){
		if( target.substr(7,3)=='btn'){
			return '<input class="' + target + '" type="button" value="' + gM( 'fogg-' + target.substring(11)) + '"/> ';
		}else if(target.substr(7,5)=='input'){
			return '<input class="' + target + '" type="text" value="' + gM( 'fogg-' + target.substring(11)) + '"/> ';
		}else{					
			return '<div class="' + target + '">'+ gM('fogg-'+ target.substring(7)) + '</div> ';
		}
	},
	doControlBindings: function(){
		var _this = this;			
		
		//hide all targets:
		var hide_target_list='';
		var coma='';
		$j.each(default_firefogg_options, function(target, na){		
			if(target.substring(0, 6)=='target'){
				hide_target_list+=coma + _this[target];
				coma=',';
			}			
		});	
		$j( hide_target_list ).hide();						
				
		//hide all but check-for-fogg
		//check for firefogg
		if( _this.firefoggCheck() ){
			//show select file: 
			$j(this.target_btn_select_file).click(function(){
				_this.select_fogg();
			}).show().attr('disabled', false);
		}else{
			var os_link = false;
			if(navigator.oscpu){
				if(navigator.oscpu.search('Linux') >= 0)
	            	os_link = firefogg_install_links['linux'];
	            else if(navigator.oscpu.search('Mac') >= 0)
	              	os_link = firefogg_install_links['macosx'];
	            else if(navigator.oscpu.search('Win') >= 0)
	              	os_link = firefogg_install_links['win32'];
			}			
			$j(_this.target_please_install).html( gM('fogg-please_install',os_link )).show();			
		}
		//setup the target save local file bindins: 
		$j( _this.target_btn_save_local_file ).click(function(){
			//update the output target
			if(_this.fogg){
				//if(_this.fogg.saveVideoAs()){
				_this.doEncode();
				//}
			}
		})
	},
	firefoggCheck:function(){
		var _this = this;			
		if(typeof(Firefogg) != 'undefined' && Firefogg().version >= '0.9.6'){						
			_this.fogg = new Firefogg();	
			return true;
		}else{						
			return false;
		}
	},
	//assume input target
	setupForm: function(){		
		js_log('firefogg::setupForm::');
				
		//call the parent form setup 
		this.pe_setupForm();
		<input type="file" size="60" id="wpUploadFile" name="wpUploadFile" tabindex="1"/>
		//change the file browser to type text:
		$j(this.selector).replaceWith('<input type="text" ' +
											'size="' + $j(this.selector).attr('size') + '" ' +
											'id="'   + $j(this.selector).attr('id') + '" ' +
											'name="' + $j(this.selector).attr("name") + '" ' + 
											'tabindex="' + $j(this.selector).attr('tabindex') + '" '+
											'class="' + $j(this.selector).attr('class') + '" ' +											 
										'>');			
		
		this.target_input_file_name = this.selector;
		
		$j(this.selector).after(
			this.getTargetHtml('target_btn_select_file') 
		);
		//check for the other inline status indicator targets: 
		
		//update the bindings: 
		this.doControlBindings();
	},
	getEditForm:function(){
		return $j(this.selector).parents().find("form").get(0);
	},
	select_fogg:function(){			
		var _this = this;
		if( _this.fogg.selectVideo() ) {
			
			var videoSelectReady= function(){
				js_log('videoSelectReady');
				//if not already hidden hide select file and show "select new": 
				$j(_this.target_btn_select_file).hide();
				//show and setup binding for new file: 
				$j(_this.target_btn_select_new_file).show().click(function(){
					_this.select_fogg();
				});
				//update if we are in passthrough mode or going to encode					
				if( _this.fogg.sourceInfo && _this.fogg.sourceFilename ){									
					//update the source status
					_this.sourceFileInfo = JSON.parse( _this.fogg.sourceInfo) ;
											
					//now setup encoder settings based source type:
					_this.autoEncoderSettings();					
					
					//if set to passthough update the interface:
					if(_this.encoder_settings['passthrough']==true){
						$j(_this.target_passthrough_mode).show();
					}else{						
						//if set to encoder expose the encode button: 
						if( !_this.form_rewrite ){
							$j(_this.target_btn_save_local_file).show();
						}
					}
					//~otherwise the encoding will be triggered by the form~
					
					//do source name update callback: 					
					$j(_this.target_input_file_name).val(_this.fogg.sourceFilename).show();
					
					if(_this.new_source_cb){
						new_source_cb( _this.fogg.sourceFilename );
					}
				}
			}
			//wait 100ms to get video info: 
			setTimeout(videoSelectReady, 200);															
		}
	},
	//simple auto encoder settings just enable passthough if file is not video or > 480 pixles tall 
	autoEncoderSettings:function(){		
		var _this = this;
		//grab the extension:
		var sf = _this.fogg.sourceFilename;						
		var ext = '';
		if(	sf.lastIndexOf('.') != -1){
			ext = sf.substring( sf.lastIndexOf('.')+1 ).toLowerCase();
		}
		//ogg video or audio
		if( $j.inArray(ext, _this.ogg_extensions) > -1 ){		
			//in the default case passthrough	
			_this.encoder_settings['passthrough'] = true;
		}else if( $j.inArray(ext, _this.video_extensions) > -1 ){
			//we are going to run the encoder			
		}else{		
			_this.encoder_settings['passthrough']  = true;
		}	
	},
	getProgressTitle:function(){
		//return the parent if we don't have fogg turned on: 
		if(! this.fogg_enabled )
			return this.pe_getProgressTitle();
			
		return gM('upload-transcode-in-progress');
	},	
	doUploadSwitch:function(){				
		var _this = this;
		//make sure firefogg is enabled otherwise do parent UploadSwich:		
		if( ! this.fogg_enabled )
			return _this.pe_doUploadSwitch();
		
		//check what mode to use firefogg in: 
		if( _this.upload_mode == 'post' ){
			_this.doEncode();
		}else if( _this.upload_mode == 'api' && _this.chunks_supported){ //if api mode and chunks supported do chunkUpload
			_this.doChunkUpload();
		}else{
			js_error( 'Error: unrecongized upload mode: ' + _this.upload_mode );
		}		
	},
	//doChunkUpload does both uploading and encoding at the same time and uploads one meg chunks as they are ready
	doChunkUpload : function(){
		var _this = this;				
		
		if( ! _this.api_url )
			return js_error( 'Error: can\'t autodetect mode without api url' );				
						
		//extension should already be ogg but since its user editable,
		//check again
		//we are transcoding so we know it will be an ogg
		//(should not be done for passthrough mode)
		var sf = _this.formData['wpDestFile'];
		var ext = '';
		if(	sf.lastIndexOf('.') != -1){
			ext = sf.substring( sf.lastIndexOf('.') ).toLowerCase();
		}
		if(!_this.passthrough && $j.inArray(ext.substr(1), _this.ogg_extensions) == -1 ){		
			var extreg = new RegExp(ext + '$', 'i');
			_this.formData['wpDestFile'] = sf.replace(extreg, '.ogg');
		}
		//add chunk response hook to build the resultURL when uploading chunks		
		
		//build the api url: 
		var aReq ={
			'action'	: 'upload',
			'format'	: 'json',
			'filename'	: _this.formData['wpDestFile'],
			'comment'	: _this.formData['wpUploadDescription'],
			'enablechunks': true
		};
		
		if( _this.formData['wpWatchthis'] )
			aReq['watch'] =  _this.formData['wpWatchthis'];
		
		if(  _this.formData['wpIgnoreWarning'] )
			aReq['ignorewarnings'] = _this.formData['wpIgnoreWarning'];
		
		js_log('do fogg upload call: '+ _this.api_url + ' :: ' + JSON.stringify( aReq ) );			
					
		_this.fogg.upload( JSON.stringify( _this.encoder_settings ),  _this.api_url ,  JSON.stringify( aReq ) );		
			
		//update upload status:						
		_this.doUploadStatus();
	},
	//doEncode and monitor progress:
	doEncode : function(){	
		var _this = this;
		_this.dispProgressOverlay();				
		js_log('doEncode: with: ' +  JSON.stringify( _this.encoder_settings ) );
		_this.fogg.encode( JSON.stringify( _this.encoder_settings ) );		  	
		
		
		 //show transcode status:
		$j('#up-status-state').html( gM('upload-transcoded-status') );
		
		//setup a local function for timed callback:
		var encodingStatus = function() {
			var status = _this.fogg.status();
			
			//update progress bar
			_this.updateProgress( _this.fogg.progress() );			
			
			//loop to get new status if still encoding
			if( _this.fogg.state == 'encoding' ) {
				setTimeout(encodingStatus, 500);
			}else if ( _this.fogg.state == 'encoding done' ) { //encoding done, state can also be 'encoding failed																
				_this.encodeDone();
			}else if(_this.fogg.state == 'encoding fail'){
				//@@todo error handling: 
				js_error('encoding failed');
			}
		}
		encodingStatus();		  			
	},	
	encodeDone:function(){
		var _this = this;
		js_log('::encodeDone::');
		//send to the post url: 				
		if( _this.form_rewrite ){
			js_log('done with encoding do upload:');					
			// ignore warnings & set source type 
			//_this.formData[ 'wpIgnoreWarning' ]='true';
			_this.formData[ 'wpSourceType' ]= 'file';		
			_this.formData[ 'action' ] 		= 'submit';
		
			_this.fogg.post( _this.editForm.action, 'wpUploadFile', JSON.stringify( _this.formData ) );				
			//update upload status:						
			_this.doUploadStatus();
		}else{
			js_log('done with encode (no upload cuz we don\'t have a target)');
		}
	},
	doUploadStatus:function() {	
		var _this = this;
		$j('#up-status-state').html( gM('uploaded-status')  );
	    
		_this.oldResponseText = '';
		//setup a local function for timed callback: 				
		var uploadStatus = function(){
			//get the response text: 
			var response_text =  _this.fogg.responseText;
			if( !response_text){
	       		try{
	       			var pstatus = JSON.parse( _this.fogg.uploadstatus() );
	       			response_text = pstatus["responseText"];
	       		}catch(e){
	       			js_log("could not parse uploadstatus / could not get responseText");
	       		}
			}
		       		
			if( _this.oldResponseText != response_text){								        					      					        				
				js_log('new result text:' + response_text);
				_this.oldResponseText = response_text;				
				//try and pare the response see if we need to take action:
				   
			}		
		    //update progress bar
		    _this.updateProgress( _this.fogg.progress() );
		    		    
		    //loop to get new status if still uploading (could also be encoding if we are in chunk upload mode) 
		    if( _this.fogg.state == 'encoding' || _this.fogg.state == 'uploading') {
				setTimeout(uploadStatus, 100);
			}
		    //check upload state
		    else if( _this.fogg.state == 'upload done' ||  _this.fogg.state == 'done' ) {	
		       	js_log( 'firefogg:upload done: '); 			        		       			       			       	       		       			       	
		       	//if in "post" upload mode read the html response (should be depricated): 
		       	if( _this.upload_mode == 'post' ) {		       		
		       		//js_log( 'done upload response is: ' + cat["responseText"] );
		       		_this.procPageResponse( response_text );
		       			
		       	}else if( _this.upload_mode == 'api'){		       				       	
		       		if( _this.fogg.resultUrl ){		       		
		       			//should have an json result:
		       			_this.updateUploadDone( _this.fogg.resultUrl );	
		       		}else{
		       			//done state with error? ..not really possible given how firefogg works
		       			js_log(" upload done, in chunks mode, but no resultUrl!");
		       		}		       				       				       				       			       	
		       	}													
			}else{  
				//upload error: 
				alert('firefogg upload error: ' + _this.fogg.state );		
	       }
	   }
	   uploadStatus();
	},	
	/*
	procPageResponse should be faded out soon.. its all very fragile to read the html output and guess at stuff*/
	procPageResponse:function( result_page ){
		js_log('f:procPageResponse');
		var sstring = 'var wgTitle = "' + this.formData['wpDestFile'].replace('_',' ');		
		var result_txt = gM('mv_upload_done', 
							wgArticlePath.replace(/\$1/, 'File:' + this.formData['wpDestFile'] ) );						
		//set the error text in case we dont' get far along in processing the response 
		$j( '#dlbox-centered' ).html( gM('mv_upload_completed') + result_txt );
												
		if( result_page && result_page.toLowerCase().indexOf( sstring.toLowerCase() ) != -1){	
			js_log( 'upload done got redirect found: ' + sstring + ' r:' + _this.upload_done_action );										
			if( _this.upload_done_action == 'redirect' ){
			$j( '#dlbox-centered' ).html( '<h3>Upload Completed:</h3>' + result_txt + '<br>' + form_txt);
				window.location = wgArticlePath.replace( /\$1/, 'File:' + formData['wpDestFile'] );
			}else{
				//check if the add_done_action is a callback:
				if( typeof _this.upload_done_action == 'function' )
					_this.upload_done_action();
			}									
		}else{								
			//js_log( 'upload page error: did not find: ' +sstring + ' in ' + "\n" + result_page );					
			var form_txt = '';		
			if( !result_page ){
				//@@todo fix this: 
				//the mediaWiki upload system does not have an API so we can\'t read errors							
			}else{
				var res = grabWikiFormError( result_page );
							
				if(res.error_txt)
					result_txt = res.error_txt;
					
				if(res.form_txt)
					form_txt = res.form_txt;
			}		
			js_log( 'error text is: ' + result_txt );		
			$j( '#dlbox-centered' ).html( '<h3>' + gM('mv_upload_completed') + '</h3>' + result_txt + '<br>' + form_txt);
		}
	}
}
