<?php
/**
 * API for MediaWiki 1.8+
 *
 * Created on Aug 21, 2008
 *
 * Copyright © 2008 - 2010 Bryan Tong Minh <Bryan.TongMinh@Gmail.com>
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
 * @file
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	// Eclipse helper - will be ignored in production
	require_once( "ApiBase.php" );
}

/**
 * @ingroup API
 */
class ApiUpload extends ApiBase {
	protected $mUpload = null;
	protected $mParams;

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		global $wgUser;

		// Check whether upload is enabled
		if ( !UploadBase::isEnabled() ) {
			$this->dieUsageMsg( array( 'uploaddisabled' ) );
		}

		// Parameter handling
		$this->mParams = $this->extractRequestParams();
		$request = $this->getMain()->getRequest();
		// Add the uploaded file to the params array
		$this->mParams['file'] = $request->getFileName( 'file' );

		// Select an upload module
		if ( !$this->selectUploadModule() ) {
			// This is not a true upload, but a status request or similar
			return;
		}
		if ( !isset( $this->mUpload ) ) {
			$this->dieUsage( 'No upload module set', 'nomodule' );
		}

		// First check permission to upload
		$this->checkPermissions( $wgUser );

		// Fetch the file
		$status = $this->mUpload->fetchFile();
		if ( !$status->isGood() ) {
			$errors = $status->getErrorsArray();
			$error = array_shift( $errors[0] );
			$this->dieUsage( 'Error fetching file from remote source', $error, 0, $errors[0] );
		}

		// Check if the uploaded file is sane
		$this->verifyUpload();

		// Check permission to upload this file
		$permErrors = $this->mUpload->verifyPermissions( $wgUser );
		if ( $permErrors !== true ) {
			// Todo: stash the upload and allow choosing a new name
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}

		// Check warnings if necessary
		$warnings = $this->getApiWarnings();

		if ( $warnings ) {
			$sessionKey = $this->mUpload->stashSession();
			if ( !$sessionKey ) {
				$this->dieUsage( 'Stashing temporary file failed', 'stashfailed' );
			}

			$result['sessionkey'] = $sessionKey;

			// is it really necessary to say 'result=warning'
			$result['result'] = 'Warning';
			$result['warnings'] = $warnings;
			$this->getResult()->addValue( null, $this->getModuleName(), $result );

		} else {
			// Perform the upload
			$result = $this->performUpload();
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $result );
		
