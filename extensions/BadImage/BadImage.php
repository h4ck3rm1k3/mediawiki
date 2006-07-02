<?php

/**
 * Extension to extend the bad image list capabilities of MediaWiki
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Rob Church <robchur@gmail.com>
 * @copyright © 2006 Rob Church
 * @licence Copyright holder allows use of the code for any purpose
 */

if( defined( 'MEDIAWIKI' ) ) {

	global $wgAutoloadClasses, $wgSpecialPages, $wgHooks;
	require_once( dirname( __FILE__ ) . '/BadImage.i18n.php' );
	
	$wgAutoloadClasses['BadImageList'] = dirname( __FILE__ ). '/BadImage.class.php';
	$wgAutoloadClasses['BadImageManipulator'] = dirname( __FILE__ ) . '/BadImage.page.php';

	$wgSpecialPages['Badimages'] = 'BadImageManipulator';
	$wgExtensionCredits['other'][] = array( 'name' => 'Bad Image List', 'author' => 'Rob Church' );
	$wgExtensionFunctions[] = 'efBadImageSetup';
	
	function efBadImageSetup() {
		global $wgMessageCache, $wgHooks;
		$wgHooks['BadImage'][] = 'efBadImage';
		$wgMessageCache->addMessages( efBadImageMessages() );
	}
	
	function efBadImage( $image, &$bad ) {
		if( BadImageList::check( $image ) ) {
			$bad = true;
			return false;
		} else {
			return true;
		}
	}
	
} else {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

?>