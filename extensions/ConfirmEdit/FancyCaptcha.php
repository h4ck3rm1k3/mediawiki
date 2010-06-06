<?php
/**
 * Experimental image-based captcha plugin, using images generated by an
 * external tool.
 *
 * Copyright (C) 2005, 2006 Brion Vibber <brion@pobox.com>
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
 *
 * @file
 * @ingroup Extensions
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

global $wgCaptchaDirectory;
$wgCaptchaDirectory = "$wgUploadDirectory/captcha"; // bad default :D

global $wgCaptchaDirectoryLevels;
$wgCaptchaDirectoryLevels = 0; // To break into subdirectories

global $wgCaptchaSecret;
$wgCaptchaSecret = "CHANGE_THIS_SECRET!";

$wgExtensionMessagesFiles['FancyCaptcha'] = dirname( __FILE__ ) . '/FancyCaptcha.i18n.php';
$wgAutoloadClasses['FancyCaptcha'] = dirname( __FILE__ ) . '/FancyCaptcha.class.php';