		// Cleanup any temporary mess
		$this->mUpload->cleanupTempFile();
	}


	/**
	 * Select an upload module and set it to mUpload. Dies on failure. If the
	 * request was a status request and not a true upload, returns false; 
	 * otherwise true
	 * 
	 * @return bool
	 */
	protected function selectUploadModule() {
		global $wgAllowAsyncCopyUploads;
		$request = $this->getMain()->getRequest();

		// One and only one of the following parameters is needed
		$this->requireOnlyOneParameter( $this->mParams,
			'sessionkey', 'file', 'url', 'statuskey' );

		if ( $wgAllowAsyncCopyUploads && $this->mParams['statuskey'] ) {
			// Status request for an async upload
			$sessionData = UploadFromUrlJob::getSessionData( $this->mParams['statuskey'] );
			if ( !isset( $sessionData['result'] ) ) {
				$this->dieUsage( 'No result in session data', 'missingresult');
			}
			if ( $sessionData['result'] == 'Warning' ) {
				$sessionData['warnings'] = $this->transformWarnings( $sessionData['warnings'] );
				$sessionData['sessionkey'] = $this->mParams['statuskey'];
			}
			$this->getResult()->addValue( null, $this->getModuleName(), $sessionData );
			return false;
			
		} 


		// The following modules all require the filename parameter to be set
		if ( is_null( $this->mParams['filename'] ) ) {
			$this->dieUsageMsg( array( 'missingparam', 'filename' ) );
		}
			

		if ( $this->mParams['sessionkey'] ) {
			// Upload stashed in a previous request
			$sessionData = $request->getSessionData( UploadBase::getSessionKeyName() );
			if ( !UploadFromStash::isValidSessionKey( $this->mParams['sessionkey'], $sessionData ) ) {
				$this->dieUsageMsg( array( 'invalid-session-key' ) );
			}

			$this->mUpload = new UploadFromStash();
			$this->mUpload->initialize( $this->mParams['filename'],
				$this->mParams['sessionkey'],
				$sessionData[$this->mParams['sessionkey']] );


		} elseif ( isset( $this->mParams['file'] ) ) {
			if ( isset( $this->mParams['stash'] ) and $this->mParams['stash'] ) {
				$this->mUpload = new UploadFromFileToStash();
			} else {
				$this->mUpload = new UploadFromFile();
			}
			$this->mUpload->initialize(
				$this->mParams['filename'],
				$request->getUpload( 'file' )
			);
		} elseif ( isset( $this->mParams['url'] ) ) {
			// Make sure upload by URL is enabled:
			if ( !UploadFromUrl::isEnabled() ) {
				$this->dieUsageMsg( array( 'copyuploaddisabled' ) );
			}

			$async = false;
			if ( $this->mParams['asyncdownload'] ) {
				if ( $this->mParams['leavemessage'] && !$this->mParams['ignorewarnings'] ) {
					$this->dieUsage( 'Using leavemessage without ignorewarnings is not supported',
						'missing-ignorewarnings' );
				}
				
				if ( $this->mParams['leavemessage'] ) {
					$async = 'async-leavemessage';
				} else {
					$async = 'async';
				}
			}
			$this->mUpload = new UploadFromUrl;
			$this->mUpload->initialize( $this->mParams['filename'],
				$this->mParams['url'], $async );

		}
		
		return true;
	}

	/**
	 * Checks that the user has permissions to perform this upload.
	 * Dies with usage message on inadequate permissions.
	 * @param $user User The user to check.
	 */
	protected function checkPermissions( $user ) {
		// Check whether the user has the appropriate permissions to upload anyway
		$permission = $this->mUpload->isAllowed( $user );

		if ( $permission !== true ) {
			if ( !$user->isLoggedIn() ) {
				$this->dieUsageMsg( array( 'mustbeloggedin', 'upload' ) );
			} else {
				$this->dieUsageMsg( array( 'badaccess-groups' ) );
			}
		}
	}

	/**
	 * Performs file verification, dies on error.
	 */
	protected function verifyUpload( ) {
		global $wgFileExtensions;

		$verification = $this->mUpload->verifyUpload( );
		if ( $verification['status'] === UploadBase::OK ) {
			return;
		}

		// TODO: Move them to ApiBase's message map
		switch( $verification['status'] ) {
			case UploadBase::EMPTY_FILE:
				$this->dieUsage( 'The file you submitted was empty', 'empty-file' );
				break;
			case UploadBase::FILE_TOO_LARGE:
				$this->dieUsage( 'The file you submitted was too large', 'file-too-large' );
				break;
			case UploadBase::FILETYPE_MISSING:
				$this->dieUsage( 'The file is missing an extension', 'filetype-missing' );
				break;
			case UploadBase::FILETYPE_BADTYPE:
				$this->dieUsage( 'This type of file is banned', 'filetype-banned',
						0, array(
							'filetype' => $verification['finalExt'],
							'allowed' => $wgFileExtensions
						) );
				break;
			case UploadBase::MIN_LENGTH_PARTNAME:
				$this->dieUsage( 'The filename is too short', 'filename-tooshort' );
				break;
			case UploadBase::ILLEGAL_FILENAME:
				$this->dieUsage( 'The filename is not allowed', 'illegal-filename',
						0, array( 'filename' => $verification['filtered'] ) );
				break;
			case UploadBase::VERIFICATION_ERROR:
				$this->getResult()->setIndexedTagName( $verification['details'], 'detail' );
				$this->dieUsage( 'This file did not pass file verification', 'verification-error',
						0, array( 'details' => $verification['details'] ) );
				break;
			case UploadBase::HOOK_ABORTED:
				$this->dieUsage( "The modification you tried to make was aborted by an extension hook",
						'hookaborted', 0, array( 'error' => $verification['error'] ) );
				break;
			default:
				$this->dieUsage( 'An unknown error occurred', 'unknown-error',
						0, array( 'code' =>  $verification['status'] ) );
				break;
		}
	}


	/**
	 * Check warnings if ignorewarnings is not set.
	 * Returns a suitable array for inclusion into API results if there were warnings
	 * Returns the empty array if there were no warnings
	 *
	 * @return array
	 */
	protected function getApiWarnings() {
		$warnings = array();

		if ( !$this->mParams['ignorewarnings'] ) {
			$warnings = $this->mUpload->checkWarnings();
			if ( $warnings ) {
				// Add indices
				$this->getResult()->setIndexedTagName( $warnings, 'warning' );

				if ( isset( $warnings['duplicate'] ) ) {
					$dupes = array();
					foreach ( $warnings['duplicate'] as $key => $dupe ) {
						$dupes[] = $dupe->getName();
					}
					// despite 'set' in the name of this function, it just formats $dupes
					$this->getResult()->setIndexedTagName( $dupes, 'duplicate' );
					$warnings['duplicate'] = $dupes;
				}

				if ( isset( $warnings['exists'] ) ) {
					$warning = $warnings['exists'];
					unset( $warnings['exists'] );
					$warnings[$warning['warning']] = $warning['file']->getName();
				}

				$result['result'] = 'Warning';
				$result['warnings'] = $this->transformWarnings( $warnings );

				$sessionKey = $this->mUpload->stashSession();
				if ( !$sessionKey ) {
					$this->dieUsage( 'Stashing temporary file failed', 'stashfailed' );
				}

				$result['sessionkey'] = $sessionKey;

				return $result;
			}
		}

		return $warnings;
	}
	
	/**
	 * Transforms a warnings array returned by mUpload->checkWarnings() into
	 * something that can be directly used as API result
	 */
	protected function transformWarnings( $warnings ) {
		// Add indices
		$this->getResult()->setIndexedTagName( $warnings, 'warning' );

		if ( isset( $warnings['duplicate'] ) ) {
			$dupes = array();
			foreach ( $warnings['duplicate'] as $dupe ) {
				$dupes[] = $dupe->getName();
			}
			$this->getResult()->setIndexedTagName( $dupes, 'duplicate' );
			$warnings['duplicate'] = $dupes;
		}

		if ( isset( $warnings['exists'] ) ) {
			$warning = $warnings['exists'];
			unset( $warnings['exists'] );
			$warnings[$warning['warning']] = $warning['file']->getName();
		}
		
		return $warnings;	
	}

	/**
	 * Perform the actual upload. Returns a suitable result array on success;
	 * dies on failure.
	 */
	protected function performUpload() {
		global $wgUser;

		// Use comment as initial page text by default
		if ( is_null( $this->mParams['text'] ) ) {
			$this->mParams['text'] = $this->mParams['comment'];
		}

		$file = $this->mUpload->getLocalFile();
		$watch = $this->getWatchlistValue( $this->mParams['watchlist'], $file->getTitle() );

		// Deprecated parameters
		if ( $this->mParams['watch'] ) {
			$watch = true;
		}

		// No errors, no warnings: do the upload
		$status = $this->mUpload->performUpload( $this->mParams['comment'],
			$this->mParams['text'], $watch, $wgUser );

		if ( !$status->isGood() ) {
			$error = $status->getErrorsArray();

			if ( count( $error ) == 1 && $error[0][0] == 'async' ) {
				// The upload can not be performed right now, because the user
				// requested so
				return array(
					'result' => 'Queued',
					'statuskey' => $error[0][1],
				);
			} else {
				$this->getResult()->setIndexedTagName( $error, 'error' );

				$this->dieUsage( 'An internal error occurred', 'internal-error', 0, $error );
			}
		}

		$file = $this->mUpload->getLocalFile();

		$result['result'] = 'Success';
		$result['filename'] = $file->getName();

		$result['imageinfo'] = $this->mUpload->getImageInfo( $this->getResult() );


	/**
	 * Get image info, but also add one or more thumbnails.  Relies a lot on SessionStashFile's
	 * modified transform().
	 * This method also has to pass in an API result, which is a rather bad idea and breaks
 	 * separation of concerns, but that's how the rest of the code works :(
	 *
	 * @param {ApiResult} result
	 * @return {Mixed} image info, mostly key-value but also including thumbnails with similar sets of sub-properties
	 */

		// XXX get default thumbnail width. 
		// perhaps when initializing... Isn't this a global? Can't find it anywhere in docs.
		$defaultThumbWidth = 120;
		if ( isset( $this->mParams['thumbwidth'] ) ) {
			$thumbnails = array();
			if ( $this->mParams['thumbwidth'] ) {
				$widths = explode( ',', $this->mParams['thumbwidth'] );
			} else {
				$widths = array( $defaultThumbWidth ); // magic number grrrrr....
			}

			foreach ( $widths as $width ) {
				$thumbnailKey = $width . "px";
				if ( $thumbnails[$thumbnailKey] ) {
					continue;
				}
				$width = max( 1, min( $file->getWidth(), $width ) );
			
				// because the file is a SessionStashFile, this thumbnail will also be stashed,
				// and a thumbnailFile will be created
				if ( ! $thumbnailImage = $file->getThumbnail( $width ) ) { 
					$this->dieUsageMsg( 'Could not obtain thumbnail', 'nothumb' );
				}
				
				// now also return metadata about thumbnails, with all possible properties
				$thumbnailFile = $thumbnailImage->thumbnailFile;
				$allPropertyNames = array_flip( ApiQueryImageInfo::getPropertyNames() );
				$thumbnails[$thumbnailKey] = ApiQueryImageInfo::getInfo( $thumbnailFile, $allPropertyNames, $result );
			
			}
			$result['thumbnails'] = $thumbnails;
		}



		return $result;
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function getAllowedParams() {
		$params = array(
			'filename' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'comment' => array(
				ApiBase::PARAM_DFLT => ''
			),
			'text' => null,
			'token' => null,
			'watch' => array(
				ApiBase::PARAM_DFLT => false,
				ApiBase::PARAM_DEPRECATED => true,
			),
			'watchlist' => array(
				ApiBase::PARAM_DFLT => 'preferences',
				ApiBase::PARAM_TYPE => array(
					'watch',
					'preferences',
					'nochange'
				),
			),
			'ignorewarnings' => false,
			'file' => null,
			'url' => null,

			'sessionkey' => null,
			'thumbwidth' => null,
			'stash' => array(
				ApiBase::PARAM_DFLT => false,
			)
		);

		global $wgAllowAsyncCopyUploads;
		if ( $wgAllowAsyncCopyUploads ) {
			$params += array(
				'asyncdownload' => false,
				'leavemessage' => false,
				'statuskey' => null,
			);
		}
		return $params;
	}

	public function getParamDescription() {
		$params = array(
			'filename' => 'Target filename',
			'token' => 'Edit token. You can get one of these through prop=info',
			'comment' => 'Upload comment. Also used as the initial page text for new files if "text" is not specified',
			'text' => 'Initial page text for new files',
			'watch' => 'Watch the page',
			'watchlist' => 'Unconditionally add or remove the page from your watchlist, use preferences or do not change watch',
			'ignorewarnings' => 'Ignore any warnings',
			'file' => 'File contents',
			'url' => 'Url to fetch the file from',
			'sessionkey' => 'Session key that identifies a previous upload that was stashed temporarily.',
			'stash' => 'If set to a true value, the server will not add the file to the repository and stash it temporarily.',
			'thumbwidth' => 'thumbwidths'
		);

		global $wgAllowAsyncCopyUploads;
		if ( $wgAllowAsyncCopyUploads ) {
			$params += array(
				'asyncdownload' => 'Make fetching a URL asynchronous',
				'leavemessage' => 'If asyncdownload is used, leave a message on the user talk page if finished',
				'statuskey' => 'Fetch the upload status for this session key',
			);
		}

		return $params;

	}

	public function getDescription() {
		return array(
			'Upload a file, or get the status of pending uploads. Several methods are available:',
			' * Upload file contents directly, using the "file" parameter',
			' * Have the MediaWiki server fetch a file from a URL, using the "url" parameter',
			' * Complete an earlier upload that failed due to warnings, using the "sessionkey" parameter',
			'Note that the HTTP POST must be done as a file upload (i.e. using multipart/form-data) when',
			'sending the "file". Note also that queries using session keys must be',
			'done in the same login session as the query that originally returned the key (i.e. do not',
			'log out and then log back in). Also you must get and send an edit token before doing any upload stuff'
		);
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'uploaddisabled' ),
			array( 'invalid-session-key' ),
			array( 'uploaddisabled' ),
			array( 'badaccess-groups' ),
			array( 'mustbeloggedin', 'upload' ),
			array( 'badaccess-groups' ),
			array( 'badaccess-groups' ),
			array( 'code' => 'fetchfileerror', 'info' => '' ),
			array( 'code' => 'nomodule', 'info' => 'No upload module set' ),
			array( 'code' => 'empty-file', 'info' => 'The file you submitted was empty' ),
			array( 'code' => 'filetype-missing', 'info' => 'The file is missing an extension' ),
			array( 'code' => 'filename-tooshort', 'info' => 'The filename is too short' ),
			array( 'code' => 'overwrite', 'info' => 'Overwriting an existing file is not allowed' ),
			array( 'code' => 'stashfailed', 'info' => 'Stashing temporary file failed' ),
			array( 'code' => 'internal-error', 'info' => 'An internal error occurred' ),
		) );
	}

	public function getTokenSalt() {
		return '';
	}

	protected function getExamples() {
		return array(
			'Upload from a URL:',
			'    api.php?action=upload&filename=Wiki.png&url=http%3A//upload.wikimedia.org/wikipedia/en/b/bc/Wiki.png',
			'Complete an upload that failed due to warnings:',
			'    api.php?action=upload&filename=Wiki.png&sessionkey=sessionkey&ignorewarnings=1',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: ApiUpload.php 51812 2009-06-12 23:45:20Z dale $';
	}
}
