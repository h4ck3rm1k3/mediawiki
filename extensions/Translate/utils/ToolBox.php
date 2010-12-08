<?php
/**
 * Classes for adding extension specific toolbox menu items.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Siebrand Mazeland, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Adds extension specific context aware toolbox menu items.
 */
class TranslateToolbox {
	/**
	 * Adds link in toolbox to Special:Prefixindex to show all other
	 * available translations for a message. Only shown when it
	 * actually is a translatable/translated message.
	 */
	static function toolboxAllTranslations( &$skin ) {
		global $wgTitle, $wgTranslateMessageNamespaces;

		$ns = $wgTitle->getNamespace();
		if ( !in_array( $ns, $wgTranslateMessageNamespaces ) ) {
			return true;
		}

		$inMessageGroup = TranslateUtils::messageKeyToGroup( $wgTitle->getNamespace(), $wgTitle->getBaseText() );

		if ( $inMessageGroup ) {
			// Add a slash at the end, to not have basename in the result of Special:Prefixindex
			$message = $wgTitle->getNsText() . ":" . $wgTitle->getBaseText();
			$desc = wfMsg( 'translate-sidebar-alltrans' );
			$url = htmlspecialchars( SpecialPage::getTitleFor( 'Translations' )->getLocalURL( 'message=' . $message ) );

			// Add the actual toolbox entry.
			// Add newlines and tabs for nicer HTML output.
			echo( "\n\t\t\t\t<li id=\"t-alltrans\"><a href=\"$url\">$desc</a></li>\n" );
		}

		return true;
	}
}
