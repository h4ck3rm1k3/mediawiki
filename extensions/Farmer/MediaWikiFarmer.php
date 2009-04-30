<?php
/**
 * @file
 * @ingroup Extensions
 * @author Gregory Szorc <gregory.szorc@gmail.com>
 */


/**
 * This class exposes functionality for a MediaWiki farm
 *
 * @addtogroup Extension
 */
class MediaWikiFarmer {

	protected $_parameters = array();

	/** Directory where config files are stored */
	protected $_configDirectory;
	protected $_storageRoot;
	protected $_storageUrl;

	/** Parameter to call_user_func which will return a wiki name from the environment */
	protected $_matchFunction;

	/** Regular expression to be used by internal _matchByURL* functions */
	protected $_matchRegExp;

	/** Array key to return from match in _matchByURL* functions */
	protected $_matchOffset;

	/** Whether to use $wgConf */
	protected $_useWgConf;

	/** Database settings */
	protected $_dbFromWikiFunction;
	protected $_dbTablePrefixSeparator;
	protected $_dbTablePrefix;
	protected $_dbAdminUser;
	protected $_dbAdminPassword;

	/** Other */
	protected $_defaultWiki;
	protected $_onUnknownWikiFunction;
	protected $_redirectToURL;
	protected $_dbSourceFile;
	protected $_defaultSkin;

	/** Extensions available to Farmer */
	protected $_extensions = array();

	protected $_sharedGroups = false;
	protected $_extensionsLoaded = false;

	/** Instance of MediaWikiFarmer_Wiki */
	protected $_activeWiki = null;
	
	/** Instance of this class */
	protected static $_instance = null;


