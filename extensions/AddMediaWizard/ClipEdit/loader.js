/*
* Clip edit loader:
*/

mw.addResourcePaths( {
	"mw.ClipEdit" : "mw.ClipEdit.js",
	"mw.style.ClipEdit" : "css/mw.style.ClipEdit.css",
	"$j.fn.ColorPicker"	: "colorpicker/js/colorpicker.js",
	"mw.style.colorpicker"	: "colorpicker/css/colorpicker.css",
	
	"$j.Jcrop"			: "Jcrop/js/jquery.Jcrop.js",
	"mw.style.Jcrop"	: "Jcrop/css/jquery.Jcrop.css"
} );


mw.addModuleLoader( 'ClipEdit', function( callback ){	
	mw.load(
		[
		 'mw.ClipEdit',
		 'mw.style.ClipEdit'
        ], 
        function(){
			callback( 'ClipEdit' );
		}
	 );
});
