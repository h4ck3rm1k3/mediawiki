/**
 * Plugin that automatically truncates the plain text contents of an element and adds an ellipsis 
 */
( function( $ ) {

$.fn.autoEllipse = function( options ) {
	$(this).each( function() {
		options = $.extend( {
			'position': 'center',
			'tooltip': false
		}, options );
		var text = $(this).text();
		var $text = $( '<span />' ).text( text ).css( 'whiteSpace', 'nowrap' );
		$(this).empty().append( $text );
		if ( $text.outerWidth() > $(this).innerWidth() ) {
			switch ( options.position ) {
				case 'right':
					var l = text.length;
					while ( $text.outerWidth() > $(this).innerWidth() && l > 0 ) {
						$text.text( text.substr( 0, l ) + '...' );
						l--;
					}
					break;
				case 'center':
					var i = [Math.round( text.length / 2 ), Math.round( text.length / 2 )];
					var side = 1; // Begin with making the end shorter
					while ( $text.outerWidth() > ( $(this).innerWidth() ) && i[0] > 0 ) {
						$text.text( text.substr( 0, i[0] ) + '...' + text.substr( i[1] ) );
						// Alternate between trimming the end and begining
						if ( side == 0 ) {
							// Make the begining shorter
							i[0]--;
							side = 1;
						} else {
							// Make the end shorter
							i[1]++;
							side = 0;
						}
					}
					break;
				case 'left':
					var r = 0;
					while ( $text.outerWidth() > $(this).innerWidth() && r < text.length ) {
						$text.text( '...' + text.substr( r ) );
						r++;
					}
					break;
			}
			if ( options.tooltip )
				$text.attr( 'title', text );
		}
	} );
};

} )( jQuery );