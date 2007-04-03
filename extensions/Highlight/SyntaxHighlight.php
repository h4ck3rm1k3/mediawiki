<?php
/**
 * Syntax highlighting extension for MediaWiki 1.5 using GeSHi
 * Copyright (C) 2005 Brion Vibber <brion@pobox.com>
 * http://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

/**
 * @addtogroup Extensions
 * @author Brion Vibber
 *
 * This extension wraps the GeSHi highlighter: http://qbnz.com/highlighter/
 *
 * Unlike the older GeSHi MediaWiki extension floating around, this makes
 * use of the new extension parameter support in MediaWiki 1.5 so it only
 * has to register one tag, <source>.
 *
 * A language is specified like: <source lang="c">void main() {}</source>
 * If you forget, or give an unsupported value, the extension spits out
 * some help text and a list of all supported languages.
 *
 * The extension has been tested with GeSHi 1.0.7 and MediaWiki 1.5 CVS
 * as of 2005-06-22.
 */

if( !defined( 'MEDIAWIKI' ) )
	die();

$wgExtensionFunctions[] = 'syntaxSetup';
$wgExtensionCredits['parserhook']['SyntaxHighlight'] = array(
	'name'          => 'SyntaxHighlight',
	'author'        => 'Brion Vibber',
	'description'   => 'Provides syntax highlighting using [http://qbnz.com/highlighter/ GeSHi Higlighter]',
);
$wgHooks['LoadAllMessages'][] = 'syntaxLoadMessages';

function syntaxSetup() {
	global $wgParser;
	$wgParser->setHook( 'source', 'syntaxHook' );
}

function syntaxLoadMessages() {
	static $loaded = false;
	if ( $loaded ) {
		return;
	}
	global $wgMessageCache;
	require_once( dirname( __FILE__ ) . '/SyntaxHighlight.i18n.php' );
	foreach( efSyntaxHighlightMessages() as $lang => $messages )
		$wgMessageCache->addMessages( $messages, $lang );
}

function syntaxHook( $text, $params = array(), $parser ) {
	if ( !class_exists( 'GeSHi' ) ) {
		require( 'geshi/geshi.php' );
	}	
	syntaxLoadMessages();
	return isset( $params['lang'] )
		? syntaxFormat( trim( $text ), $params, $parser )
		: syntaxHelp();
}

function syntaxFormat( $text, $params, $parser ) {
	$lang = $params['lang'];
	if ( !preg_match( '/^[A-Za-z_0-9-]*$/', $lang ) ) {
		return syntaxHelp( wfMsgHtml( 'syntaxhighlight-err-language' ) );
	}

	$geshi = new GeSHi( $text, $lang );
	if ( $geshi->error == GESHI_ERROR_NO_SUCH_LANG ) {
		return syntaxHelp( wfMsgHtml( 'syntaxhighlight-err-language' ) );
	}

	$geshi->set_encoding( 'UTF-8' );
	$geshi->enable_classes();
	$geshi->set_overall_class( "source source-$lang" );

	if ( isset( $params['line'] ) ) {
		$geshi->enable_line_numbers( GESHI_FANCY_LINE_NUMBERS );
	}
	if ( isset( $params['start'] ) ) {
		$geshi->start_line_numbers_at( $params['start'] );
	}
	// Header type not optional because MW doesn't like <div> mode
	// $geshi->set_header_type( GESHI_HEADER_PRE );

	if ( isset( $params['strict'] ) ) {
		$geshi->enable_strict_mode();
	}

	$out   = $geshi->parse_code();
	$error = $geshi->error();

	if ( $error ) {
		return syntaxHelp( $error );
	} else {
		$geshi->set_overall_class( "source-$lang" );
		$parser->mOutput->addHeadItem( 
			"<style><!--\n" .		
			$geshi->get_stylesheet( false ) .
			"--></style>\n",
			"source-$lang" );
		return $out;
	}
}

/**
 * Return a syntax help message
 * @param string $error HTML error message
 */
function syntaxHelp( $error = false ) {
	return syntaxError( 
		( $error ? "<p>$error</p>" : '' ) . 
		'<p>' . wfMsg( 'syntaxhighlight-specify' ) . ' ' .
		'<samp>&lt;source lang=&quot;html&quot;&gt;...&lt;/source&gt;</samp></p>' .
		'<p>' . wfMsg( 'syntaxhighlight-supported' ) . '</p>' .
		syntaxFormatList( syntaxLanguageList() ) );
}

/**
 * Put a red-bordered div around an HTML message
 * @param string $contents HTML error message
 * @return HTML
 */
function syntaxError( $contents ) {
	return "<div style=\"border:solid red 1px; padding:.5em;\">$contents</div>";
}

function syntaxFormatList( $list ) {
	return empty( $list )
		? wfMsg( 'syntaxhighlight-err-loading' )
		: '<p style="padding:0em 1em;">' .
			implode( ', ', array_map( 'syntaxListItem', $list ) ) .
			'</p>';
}

function syntaxListItem( $item ) {
	return "<samp>" . htmlspecialchars( $item ) . "</samp>";
}

function syntaxLanguageList() {
	$langs = array();
	$langroot = @opendir( GESHI_LANG_ROOT );
	if( $langroot ) {
		while( $item = readdir( $langroot ) ) {
			if( preg_match( '/^(.*)\\.php$/', $item, $matches ) ) {
				$langs[] = $matches[1];
			}
		}
		closedir( $langroot );
	}
	sort( $langs );
	return $langs;
}

?>