	public static function getInstance() {
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @param $params Array of parameters to control behavior
	 *
	 * @todo Load up special page
	 */
	public function __construct( $params ) {
		$this->_configDirectory = $params['configDirectory'];
		$this->_matchFunction = $params['wikiIdentifierFunction'];
		$this->_matchRegExp = $params['matchRegExp'];
		$this->_matchOffset = $params['matchOffset'];
		$this->_matchServerNameSuffix = $params['matchServerNameSuffix'];
		$this->_defaultWiki = $params['defaultWiki'];
		$this->_onUnknownWikiFunction = $params['onUnknownWiki'];
		$this->_redirectToURL = $params['redirectToURL'];
		$this->_useWgConf = $params['useWgConf'];
		$this->_dbAdminUser = $params['dbAdminUser'];
		$this->_dbAdminPassword = $params['dbAdminPassword'];
		$this->_dbSourceFile = $params['newDbSourceFile'];
		$this->_dbFromWikiFunction = $params['dbFromWikiFunction'];
		$this->_dbTablePrefixSeparator = $params['dbTablePrefixSeparator'];
		$this->_dbTablePrefix = $params['dbTablePrefix'];
		$this->_storageRoot = $params['perWikiStorageRoot'];
		$this->_storageUrl = $params['perWikiStorageUrl'];
		$this->_defaultSkin = $params['defaultSkin'];

		$this->_parameters = $params;

		//register this object as the static instance
		self::$_instance = $this;

		global $wgSharedTables;
		//if the groups table is being shared
		if( in_array( 'user_groups', $wgSharedTables ) ){
			$this->_sharedGroups = true;
		}

		if( !is_dir( $this->_configDirectory ) ){
			throw new MWException( 'configDirectory not found: ' . $this->_configDirectory );
		} else {
			if ( !is_dir( $this->_configDirectory . '/wikis/' ) ){
				mkdir( $this->_configDirectory . '/wikis' );
			}
		}

	}

	public function __get( $key ) {
		if( array_key_exists( $key, $this->_parameters ) ){
			return $this->_parameters[$key];
		}

		$property = '_' . $key;

		return isset( $this->$property ) ? $this->$property : null;
	}

	/**
	 * Get the active wiki for this MediaWikiFarmer instance
	 */
	public function getActiveWiki() {
		return $this->_activeWiki;
	}

	/**
	 * Runs MediaWikiFarmer
	 *
	 * This function does all the fun stuff
	 */
	public function run() {
		if( !$this->_defaultWiki ) {
			throw new MWException( 'Default wiki must be set' );
		}

		// first we try to find the wiki name that was accessed by calling the appropriate function
		if( is_callable( $this->_matchFunction ) ){
			$wiki = call_user_func( $this->_matchFunction, $this );

			// if our function coudln't identify the wiki from the environment
			if ( !$wiki ) {
				$wiki = $this->_defaultWiki;
			}

			// sanitize wiki name
			// we force to lcase b/c having all types of case combos would just be confusing to end-user
			// besides, hostnames are not case sensitive
			$wiki = strtolower( preg_replace( '/[^[:alnum:_\-]]/', '', $wiki ) );

			// now we have a valid wiki name
			return $this->_doWiki( $wiki );

		} else {
			throw new MWException( 'Function to map wiki name in farm not found: ' . print_r( $this->_matchFunction, true ) );
		}
	}

	/**
	 * Performs actions necessary to run a specified wiki
	 *
	 * @param string $wiki Wiki to load
	 */
	protected function _doWiki( $wiki ){
		$wiki = MediaWikiFarmer_Wiki::factory( $wiki );
		$this->_activeWiki = $wiki;

		if( !$wiki->exists() ){
			// if the default wiki doesn't exist (probably first-time user)
			if ( $wiki->isDefaultWiki() ) {

				global $wgSitename;
				$wiki->title = $wgSitename;

				$wiki->save();

				if( !$wiki->exists() ){
					throw new MWException( 'MediaWikiFarmer could not write the default wiki configuration file.' );
				} else {
					$this->updateFarmList();
					$wiki->initialize();
				}
			} else {
				// we are not dealing with the default wiki

				// we invoke the function to be called when an unknown wiki is accessed
				if( is_callable( $this->_onUnknownWikiFunction ) ){
					call_user_func( $this->_onUnknownWikiFunction, $this, $wiki );
				} else {
					throw new MWException( 'Could not call function: ' . print_r( $this->_onUnknownFunction, true ) );
				}
			}
		} else {
			// the wiki exists!
			// we initialize this wiki
			$wiki->initialize();
		}

	}

	/**
	 * Matches a URL to a wiki by comparing a URL to a regular expression
	 * pattern
	 *
	 * This function applies the regular expression as defined by the
	 * defaultWikiIdentifierRegExp parameter and feeds it into preg_match
	 * against the URL.  From the matches array, the defaultWikiIdentifierOffset
	 * key from that array is returned.  False is returns upon failure to match
	 *
	 * @param $farmer MediaWikiFarmer
	 * @param string $url URL that was accessed.  Probably $_SERVER
	 * ['REQUEST_URI']
	 *
	 * @return string Wiki identifier.  Return null, false, or nothing if you
	 * want to use the default wiki, as specified by the 'defaultWiki'
	 * parameter.
	 */
	protected static function _matchByURLRegExp( MediaWikiFarmer $farmer, $url = null ){
		if( is_null( $url ) )
			$url = $_SERVER['REQUEST_URI'];

		if( preg_match( $farmer->_matchRegExp, $url, $matches ) === 1 ){
			if( array_key_exists( $farmer->_matchOffset, $matches ) ) {
				return $matches[$farmer->_matchOffset];
			}
		}

		return false;
	}

	/**
	 * Matches a URL to a wiki by looking at the hostname
	 *
	 * First, parses the URL and extracts the hostname.  Then, we do a
	 * preg_match against the hostname with the pattern defined by the
	 * matchRegExp parameter.  If it matches, we return the matchOffset key from
	 * the matching array, if that key exists.  Else we return false
	 *
	 * @param string $url URL to match to a wiki
	 * @return string|bool Wiki name on success.  false on failure
	 */
	protected static function _matchByURLHostname( MediaWikiFarmer $farmer, $url = null ){
		if( is_null( $url ) )
			$url = $_SERVER['REQUEST_URI'];
			
		if ( $result = parse_url( $url ) ) {
			if ( $host = $result['host'] ) {
				if ( preg_match( $farmer->_matchRegExp, $host, $matches ) === 1 ) {
					if ( array_key_exists( $farmer->_matchOffset, $matches ) ) {
						return $matches[$farmer->_matchOffset];
					}
				}
			}
		}

		return false;
	}

	/**
	 * Returns a wiki name by matching against the server name
	 *
	 * Valuable for wildcard DNS farms, like wiki1.mydomain, wiki2.mydomain, etc
	 *
	 * Will look at the server name and return everything before the first
	 * period
	 *
	 */
	protected static function _matchByServerName( MediaWikiFarmer $farmer ){
		$serverName = $_SERVER['SERVER_NAME'];

		//if string ends with the suffix specified
		if ( substr( $serverName, -strlen( $farmer->_matchServerNameSuffix ) ) == $farmer->_matchServerNameSuffix
			&& $serverName != $farmer->_matchServerNameSuffix ) {
			return substr( $serverName, 0, -strlen( $farmer->_matchServerNameSuffix ) - 1 );
		}

		return false;

	}

	/**
	 * Sends HTTP redirect to URL
	 *
	 * This function is called by default when an unknown wiki is accessed.
	 *
	 * @param string $wiki Unknown wiki that was accessed
	 */
	protected static function _redirectTo( MediaWikiFarmer $farmer, $wiki ) {
		$urlTo = str_replace( '$1', $wiki->name, $farmer->_redirectToURL );

		header( 'Location: ' . $urlTo );
		exit;
	}

	/**
	 * Returns the database table prefix, as suitable for $wgDBprefix
	 */
	public function splitWikiDB( $wiki ){
		$callback = $this->_dbFromWikiFunction;
		return call_user_func( $callback, $this, $wiki );
		
	}

	protected static function _prefixTable( MediaWikiFarmer $farmer, $wiki ){
		if( $farmer->useWgConf() ){
			global $wgConf;
			return array( $wgConf->get( 'wgDBname', $wiki ), $wgConf->get( 'wgDBprefix', $wiki ) );
		} else {
			global $wgDBname;
			$prefix = $farmer->_dbTablePrefix . $wiki . $farmer->_dbTablePrefixSeparator;
			return array( $wgDBname, $prefix );
		}
	}

	/**
	 * Determines whether use can create a wiki
	 *
	 * @param $user User object
	 * @param $wiki String: wiki name (optional)
	 *
	 * @return bool
	 */
	public static function userCanCreateWiki( $user, $wiki = null ) {
		return $user->isAllowed( 'createwiki' );
	}

	public static function userIsFarmerAdmin( $user ) {
		return $user->isAllowed( 'farmeradmin' );
	}

	/**
	 * Gets file holding extensions definitions
	 */
	protected function _getExtensionFile() {
		return $this->_configDirectory . '/extensions';
	}

	/**
	 * Gets extensions objects
	 */
	public function getExtensions( $forceReload = false ) {
		if ( $this->_extensionsLoaded && !$forceReload ) {
			return $this->_extensions;
		}

		if( is_readable( $this->_getExtensionFile() ) ) {
			$contents = file_get_contents( $this->_getExtensionFile() );

			$extensions = unserialize( $contents );

			if( is_array( $extensions ) ) {
				$this->_extensions = $extensions;
			}
		} else {
			//perhaps we should throw an error or something?
		}

		$extensionsLoaded = true;
		return $this->_extensions;
	}

	public function registerExtension( MediaWikiFarmer_Extension $e ) {
		//force reload of file
		$this->getExtensions( true );

		$this->_extensions[$e->name] = $e;

		$this->_writeExtensions();
	}

	/**
	 * Writes out extension definitions to file
	 */
	protected function _writeExtensions() {
		$file = $this->_getExtensionFile();

		$content = serialize( $this->_extensions );

		if ( file_put_contents( $file, $content, LOCK_EX ) != strlen( $content ) ) {
			throw new MWException( wfMsgHtml( 'farmer-error-noextwrite' ) . wfMsgHtml( 'word-separator' ) . $file );
		}
	}

	public function getConfigPath() {
		return $this->_configDirectory;
	}

	public function getStorageRoot() {
		return $this->_storageRoot;
	}

	public function getStorageUrl() {
		return $this->_storageUrl;
	}

	/**
	 * Looks for wiki configuration files and updates the farm digest file
	 */
	public function updateFarmList() {
		$directory = new DirectoryIterator( $this->_configDirectory . '/wikis/' );
		$wikis = array();

		foreach( $directory as $file ) {
			if( !$file->isDot() && !$file->isDir() ) {
				if( substr( $file->getFilename(), -7 ) == '.farmer' ) {
					$base = substr( $file->getFileName(), 0, -7 );
					$wikis[$base] = MediaWikiFarmer_Wiki::factory( $base );
				}
			}
		}

		$farmList = array();

		foreach( $wikis as $k=>$v ) {
			$arr = array();
			$arr['name'] = $v->name;
			$arr['title'] = $v->title;
			$arr['description'] = $v->description;

			$farmList[$k] = $arr;

		}

		file_put_contents( $this->_getFarmListFile(), serialize( $farmList ), LOCK_EX );
	}

	public function updateInterwikiTable() {
		$wikis = $this->getFarmList();
		$dbw = wfGetDB( DB_MASTER );
		$replacements = array();
		foreach( $wikis as $key => $stuff ){
			$wiki = MediaWikiFarmer_Wiki::factory( $key );
			$replacements[] = array(
				'iw_prefix' => $wiki->name,
				'iw_url' => $wiki->getUrl(),
				'iw_local' => 1,
			);
		}
		$dbw->replace( 'interwiki', 'iw_prefix', $replacements, __METHOD__ );
	}

	public function getFarmList() {
		return unserialize( file_get_contents( $this->_getFarmListFile() ) );
	}

	protected function _getFarmListFile() {
		return $this->_configDirectory . '/farmlist';
	}

	public function getDefaultWiki() {
		return $this->_defaultWiki;
	}

	public function sharingGroups() {
		return $this->_sharedGroups;
	}

	public function useWgConf() {
		return $this->_useWgConf;
	}
}
