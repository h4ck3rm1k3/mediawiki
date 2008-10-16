<?php
if (!defined('MEDIAWIKI')) die();

class CodeRepository {
	public static function newFromName( $name ) {
		$dbw = wfGetDB( DB_MASTER );
		$row = $dbw->selectRow(
			'code_repo',
			array(
				'repo_id',
				'repo_name',
				'repo_path',
				'repo_viewvc',
				'repo_bugzilla' ),
			array( 'repo_name' => $name ),
			__METHOD__ );

		if( $row ) {
			return self::newFromRow( $row );
		} else {
			return null;
		}
	}

	static function newFromRow( $row ) {
		$repo = new CodeRepository();
		$repo->mId = intval($row->repo_id);
		$repo->mName = $row->repo_name;
		$repo->mPath = $row->repo_path;
		$repo->mViewVc = $row->repo_viewvc;
		$repo->mBugzilla = $row->repo_bugzilla;
		return $repo;
	}

	static function getRepoList(){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'code_repo', '*', array(), __METHOD__ );
		$repos = array();
		foreach( $res as $row ){
			$repos[] = self::newFromRow( $row );
		}
		return $repos;
	}

	function getId() {
		return intval( $this->mId );
	}

	function getName() {
		return $this->mName;
	}

	function getPath(){
		return $this->mPath;
	}

	function getViewVcBase(){
		return $this->mViewVc;
	}

	/**
	 * Return a bug URL or false.
	 */
	function getBugPath( $bugId ) {
		if( $this->mBugzilla ) {
			return str_replace( '$1',
				urlencode( $bugId ), $this->mBugzilla );
		}
		return false;
	}

	function getLastStoredRev() {
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectField(
			'code_rev',
			'MAX(cr_id)',
			array( 'cr_repo_id' => $this->getId() ),
			__METHOD__
		);
		return intval( $row );
	}
	
	function getAuthorList() {
		global $wgMemc;
		$key = wfMemcKey( 'codereview', 'authors', $this->getId() );
		$authors = $wgMemc->get( $key );
		if( is_array($authors) ) {
			return $authors;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 
			'code_rev',
			array( 'cr_author', 'MAX(cr_timestamp) AS time' ),
			array( 'cr_repo_id' => $this->getId() ),
			__METHOD__,
			array( 'GROUP BY' => 'cr_author', 
				'ORDER BY' => 'time DESC', 'LIMIT' => 500 )
		);
		$authors = array();
		while( $row = $dbr->fetchObject( $res ) ) {
			$authors[] = $row->cr_author;
		}
		$wgMemc->set( $key, $authors, 3600*24*3 );
		return $authors;
	}
	
	function getTagList() {
		global $wgMemc;
		$key = wfMemcKey( 'codereview', 'tags', $this->getId() );
		$tags = $wgMemc->get( $key );
		if( is_array($tags) ) {
			return $tags;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 
			'code_tags',
			array( 'ct_tag', 'COUNT(*) AS revs' ),
			array( 'ct_repo_id' => $this->getId() ),
			__METHOD__,
			array( 'GROUP BY' => 'ct_tag', 
				'ORDER BY' => 'revs DESC', 'LIMIT' => 500 )
		);
		$tags = array();
		while( $row = $dbr->fetchObject( $res ) ) {
			$tags[] = $row->ct_tag;
		}
		$wgMemc->set( $key, $tags, 3600*24*3 );
		return $tags;
	}

	/**
	 * Load a particular revision out of the DB
	 */
	function getRevision( $id ) {
		if ( !$this->isValidRev( $id ) ) {
			return null;
		}
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow(
			'code_rev',
			'*',
			array(
				'cr_id' => $id,
				'cr_repo_id' => $this->getId(),
			),
			__METHOD__
		);
		if( !$row )
			throw new MWException( 'Failed to load expected revision data' );
		return CodeRevision::newFromRow( $row );
	}

	/**
	 * @param int $rev Revision ID
	 * @param $useCache 'skipcache' to avoid caching
	 *                   'cached' to *only* fetch if cached
	 */
	function getDiff( $rev, $useCache = '' ) {
		global $wgMemc;

		$rev1 = $rev - 1;
		$rev2 = $rev;
		
		$revision = $this->getRevision( $rev );
		if( $revision == null || !$revision->isDiffable() ) {
			return false;
		}

		$key = wfMemcKey( 'svn', md5( $this->mPath ), 'diff', $rev1, $rev2 );
		if( $useCache === 'skipcache' ) {
			$data = NULL;
		} else {
			$data = $wgMemc->get( $key );
		}

		if( !$data && $useCache != 'cached' ) {
			$svn = SubversionAdaptor::newFromRepo( $this->mPath );
			$data = $svn->getDiff( '', $rev1, $rev2 );
			$wgMemc->set( $key, $data, 3600*24*3 );
		}

		return $data;
	}

	/**
	 * Is the requested revid a valid revision to show?
	 * @return bool
	 * @param $rev int Rev id to check
	 */
	function isValidRev( $rev ) {
		$rev = intval( $rev );
		if ( $rev > 0 && $rev <= $this->getLastStoredRev() ) {
			return true;
		}
		return false;
	}
	
	/*
	 * Link the $author to the wikiuser $user
	 * @param string $author
	 * @param User $user
	 * @return bool success
	 */
	function linkTo( $author, User $user ) {
		// We must link to an existing user
		if( !$user->getId() ) {
			return false;
		}
		$dbw = wfGetDB( DB_MASTER );
		// Insert in the auther -> user link row.
		// Skip existing rows.
		$dbw->insert( 'code_authors',
			array(
				'ca_repo_id'   => $this->getId(),
				'ca_author'    => $author,
				'ca_user_text' => $user->getName()
			),
			__METHOD__,
			array( 'IGNORE' )
		);
		// If the last query already found a row, then update it.
		if( !$dbw->affectedRows() ) {
			$dbw->update(
				'code_authors',
				array( 'ca_user_text' => $user->getName() ),
				array(
					'ca_repo_id'  => $this->getId(),
					'ca_author'   => $author,
				),
				__METHOD__
			);
		}
		return ( $dbw->affectedRows() > 0 );
	}

	/*
	 * Link the $author to the wikiuser $user
	 * @param string $author
	 * @return bool success
	 */
	function unlink( $author ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'code_authors',
			array(
				'ca_repo_id' => $this->getId(),
				'ca_author'  => $author,
			),
			__METHOD__
		);
		return ( $dbw->affectedRows() > 0 );
	}
}
