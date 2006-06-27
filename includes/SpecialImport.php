<?php
/**
 * MediaWiki page data importer
 * Copyright (C) 2003,2005 Brion Vibber <brion@pobox.com>
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
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */

/**
 * Constructor
 */
function wfSpecialImport( $page = '' ) {
	global $wgUser, $wgOut, $wgRequest, $wgTitle, $wgImportSources;

	###
#	$wgOut->addWikiText( "Special:Import is not ready for this beta release, sorry." );
#	return;
	###

	if( $wgRequest->wasPosted() && $wgRequest->getVal( 'action' ) == 'submit') {
		$isUpload = false;
		$interwiki = false;
		
		switch( $wgRequest->getVal( "source" ) ) {
		case "upload":
			$isUpload = true;
			if( $wgUser->isAllowed( 'importupload' ) ) {
				$source = ImportStreamSource::newFromUpload( "xmlimport" );
			} else {
				return $wgOut->permissionRequired( 'importupload' );
			}
			break;
		case "interwiki":
			$interwiki = $wgRequest->getVal( 'interwiki' );
			$history = $wgRequest->getCheck( 'interwikiHistory' );
			$source = ImportStreamSource::newFromInterwiki(
				$interwiki,
				$wgRequest->getText( "frompage" ),
				$history );
			break;
		default:
			$source = new WikiError( "Unknown import source type" );
		}

		if( WikiError::isError( $source ) ) {
			$wgOut->addWikiText( wfEscapeWikiText( $source->getMessage() ) );
		} else {
			$wgOut->addWikiText( wfMsg( "importstart" ) );
			
			$importer = new WikiImporter( $source );
			$reporter = new ImportReporter( $importer, $isUpload, $interwiki );
			
			$reporter->open();
			$result = $importer->doImport();
			$reporter->close();
			
			if( WikiError::isError( $result ) ) {
				$wgOut->addWikiText( wfMsg( "importfailed",
					wfEscapeWikiText( $result->getMessage() ) ) );
			} else {
				# Success!
				$wgOut->addWikiText( wfMsg( "importsuccess" ) );
			}
		}
	}

	$action = $wgTitle->escapeLocalUrl( 'action=submit' );

	if( $wgUser->isAllowed( 'importupload' ) ) {
		$wgOut->addWikiText( wfMsg( "importtext" ) );
		$wgOut->addHTML( "
<fieldset>
	<legend>" . wfMsgHtml('upload') . "</legend>
	<form enctype='multipart/form-data' method='post' action=\"$action\">
		<input type='hidden' name='action' value='submit' />
		<input type='hidden' name='source' value='upload' />
		<input type='hidden' name='MAX_FILE_SIZE' value='2000000' />
		<input type='file' name='xmlimport' value='' size='30' />
		<input type='submit' value=\"" . wfMsgHtml( "uploadbtn" ) . "\" />
	</form>
</fieldset>
" );
	} else {
		if( empty( $wgImportSources ) ) {
			$wgOut->addWikiText( wfMsg( 'importnosources' ) );
		}
	}

	if( !empty( $wgImportSources ) ) {
		$wgOut->addHTML( "
<fieldset>
	<legend>" . wfMsgHtml('importinterwiki') . "</legend>
	<form method='post' action=\"$action\">" .
		$wgOut->parse( wfMsg( 'import-interwiki-text' ) ) . "
		<input type='hidden' name='action' value='submit' />
		<input type='hidden' name='source' value='interwiki' />
		<table>
			<tr>
				<td>
					<select name='interwiki'>" );
		foreach( $wgImportSources as $interwiki ) {
			$iw = htmlspecialchars( $interwiki );
			$wgOut->addHTML( "<option value=\"$iw\">$iw</option>\n" );
		}
		$wgOut->addHTML( "
					</select>
				</td>
				<td>" .
					wfInput( 'frompage', 40 ) .
				"</td>
			</tr>
			<tr>
				<td></td>
				<td>" .
					wfCheckLabel( wfMsg( 'import-interwiki-history' ),
						'interwikiHistory', 'interwikiHistory', true ) .
				"</td>
			</tr>
			<tr>
				<td></td>
				<td>" .
					wfSubmitButton( wfMsg( 'import-interwiki-submit' ) ) .
				"</td>
			</tr>
		</table>
	</form>
</fieldset>
" );
	}
}

/**
 * Reporting callback
 */
class ImportReporter {
	function __construct( $importer, $upload, $interwiki ) {
		$importer->setPageOutCallback( array( $this, 'reportPage' ) );
		$this->mPageCount = 0;
		$this->mIsUpload = $upload;
		$this->mInterwiki = $interwiki;
	}
	
	function open() {
		global $wgOut;
		$wgOut->addHtml( "<ul>\n" );
	}
	
	function reportPage( $pageName, $revisionCount ) {
		global $wgOut, $wgUser, $wgLang, $wgContLang;
		
		$skin = $wgUser->getSkin();
		$title = Title::newFromText( $pageName );
		
		$this->mPageCount++;
		
		$localCount = $wgLang->formatNum( $revisionCount );
		$contentCount = $wgContLang->formatNum( $revisionCount );
		
		$wgOut->addHtml( "<li>" . $skin->makeKnownLinkObj( $title ) .
			" " .
			wfMsgHtml( 'import-revision-count', $localCount ) .
			"</li>\n" );
		
		$log = new LogPage( 'import' );
		if( $this->mIsUpload ) {
			$detail = wfMsgForContent( 'import-logentry-upload-detail',
				$contentCount );
			$log->addEntry( 'upload', $title, $detail );
		} else {
			$interwiki = '[[:' . $this->mInterwiki . ':' .
				$title->getPrefixedText() . ']]';
			$detail = wfMsgForContent( 'import-logentry-interwiki-detail',
				$contentCount, $interwiki );
			$log->addEntry( 'interwiki', $title, $detail );
		}
		
		$comment = $detail; // quick
		$dbw = wfGetDB( DB_MASTER );
		$nullRevision = Revision::newNullRevision(
			$dbw, $title->getArticleId(), $comment, true );
		$nullRevId = $nullRevision->insertOn( $dbw );
	}
	
	function close() {
		global $wgOut;
		if( $this->mPageCount == 0 ) {
			$wgOut->addHtml( "<li>" . wfMsgHtml( 'importnopages' ) . "</li>\n" );
		}
		$wgOut->addHtml( "</ul>\n" );
	}
}

/**
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class WikiRevision {
	var $title = NULL;
	var $id = 0;
	var $timestamp = "20010115000000";
	var $user = 0;
	var $user_text = "";
	var $text = "";
	var $comment = "";
	var $minor = false;

	function setTitle( $text ) {
		$this->title = Title::newFromText( $text );
	}

	function setID( $id ) {
		$this->id = $id;
	}

	function setTimestamp( $ts ) {
		# 2003-08-05T18:30:02Z
		$this->timestamp = wfTimestamp( TS_MW, $ts );
	}

	function setUsername( $user ) {
		$this->user_text = $user;
	}

	function setUserIP( $ip ) {
		$this->user_text = $ip;
	}

	function setText( $text ) {
		$this->text = $text;
	}

	function setComment( $text ) {
		$this->comment = $text;
	}

	function setMinor( $minor ) {
		$this->minor = (bool)$minor;
	}

	function getTitle() {
		return $this->title;
	}

	function getID() { 
		return $this->id;
	}

	function getTimestamp() {
		return $this->timestamp;
	}

	function getUser() {
		return $this->user_text;
	}

	function getText() {
		return $this->text;
	}

	function getComment() {
		return $this->comment;
	}

	function getMinor() {
		return $this->minor;
	}

	function importOldRevision() {
		$fname = "WikiImporter::importOldRevision";
		$dbw =& wfGetDB( DB_MASTER );

		# Sneak a single revision into place
		$user = User::newFromName( $this->getUser() );
		if( $user ) {
			$userId = intval( $user->getId() );
			$userText = $user->getName();
		} else {
			$userId = 0;
			$userText = $this->getUser();
		}

		// avoid memory leak...?
		$linkCache =& LinkCache::singleton();
		$linkCache->clear();

		$article = new Article( $this->title );
		$pageId = $article->getId();
		if( $pageId == 0 ) {
			# must create the page...
			$pageId = $article->insertOn( $dbw );
			$created = true;
		} else {
			$created = false;
		}

		# FIXME: Check for exact conflicts
		# FIXME: Use original rev_id optionally
		# FIXME: blah blah blah

		#if( $numrows > 0 ) {
		#	return wfMsg( "importhistoryconflict" );
		#}

		# Insert the row
		$revision = new Revision( array(
			'page'       => $pageId,
			'text'       => $this->getText(),
			'comment'    => $this->getComment(),
			'user'       => $userId,
			'user_text'  => $userText,
			'timestamp'  => $this->timestamp,
			'minor_edit' => $this->minor,
			) );
		$revId = $revision->insertOn( $dbw );
		$changed = $article->updateIfNewerOn( $dbw, $revision );

		if( $created ) {
			wfDebug( __METHOD__ . ": running onArticleCreate\n" );
			Article::onArticleCreate( $this->title );
		} else {
			if( $changed ) {
				wfDebug( __METHOD__ . ": running onArticleEdit\n" );
				Article::onArticleEdit( $this->title );
			}
		}
		if( $created || $changed ) {
			wfDebug( __METHOD__ . ": running edit updates\n" );
			$article->editUpdates(
				$this->getText(),
				$this->getComment(),
				$this->minor,
				$this->timestamp,
				$revId );
		}
		
		return true;
	}

}

/**
 *
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class WikiImporter {
	var $mSource = null;
	var $mPageCallback = null;
	var $mPageOutCallback = null;
	var $mRevisionCallback = null;
	var $lastfield;

	function WikiImporter( $source ) {
		$this->setRevisionCallback( array( &$this, "importRevision" ) );
		$this->mSource = $source;
	}

	function throwXmlError( $err ) {
		$this->debug( "FAILURE: $err" );
		wfDebug( "WikiImporter XML error: $err\n" );
	}

	# --------------

	function doImport() {
		if( empty( $this->mSource ) ) {
			return new WikiErrorMsg( "importnotext" );
		}

		$parser = xml_parser_create( "UTF-8" );

		# case folding violates XML standard, turn it off
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, false );

		xml_set_object( $parser, $this );
		xml_set_element_handler( $parser, "in_start", "" );

		$offset = 0; // for context extraction on error reporting
		do {
			$chunk = $this->mSource->readChunk();
			if( !xml_parse( $parser, $chunk, $this->mSource->atEnd() ) ) {
				wfDebug( "WikiImporter::doImport encountered XML parsing error\n" );
				return new WikiXmlError( $parser, 'XML import parse failure', $chunk, $offset );
			}
			$offset += strlen( $chunk );
		} while( $chunk !== false && !$this->mSource->atEnd() );
		xml_parser_free( $parser );

		return true;
	}

	function debug( $data ) {
		#wfDebug( "IMPORT: $data\n" );
	}

	function notice( $data ) {
		global $wgCommandLineMode;
		if( $wgCommandLineMode ) {
			print "$data\n";
		} else {
			global $wgOut;
			$wgOut->addHTML( "<li>$data</li>\n" );
		}
	}

	/**
	 * Sets the action to perform as each new page in the stream is reached.
	 * @param callable $callback
	 * @return callable
	 */
	function setPageCallback( $callback ) {
		$previous = $this->mPageCallback;
		$this->mPageCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each page in the stream is completed.
	 * Callback accepts the page title and a count of revisions.
	 * @param callable $callback
	 * @return callable
	 */
	function setPageOutCallback( $callback ) {
		$previous = $this->mPageOutCallback;
		$this->mPageOutCallback = $callback;
		return $previous;
	}

	/**
	 * Sets the action to perform as each page revision is reached.
	 * @param callable $callback
	 * @return callable
	 */
	function setRevisionCallback( $callback ) {
		$previous = $this->mRevisionCallback;
		$this->mRevisionCallback = $callback;
		return $previous;
	}

	/**
	 * Default per-revision callback, performs the import.
	 * @param WikiRevision $revision
	 * @private
	 */
	function importRevision( &$revision ) {
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->deadlockLoop( array( &$revision, 'importOldRevision' ) );
	}

	/**
	 * Alternate per-revision callback, for debugging.
	 * @param WikiRevision $revision
	 * @private
	 */
	function debugRevisionHandler( &$revision ) {
		$this->debug( "Got revision:" );
		if( is_object( $revision->title ) ) {
			$this->debug( "-- Title: " . $revision->title->getPrefixedText() );
		} else {
			$this->debug( "-- Title: <invalid>" );
		}
		$this->debug( "-- User: " . $revision->user_text );
		$this->debug( "-- Timestamp: " . $revision->timestamp );
		$this->debug( "-- Comment: " . $revision->comment );
		$this->debug( "-- Text: " . $revision->text );
	}

	/**
	 * Notify the callback function when a new <page> is reached.
	 * @param Title $title
	 * @private
	 */
	function pageCallback( $title ) {
		if( is_callable( $this->mPageCallback ) ) {
			call_user_func( $this->mPageCallback, $title );
		}
	}

	/**
	 * Notify the callback function when a </page> is closed.
	 * @param Title $title
	 * @param int $revisionCount
	 * @private
	 */
	function pageOutCallback( $title, $revisionCount ) {
		if( is_callable( $this->mPageOutCallback ) ) {
			call_user_func( $this->mPageOutCallback, $title, $revisionCount );
		}
	}


	# XML parser callbacks from here out -- beware!
	function donothing( $parser, $x, $y="" ) {
		#$this->debug( "donothing" );
	}

	function in_start( $parser, $name, $attribs ) {
		$this->debug( "in_start $name" );
		if( $name != "mediawiki" ) {
			return $this->throwXMLerror( "Expected <mediawiki>, got <$name>" );
		}
		xml_set_element_handler( $parser, "in_mediawiki", "out_mediawiki" );
	}

	function in_mediawiki( $parser, $name, $attribs ) {
		$this->debug( "in_mediawiki $name" );
		if( $name == 'siteinfo' ) {
			xml_set_element_handler( $parser, "in_siteinfo", "out_siteinfo" );
		} elseif( $name == 'page' ) {
			$this->workRevisionCount = 0;
			xml_set_element_handler( $parser, "in_page", "out_page" );
		} else {
			return $this->throwXMLerror( "Expected <page>, got <$name>" );
		}
	}
	function out_mediawiki( $parser, $name ) {
		$this->debug( "out_mediawiki $name" );
		if( $name != "mediawiki" ) {
			return $this->throwXMLerror( "Expected </mediawiki>, got </$name>" );
		}
		xml_set_element_handler( $parser, "donothing", "donothing" );
	}


	function in_siteinfo( $parser, $name, $attribs ) {
		// no-ops for now
		$this->debug( "in_siteinfo $name" );
		switch( $name ) {
		case "sitename":
		case "base":
		case "generator":
		case "case":
		case "namespaces":
		case "namespace":
			break;
		default:
			return $this->throwXMLerror( "Element <$name> not allowed in <siteinfo>." );
		}
	}

	function out_siteinfo( $parser, $name ) {
		if( $name == "siteinfo" ) {
			xml_set_element_handler( $parser, "in_mediawiki", "out_mediawiki" );
		}
	}


	function in_page( $parser, $name, $attribs ) {
		$this->debug( "in_page $name" );
		switch( $name ) {
		case "id":
		case "title":
		case "restrictions":
			$this->appendfield = $name;
			$this->appenddata = "";
			$this->parenttag = "page";
			xml_set_element_handler( $parser, "in_nothing", "out_append" );
			xml_set_character_data_handler( $parser, "char_append" );
			break;
		case "revision":
			$this->workRevision = new WikiRevision;
			$this->workRevision->setTitle( $this->workTitle );
			$this->workRevisionCount++;
			xml_set_element_handler( $parser, "in_revision", "out_revision" );
			break;
		default:
			return $this->throwXMLerror( "Element <$name> not allowed in a <page>." );
		}
	}

	function out_page( $parser, $name ) {
		$this->debug( "out_page $name" );
		if( $name != "page" ) {
			return $this->throwXMLerror( "Expected </page>, got </$name>" );
		}
		xml_set_element_handler( $parser, "in_mediawiki", "out_mediawiki" );

		$this->pageOutCallback( $this->workTitle, $this->workRevisionCount );
		
		$this->workTitle = NULL;
		$this->workRevision = NULL;
		$this->workRevisionCount = 0;
	}

	function in_nothing( $parser, $name, $attribs ) {
		$this->debug( "in_nothing $name" );
		return $this->throwXMLerror( "No child elements allowed here; got <$name>" );
	}
	function char_append( $parser, $data ) {
		$this->debug( "char_append '$data'" );
		$this->appenddata .= $data;
	}
	function out_append( $parser, $name ) {
		$this->debug( "out_append $name" );
		if( $name != $this->appendfield ) {
			return $this->throwXMLerror( "Expected </{$this->appendfield}>, got </$name>" );
		}
		xml_set_element_handler( $parser, "in_$this->parenttag", "out_$this->parenttag" );
		xml_set_character_data_handler( $parser, "donothing" );

		switch( $this->appendfield ) {
		case "title":
			$this->workTitle = $this->appenddata;
			$this->pageCallback( $this->workTitle );
			break;
		case "id":
			if ( $this->parenttag == 'revision' ) {
				$this->workRevision->setID( $this->appenddata );
			}
			break;
		case "text":
			$this->workRevision->setText( $this->appenddata );
			break;
		case "username":
			$this->workRevision->setUsername( $this->appenddata );
			break;
		case "ip":
			$this->workRevision->setUserIP( $this->appenddata );
			break;
		case "timestamp":
			$this->workRevision->setTimestamp( $this->appenddata );
			break;
		case "comment":
			$this->workRevision->setComment( $this->appenddata );
			break;
		case "minor":
			$this->workRevision->setMinor( true );
			break;
		default:
			$this->debug( "Bad append: {$this->appendfield}" );
		}
		$this->appendfield = "";
		$this->appenddata = "";
	}

	function in_revision( $parser, $name, $attribs ) {
		$this->debug( "in_revision $name" );
		switch( $name ) {
		case "id":
		case "timestamp":
		case "comment":
		case "minor":
		case "text":
			$this->parenttag = "revision";
			$this->appendfield = $name;
			xml_set_element_handler( $parser, "in_nothing", "out_append" );
			xml_set_character_data_handler( $parser, "char_append" );
			break;
		case "contributor":
			xml_set_element_handler( $parser, "in_contributor", "out_contributor" );
			break;
		default:
			return $this->throwXMLerror( "Element <$name> not allowed in a <revision>." );
		}
	}

	function out_revision( $parser, $name ) {
		$this->debug( "out_revision $name" );
		if( $name != "revision" ) {
			return $this->throwXMLerror( "Expected </revision>, got </$name>" );
		}
		xml_set_element_handler( $parser, "in_page", "out_page" );

		$out = call_user_func_array( $this->mRevisionCallback,
			array( &$this->workRevision, &$this ) );
		if( !empty( $out ) ) {
			global $wgOut;
			$wgOut->addHTML( "<li>" . $out . "</li>\n" );
		}
	}

	function in_contributor( $parser, $name, $attribs ) {
		$this->debug( "in_contributor $name" );
		switch( $name ) {
		case "username":
		case "ip":
		case "id":
			$this->parenttag = "contributor";
			$this->appendfield = $name;
			xml_set_element_handler( $parser, "in_nothing", "out_append" );
			xml_set_character_data_handler( $parser, "char_append" );
			break;
		default:
			$this->throwXMLerror( "Invalid tag <$name> in <contributor>" );
		}
	}

	function out_contributor( $parser, $name ) {
		$this->debug( "out_contributor $name" );
		if( $name != "contributor" ) {
			return $this->throwXMLerror( "Expected </contributor>, got </$name>" );
		}
		xml_set_element_handler( $parser, "in_revision", "out_revision" );
	}

}

/** @package MediaWiki */
class ImportStringSource {
	function ImportStringSource( $string ) {
		$this->mString = $string;
		$this->mRead = false;
	}

	function atEnd() {
		return $this->mRead;
	}

	function readChunk() {
		if( $this->atEnd() ) {
			return false;
		} else {
			$this->mRead = true;
			return $this->mString;
		}
	}
}

/** @package MediaWiki */
class ImportStreamSource {
	function ImportStreamSource( $handle ) {
		$this->mHandle = $handle;
	}

	function atEnd() {
		return feof( $this->mHandle );
	}

	function readChunk() {
		return fread( $this->mHandle, 32768 );
	}

	function newFromFile( $filename ) {
		$file = @fopen( $filename, 'rt' );
		if( !$file ) {
			return new WikiError( "Couldn't open import file" );
		}
		return new ImportStreamSource( $file );
	}

	function newFromUpload( $fieldname = "xmlimport" ) {
		$upload =& $_FILES[$fieldname];

		if( !isset( $upload ) || !$upload['name'] ) {
			return new WikiErrorMsg( 'importnofile' );
		}
		if( !empty( $upload['error'] ) ) {
			return new WikiErrorMsg( 'importuploaderror', $upload['error'] );
		}
		$fname = $upload['tmp_name'];
		if( is_uploaded_file( $fname ) ) {
			return ImportStreamSource::newFromFile( $fname );
		} else {
			return new WikiErrorMsg( 'importnofile' );
		}
	}

	function newFromURL( $url ) {
		wfDebug( __METHOD__ . ": opening $url\n" );
		# fopen-wrappers are normally turned off for security.
		ini_set( "allow_url_fopen", true );
		$ret = ImportStreamSource::newFromFile( $url );
		ini_set( "allow_url_fopen", false );
		return $ret;
	}

	function newFromInterwiki( $interwiki, $page, $history=false ) {
		$base = Title::getInterwikiLink( $interwiki );
		$link = Title::newFromText( "$interwiki:Special:Export/$page" );
		if( empty( $base ) || empty( $link ) ) {
			return new WikiError( 'Bad interwiki link' );
		} else {
			$params = $history ? 'history=1' : '';
			$url = $link->getFullUrl( $params );
			return ImportStreamSource::newFromURL( $url );
		}
	}
}


?>
