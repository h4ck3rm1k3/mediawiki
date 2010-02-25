<?php
/**
 * API extension for Storyboard.
 * 
 * @file ApiStories.php
 * @ingroup Storyboard
 * 
 * @author Jeroen De Dauw
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

if ( !defined( 'MEDIAWIKI' ) ) {
	// Eclipse helper - will be ignored in production
	require_once ( "ApiBase.php" );
}

/**
 * This action returns the html for Stories to be displayed in a storyboard.
 *
 * @ingroup Storyboard
 */
class ApiStoryboardStoriesFeed extends ApiQueryBase {
	public function __construct( $main, $action ) {
		parent :: __construct( $main, $action );
	}

	/**
	 * Retrieve the stories from the database.
	 */
	public function execute() {
		// Get the requests parameters.
		$params = $this->extractRequestParams();
		
		// Get a slave db object to do read operations against.
		$dbr = wfGetDB( DB_SLAVE );
	}
	
	/**
	 * TODO
	 * @see includes/api/ApiBase#getAllowedParams()
	 */
	public function getAllowedParams() {
		return array (
			'offset' => array (
				ApiBase :: PARAM_DFLT => 0,
				ApiBase :: PARAM_TYPE => 'integer',
				ApiBase :: PARAM_MIN => 0,
			),
			'size' => array (
				ApiBase :: PARAM_DFLT => 5,
				ApiBase :: PARAM_TYPE => 'integer',
				ApiBase :: PARAM_MIN => 1,
				ApiBase :: PARAM_MAX => 100,
			),				
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see includes/api/ApiBase#getParamDescription()
	 */
	public function getParamDescription() {
		return array (
			'offset' => 'Number of the first story to return',
			'size'   => 'Amount of stories to return',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see includes/api/ApiBase#getDescription()
	 */
	public function getDescription() {
		return 'This module returns stories for a storyboard';
	}

	/**
	 * (non-PHPdoc)
	 * @see includes/api/ApiBase#getExamples()
	 */
	protected function getExamples() {
		return array (
			'api.php?action=stories',
			'api.php?action=stories&offset=42',
			'api.php?action=stories&offset=4&size=2',
		);
	}

	/**
	 * TODO
	 * @see includes/api/ApiBase#getVersion()
	 */
	public function getVersion() {
		return __CLASS__ . ': ';
	}	
	
}